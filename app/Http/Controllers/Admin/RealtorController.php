<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\RealtorStats;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RealtorController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['active', 'suspended'], true) ? $request->query('status') : null;
        $term = trim((string) $request->query('q'));

        $realtors = User::affiliates()
            ->withCount(['downlines', 'referredLeads as leads_count', 'sales as closed_sales' => fn ($q) => $q->whereIn('status', ['approved', 'paid'])])
            ->withSum('commissions as earned', 'amount')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($term, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")->orWhere('referral_code', 'like', "%{$term}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.realtors.index', [
            'realtors' => $realtors,
            'status' => $status,
            'term' => $term,
            'counts' => User::affiliates()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function create()
    {
        return view('admin.realtors.create', ['sponsors' => User::affiliates()->where('status', 'active')->orderBy('name')->get(['id', 'name'])]);
    }

    /** The admin creates an account with a random temporary password, shown once. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email:rfc', 'lowercase', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\s()\-]{7,20}$/'],
            'referred_by' => ['nullable', Rule::exists('users', 'id')->whereIn('role', User::AFFILIATE_ROLES)],
        ], ['phone.regex' => 'Enter a valid phone number.']);

        $password = Str::password(14, symbols: false);

        $user = new User(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'password' => $password]);
        $user->forceFill(['role' => 'realtor', 'status' => 'active', 'referred_by' => $data['referred_by'] ?? null, 'email_verified_at' => now()])->save();

        Audit::log('realtor.created', $user, 'Created realtor '.$user->name);

        return redirect()->route('admin.realtors.show', $user)
            ->with('success', 'Realtor created. Share the temporary password securely; they should change it after signing in.')
            ->with('temp_password', $password);
    }

    public function show(User $realtor)
    {
        abort_unless($realtor->isAffiliate(), 404);

        $stats = new RealtorStats($realtor);

        return view('admin.realtors.show', [
            'realtor' => $realtor->load('referrer'),
            'summary' => $stats->summary(),
            'levels' => $stats->teamLevels(),
            'team' => $realtor->downlines()->withCount('downlines')->latest()->take(10)->get(),
            'leads' => $realtor->referredLeads()->with('property:id,title')->latest()->take(6)->get(),
            'commissions' => $realtor->commissions()->with('sale.property:id,title')->latest()->take(8)->get(),
            'activity' => ActivityLog::where('user_id', $realtor->id)->latest('created_at')->take(8)->get(),
            'sponsors' => User::affiliates()->where('id', '!=', $realtor->id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, User $realtor): RedirectResponse
    {
        abort_unless($realtor->isAffiliate(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\s()\-]{7,20}$/'],
            'commission_rate_override' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'referred_by' => ['nullable', Rule::exists('users', 'id')->whereIn('role', User::AFFILIATE_ROLES), Rule::notIn([$realtor->id])],
        ], ['phone.regex' => 'Enter a valid phone number.']);

        // Never let the sponsor be one of this realtor's own downline (that would create a loop).
        if (! empty($data['referred_by']) && $this->isInDownline($realtor, (int) $data['referred_by'])) {
            return back()->withErrors(['referred_by' => 'That person is in this realtor\'s own team, which would create a loop.'])->withInput();
        }

        $realtor->fill(['name' => $data['name'], 'phone' => $data['phone'] ?? null]);
        $realtor->forceFill([
            'commission_rate_override' => ($data['commission_rate_override'] ?? '') === '' ? null : $data['commission_rate_override'],
            'referred_by' => $data['referred_by'] ?? null,
        ])->save();

        Audit::log('realtor.updated', $realtor, 'Updated realtor '.$realtor->name, ['override' => $realtor->commission_rate_override, 'sponsor' => $realtor->referred_by]);

        return back()->with('success', 'Realtor updated.');
    }

    public function status(Request $request, User $realtor): RedirectResponse
    {
        abort_unless($realtor->isAffiliate(), 404);

        $data = $request->validate(['status' => ['required', 'in:active,suspended'], 'reason' => ['nullable', 'string', 'max:300']]);

        $realtor->forceFill(['status' => $data['status']])->save();

        Audit::log('realtor.'.($data['status'] === 'active' ? 'activated' : 'suspended'), $realtor, ucfirst($data['status']).': '.$realtor->name, ['reason' => $data['reason'] ?? null]);

        return back()->with('success', $realtor->name.' is now '.$data['status'].'.');
    }

    /** For a realtor who lost their phone: switch 2FA off so they can set it up again. */
    public function resetTwoFactor(User $realtor): RedirectResponse
    {
        abort_unless($realtor->isAffiliate(), 404);

        $realtor->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
        Audit::log('realtor.two_factor_reset', $realtor, 'Two-factor reset for '.$realtor->name);

        return back()->with('success', 'Two-factor authentication was reset for '.$realtor->name.'.');
    }

    private function isInDownline(User $realtor, int $candidateId): bool
    {
        $ids = [$realtor->id];
        for ($i = 0; $i < 20; $i++) {
            $ids = User::whereIn('referred_by', $ids)->pluck('id')->all();
            if (! $ids) {
                return false;
            }
            if (in_array($candidateId, $ids, true)) {
                return true;
            }
        }

        return false;
    }
}
