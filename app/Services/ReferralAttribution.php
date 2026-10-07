<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Services;

use App\Http\Controllers\ReferralController;
use App\Models\ReferralVisit;
use App\Models\User;
use App\Notifications\NewDownline;
use Illuminate\Http\Request;

class ReferralAttribution
{
    /** The referral visit this browser is tagged with (first touch wins), if any. */
    public function visit(Request $request): ?ReferralVisit
    {
        $token = $request->cookie(ReferralController::COOKIE);

        return $token ? ReferralVisit::where('visitor_token', $token)->first() : null;
    }

    /**
     * Place a newly registered user in their referrer's downline and mark the visit converted.
     * Ignores inactive referrers and self-referrals.
     */
    public function attachToNewUser(User $user, Request $request): void
    {
        $visit = $this->visit($request);
        $referrer = $visit ? User::find($visit->referrer_id) : null;

        if (! $visit || ! $referrer || ! $referrer->isActive() || ! $referrer->isAffiliate() || $referrer->is($user)) {
            return;
        }

        $user->forceFill(['referred_by' => $referrer->id])->save();

        $visit->forceFill(['converted_user_id' => $user->id, 'converted_at' => now()])->save();

        $referrer->notify(new NewDownline($user));
    }
}
