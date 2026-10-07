<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Sale;
use App\Models\User;
use App\Notifications\CommissionEarned;
use App\Services\CommissionCalculator;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public const STATUSES = ['pending', 'approved', 'paid', 'cancelled'];

    public function index(Request $request)
    {
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null;
        $term = trim((string) $request->query('q'));

        $sales = Sale::with(['property:id,title', 'realtor:id,name'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($term, fn ($q) => $q->where(fn ($w) => $w->where('buyer_name', 'like', "%{$term}%")->orWhere('reference', 'like', "%{$term}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.sales.index', [
            'sales' => $sales,
            'status' => $status,
            'term' => $term,
            'counts' => Sale::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Request $request)
    {
        $lead = $request->filled('lead') ? Lead::with('property')->find($request->query('lead')) : null;

        return view('admin.sales.create', [
            'lead' => $lead,
            'properties' => Property::orderBy('title')->get(['id', 'title', 'price', 'status', 'units_total']),
            'realtors' => User::affiliates()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'realtor_id' => ['required', Rule::exists('users', 'id')->whereIn('role', User::AFFILIATE_ROLES)],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'buyer_name' => ['required', 'string', 'max:160'],
            'buyer_email' => ['nullable', 'email', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:30'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999999'],
            'reference' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['quantity'] = $data['quantity'] ?? 1;

        $property = Property::findOrFail($data['property_id']);
        if ($property->isMultiUnit() && $data['quantity'] > $property->unitsRemaining()) {
            return back()->withInput()->withErrors(['quantity' => 'Only '.$property->unitsRemaining().' unit(s) are left on this listing.']);
        }

        $sale = new Sale($data);
        $sale->status = 'pending';
        $sale->save();

        Audit::log('sale.created', $sale, 'Recorded sale to '.$sale->buyer_name, ['amount' => (float) $sale->amount, 'quantity' => $sale->quantity]);

        return redirect()->route('admin.sales.show', $sale)->with('success', 'Sale recorded. Approve it to calculate commissions.');
    }

    public function show(Sale $sale)
    {
        return view('admin.sales.show', ['sale' => $sale->load(['property', 'realtor', 'lead', 'approver', 'commissions.user'])]);
    }

    /** Approving locks the sale in: the property is marked sold and commissions are created for the realtor and their upline. */
    public function approve(Sale $sale, CommissionCalculator $calculator): RedirectResponse
    {
        if ($sale->status !== 'pending') {
            return back()->with('error', 'Only pending sales can be approved.');
        }

        $commissions = DB::transaction(function () use ($sale, $calculator) {
            $sale->forceFill(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()])->save();

            // A multi-unit listing (bulk land sold off in plots) keeps selling normally after each approved
            // sale — it is never auto-marked "sold"; only the admin decides when to close it. A single-unit
            // listing (one house, one plot sold whole) is taken the moment its one sale is approved.
            $property = $sale->property;
            if ($property && ! $property->isMultiUnit()) {
                $property->forceFill(['status' => 'sold'])->save();
            }

            $sale->lead?->forceFill(['status' => 'converted'])->save();

            return $calculator->generate($sale);
        });

        foreach ($commissions as $commission) {
            $commission->user->notify(new CommissionEarned($commission));
        }

        Audit::log('sale.approved', $sale, 'Approved sale to '.$sale->buyer_name, ['amount' => (float) $sale->amount, 'commissions' => $commissions->sum('amount')]);

        return back()->with('success', 'Sale approved. '.$commissions->count().' commission(s) created.');
    }

    public function cancel(Sale $sale, CommissionCalculator $calculator): RedirectResponse
    {
        if (! in_array($sale->status, ['pending', 'approved'], true)) {
            return back()->with('error', 'This sale can no longer be cancelled.');
        }

        try {
            DB::transaction(function () use ($sale, $calculator) {
                $calculator->revoke($sale);
                $wasApproved = $sale->status === 'approved';
                $sale->forceFill(['status' => 'cancelled'])->save();

                // Single-unit listing only: put it back on the market unless another approved/paid sale still
                // holds it. A multi-unit listing's status is never touched automatically — the admin controls it.
                $property = $sale->property;
                if ($wasApproved && $property && ! $property->isMultiUnit()
                    && ! Sale::where('property_id', $property->id)->whereIn('status', ['approved', 'paid'])->where('id', '!=', $sale->id)->exists()) {
                    $property->forceFill(['status' => 'available'])->save();
                }
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        Audit::log('sale.cancelled', $sale, 'Cancelled sale to '.$sale->buyer_name);

        return back()->with('success', 'Sale cancelled and unpaid commissions removed.');
    }
}
