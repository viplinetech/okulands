<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Cloudflare Turnstile bot check (free, no puzzles for most humans).
 * Active only when both keys are configured; otherwise sign-up relies on the other defences.
 */
class Turnstile
{
    public function enabled(): bool
    {
        return filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret'));
    }

    public function siteKey(): ?string
    {
        return config('services.turnstile.site_key');
    }

    /** Fails closed: a missing token, a network error or a rejected token all count as "not human". */
    public function passes(?string $token, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return $response->ok() && $response->json('success') === true;
        } catch (Throwable) {
            return false;
        }
    }
}
