<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\ReferralVisit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Realtor referral links: yoursite.com/ref/{code}.
 *
 * "Referral Protection": a visitor is tagged to whichever realtor's link they clicked MOST
 * RECENTLY, for a year. A later link from a different realtor takes over the tag, so whoever
 * was actually behind the enquiry/booking gets the credit (last-touch attribution, the
 * industry-standard model). Any enquiry the visitor sends afterwards is credited to that
 * realtor (see LeadController); leads already created stay with whoever earned them at the
 * time — this never changes credit on a booking or sale that already happened.
 */
class ReferralController extends Controller
{
    public const COOKIE = 'oku_ref';

    /** How long the referral tag lives in the visitor's browser, renewed on every visit. */
    public const TTL_MINUTES = 60 * 24 * 365;

    public function track(Request $request, string $code): RedirectResponse
    {
        $referrer = User::where('referral_code', $code)->where('status', 'active')->first();

        $destination = $this->safePath($request->query('to'));

        if (! $referrer) {
            return redirect($destination);
        }

        $token = $request->cookie(self::COOKIE);
        $existing = $token ? ReferralVisit::where('visitor_token', $token)->first() : null;

        if (! $existing) {
            $token = (string) Str::uuid();
            ReferralVisit::create([
                'visitor_token' => $token,
                'referrer_id' => $referrer->id,
                'landing_url' => Str::limit($destination, 250, ''),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            ]);
        } elseif ($existing->referrer_id !== $referrer->id) {
            // Last-touch: a different realtor's link takes over the tag going forward. Leads
            // already created under the previous referrer keep their own stored referrer_id.
            $existing->forceFill(['referrer_id' => $referrer->id])->save();
        }

        // Re-issue the cookie either way so an active visitor's tag keeps renewing for another year.
        return redirect($destination)->withCookie(
            cookie(self::COOKIE, $token, self::TTL_MINUTES, '/', null, $request->isSecure(), true, false, 'lax')
        );
    }

    /** Only same-site paths are allowed as a destination (no open redirects). */
    private function safePath(?string $to): string
    {
        if ($to && str_starts_with($to, '/') && ! str_starts_with($to, '//') && ! str_contains($to, '\\')) {
            return $to;
        }

        return '/';
    }
}
