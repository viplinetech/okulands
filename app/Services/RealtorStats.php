<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Services;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Support\Carbon;

/** Numbers shown on a realtor's dashboard. Everything is scoped to the one user passed in. */
class RealtorStats
{
    public function __construct(private User $user) {}

    /** @return array<string, int|float> */
    public function summary(): array
    {
        $u = $this->user;

        $commissions = Commission::where('user_id', $u->id);

        return [
            'clicks' => $u->referralVisits()->count(),
            'joined' => $u->referralVisits()->whereNotNull('converted_user_id')->count(),
            'leads' => $u->referredLeads()->count(),
            'inspections' => $u->referredLeads()->where('type', 'inspection')->count(),
            'new_inspections' => $u->referredLeads()->where('type', 'inspection')->where('status', 'new')->count(),
            'new_leads' => $u->referredLeads()->where('status', 'new')->count(),
            'hot_leads' => $u->referredLeads()->whereIn('status', ['hot'])->count(),
            'sales' => $u->sales()->whereIn('status', ['approved', 'paid'])->count(),
            'team' => $u->downlines()->count(),
            'earned' => (float) (clone $commissions)->sum('amount'),
            'pending' => (float) (clone $commissions)->where('status', 'pending')->sum('amount'),
            'paid' => (float) (clone $commissions)->where('status', 'paid')->sum('amount'),
        ];
    }

    /**
     * Commission earned in each of the last N months (oldest first), grouped in PHP so it works on any database.
     *
     * @return array<int, array{label: string, value: float}>
     */
    public function monthlyEarnings(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $byMonth = Commission::where('user_id', $this->user->id)
            ->where('created_at', '>=', $start)
            ->get(['amount', 'created_at'])
            ->groupBy(fn (Commission $c) => $c->created_at->format('Y-m'))
            ->map(fn ($rows) => (float) $rows->sum('amount'));

        return collect(range(0, $months - 1))->map(function (int $i) use ($start, $byMonth) {
            /** @var Carbon $month */
            $month = $start->copy()->addMonths($i);

            return ['label' => $month->format('M'), 'value' => (float) ($byMonth[$month->format('Y-m')] ?? 0)];
        })->all();
    }

    /** Profile completeness checklist: what still needs doing before payouts can be made. */
    public function checklist(): array
    {
        $u = $this->user;

        return [
            ['done' => filled($u->phone), 'label' => 'Add your phone number', 'route' => 'realtor.profile'],
            ['done' => filled($u->bank_name) && filled($u->account_number) && filled($u->account_name), 'label' => 'Add your bank details for payouts', 'route' => 'realtor.profile', 'anchor' => 'payout'],
            ['done' => $u->hasTwoFactorEnabled(), 'label' => 'Turn on two-factor security', 'route' => 'realtor.security'],
            ['done' => $u->referralVisits()->exists(), 'label' => 'Share your link and get your first click', 'route' => 'realtor.share'],
        ];
    }

    /** Number of people at each depth of the downline (level 1 = direct recruits). @return array<int, int> */
    public function teamLevels(int $maxLevels = 5): array
    {
        $levels = [];
        $ids = [$this->user->id];

        for ($level = 1; $level <= $maxLevels; $level++) {
            $ids = User::whereIn('referred_by', $ids)->pluck('id')->all();
            if (! $ids) {
                break;
            }
            $levels[$level] = count($ids);
        }

        return $levels;
    }
}
