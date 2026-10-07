<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Withdrawal;
use App\Notifications\WithdrawalPaid;
use App\Notifications\WithdrawalRejected;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['pending', 'paid', 'rejected'], true) ? $request->query('status') : null;

        $withdrawals = Withdrawal::with('user:id,name')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.withdrawals.index', [
            'withdrawals' => $withdrawals,
            'status' => $status,
            'totals' => [
                'pending' => (float) Withdrawal::where('status', 'pending')->sum('amount'),
                'paid' => (float) Withdrawal::where('status', 'paid')->sum('amount'),
            ],
        ]);
    }

    /**
     * Paying a withdrawal settles it from the realtor's oldest pending commissions first (FIFO), so the
     * earnings page's Pending/Paid split stays accurate — one ledger, no money double-counted.
     */
    public function pay(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal has already been decided.');
        }

        $data = $request->validate(['reference' => ['nullable', 'string', 'max:120']]);

        DB::transaction(function () use ($withdrawal, $data, $request) {
            $remaining = (float) $withdrawal->amount;

            $commissions = Commission::where('user_id', $withdrawal->user_id)->where('status', 'pending')->oldest()->get();
            foreach ($commissions as $commission) {
                if ($remaining <= 0) {
                    break;
                }
                $take = min($remaining, (float) $commission->amount);
                if ($take >= (float) $commission->amount) {
                    $commission->forceFill(['status' => 'paid', 'paid_at' => now(), 'payout_reference' => $data['reference'] ?? ('Withdrawal #'.$withdrawal->id)])->save();
                    $remaining -= (float) $commission->amount;
                }
            }

            $withdrawal->forceFill([
                'status' => 'paid',
                'reference' => $data['reference'] ?? null,
                'decided_by' => $request->user()->id,
                'decided_at' => now(),
            ])->save();
        });

        $withdrawal->user->notify(new WithdrawalPaid($withdrawal));
        Audit::log('withdrawal.paid', $withdrawal, '₦'.number_format((float) $withdrawal->amount).' paid to '.$withdrawal->user->name, ['reference' => $data['reference'] ?? null]);

        return back()->with('success', 'Marked as paid and the realtor was notified.');
    }

    public function reject(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal has already been decided.');
        }

        $data = $request->validate(['notes' => ['nullable', 'string', 'max:500']]);

        $withdrawal->forceFill([
            'status' => 'rejected',
            'notes' => $data['notes'] ?? null,
            'decided_by' => $request->user()->id,
            'decided_at' => now(),
        ])->save();

        $withdrawal->user->notify(new WithdrawalRejected($withdrawal));
        Audit::log('withdrawal.rejected', $withdrawal, 'Declined withdrawal for '.$withdrawal->user->name, ['notes' => $data['notes'] ?? null]);

        return back()->with('success', 'Withdrawal declined and the realtor was notified.');
    }
}
