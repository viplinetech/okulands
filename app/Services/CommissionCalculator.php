<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionSetting;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Turns an approved sale into commission rows.
 *
 *  - Tier 1 is the realtor who closed the sale (their personal override rate, else the tier-1 rate).
 *  - Tier 2 is that realtor's referrer, tier 3 is theirs, and so on, using the rates the admin
 *    has configured. The chain stops at the first tier with no configured rate.
 */
class CommissionCalculator
{
    /** @return Collection<int, Commission> */
    public function generate(Sale $sale): Collection
    {
        if ($sale->commissions()->exists()) {
            return $sale->commissions; // idempotent: never pay a sale twice
        }

        $rates = CommissionSetting::query()->pluck('rate', 'tier'); // [tier => rate]
        $realtor = $sale->realtor;

        if (! $realtor || $rates->isEmpty()) {
            return collect();
        }

        return DB::transaction(function () use ($sale, $realtor, $rates) {
            $created = collect();

            $tierOne = $realtor->commission_rate_override ?? $rates->get(1);
            if ($tierOne !== null) {
                $created->push($this->make($sale, $realtor->id, 1, (float) $tierOne));
            }

            $deepest = (int) $rates->keys()->max();
            foreach ($realtor->uplineChain(max(0, $deepest - 1)) as $link) {
                $tier = $link['tier'] + 1;
                $rate = $rates->get($tier);
                if ($rate === null) {
                    break;
                }
                $created->push($this->make($sale, $link['user']->id, $tier, (float) $rate));
            }

            return $created;
        });
    }

    /** Remove unpaid commissions (used when a sale is cancelled or re-opened). Paid ones are protected. */
    public function revoke(Sale $sale): void
    {
        if ($sale->commissions()->where('status', 'paid')->exists()) {
            throw new \DomainException('This sale already has paid commissions and cannot be reversed.');
        }

        $sale->commissions()->delete();
    }

    private function make(Sale $sale, int $userId, int $tier, float $rate): Commission
    {
        return Commission::create([
            'sale_id' => $sale->id,
            'user_id' => $userId,
            'tier' => $tier,
            'rate' => $rate,
            'amount' => round((float) $sale->amount * $rate / 100, 2),
            'status' => 'pending',
        ]);
    }
}
