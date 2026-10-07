<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Time-based one-time passwords (Google Authenticator, Authy, 1Password, Microsoft Authenticator…)
 * plus one-time recovery codes. Codes cannot be replayed: a code that was already accepted is refused.
 */
class TwoFactorService
{
    private Google2FA $google;

    public function __construct()
    {
        $this->google = new Google2FA;
        $this->google->setWindow(1); // tolerate ±30s of clock drift
    }

    public function newSecret(): string
    {
        return $this->google->generateSecretKey(32);
    }

    /** Inline SVG QR code for the authenticator app (no external service is contacted). */
    public function qrSvg(string $email, string $secret): string
    {
        $url = $this->google->getQRCodeUrl(config('app.name', 'Oku Lands'), $email, $secret);
        $svg = (new Writer(new ImageRenderer(new RendererStyle(200, 1), new SvgImageBackEnd)))->writeString($url);

        return preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg);
    }

    /** Verify a code for an enabled account (with replay protection). */
    public function verify(User $user, string $code): bool
    {
        return $this->check((string) $user->two_factor_secret, $code, 'user:'.$user->id);
    }

    /** Verify a code against a secret that is not saved yet (during setup). */
    public function verifyPending(string $secret, string $code, int $userId): bool
    {
        return $this->check($secret, $code, 'setup:'.$userId);
    }

    private function check(string $secret, string $code, string $scope): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if ($secret === '' || ! preg_match('/^\d{6}$/', (string) $code)) {
            return false;
        }

        $key = '2fa:last:'.$scope;

        // An integer (never null) "last accepted step" makes the library return the matching time step
        // instead of a bare `true`, which is what lets us refuse a code that was already used.
        $step = $this->google->verifyKeyNewer($secret, $code, (int) Cache::get($key, 0));

        if ($step === false || $step === null) {
            return false;
        }

        Cache::put($key, (int) $step, now()->addMinutes(5));

        return true;
    }

    /** @return array<int, string> eight single-use codes like "k3f9a-p2x7q" */
    public function newRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::lower(Str::random(5).'-'.Str::random(5)))
            ->all();
    }

    /** Consume a recovery code if it is valid. Returns true on success. */
    public function useRecoveryCode(User $user, string $input): bool
    {
        $input = Str::lower(trim($input));
        $codes = $user->two_factor_recovery_codes ?? [];

        foreach ($codes as $i => $code) {
            if (hash_equals($code, $input)) {
                unset($codes[$i]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }
}
