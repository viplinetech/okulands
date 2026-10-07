<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\CommissionSetting;
use App\Notifications\CommissionPaid;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['pending', 'paid'], true) ? $request->query('status') : null;
        $term = trim((string) $request->query('q'));

        $commissions = Commission::with(['user:id,name,bank_name,account_name,account_number', 'sale.property:id,title'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($term, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.commissions.index', [
            'commissions' => $commissions,
            'status' => $status,
            'term' => $term,
            'totals' => [
                'pending' => (float) Commission::where('status', 'pending')->sum('amount'),
                'paid' => (float) Commission::where('status', 'paid')->sum('amount'),
            ],
            'rates' => CommissionSetting::orderBy('tier')->get(),
        ]);
    }

    public function pay(Request $request, Commission $commission): RedirectResponse
    {
        if ($commission->status === 'paid') {
            return back()->with('error', 'This commission is already paid.');
        }

        $data = $request->validate(['payout_reference' => ['nullable', 'string', 'max:120']]);

        DB::transaction(function () use ($commission, $data) {
            $commission->forceFill(['status' => 'paid', 'paid_at' => now(), 'payout_reference' => $data['payout_reference'] ?? null])->save();

            $sale = $commission->sale;
            if ($sale && ! $sale->commissions()->where('status', 'pending')->exists()) {
                $sale->forceFill(['status' => 'paid'])->save();
            }
        });

        $commission->user->notify(new CommissionPaid($commission));
        Audit::log('commission.paid', $commission, '₦'.number_format((float) $commission->amount).' paid to '.$commission->user->name, ['reference' => $data['payout_reference'] ?? null]);

        return back()->with('success', 'Marked as paid and the realtor was notified.');
    }

    /**
     * Save tier rates. Only future sales use the new rates: commissions already created keep the rate they were made with.
     */
    public function updateRates(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rates' => ['required', 'array', 'min:1', 'max:10'],
            'rates.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'new_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], ['rates.*.numeric' => 'Each rate must be a number between 0 and 100.']);

        $rates = collect($data['rates'])->filter(fn ($v) => $v !== null && $v !== '');

        if (! $rates->has(1)) {
            return back()->with('error', 'Tier 1 (the realtor who closes the sale) must have a rate.');
        }

        if (($data['new_rate'] ?? '') !== '' && $data['new_rate'] !== null) {
            $rates->put($rates->keys()->max() + 1, $data['new_rate']);
        }

        DB::transaction(function () use ($rates) {
            CommissionSetting::whereNotIn('tier', $rates->keys())->delete();
            foreach ($rates as $tier => $rate) {
                CommissionSetting::updateOrCreate(['tier' => (int) $tier], ['rate' => $rate]);
            }
        });

        Audit::log('commission.rates_updated', null, 'Commission rates changed', $rates->all());

        return back()->with('success', 'Commission rates saved. They apply to sales approved from now on.');
    }
}
