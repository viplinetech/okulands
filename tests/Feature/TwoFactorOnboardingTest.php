<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TrustedDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/** Friendlier two-factor rollout: a grace period with a reminder, and "trust this device". */
class TwoFactorOnboardingTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'JBSWY3DPEHPK3PXP';

    private function withTwoFactor(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => self::SECRET, 'two_factor_recovery_codes' => ['aaaaa-bbbbb'], 'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_a_new_admin_can_work_during_the_grace_period_and_sees_a_reminder(): void
    {
        config(['security.admin_2fa_grace_days' => 7]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/adminbackend/dashboard')->assertOk()->assertSee('Protect your admin account')->assertSee('7 days');
        $this->assertEqualsWithDelta(now()->addDays(7)->timestamp, $admin->fresh()->two_factor_grace_ends_at->timestamp, 5);

        // The security page shows the plain-language guide instead of the reminder banner.
        $this->get('/adminbackend/security')->assertOk()->assertSee('How it works')->assertSee('Google Authenticator')->assertDontSee('Protect your admin account');
    }

    public function test_the_grace_period_ends_and_two_factor_becomes_required(): void
    {
        config(['security.admin_2fa_grace_days' => 7]);
        $admin = User::factory()->admin()->create(['two_factor_grace_ends_at' => now()->subMinute()]);

        $this->actingAs($admin)->get('/adminbackend/dashboard')->assertRedirect(route('admin.security'));
        $this->get('/adminbackend/leads')->assertRedirect(route('admin.security'));
        $this->get('/adminbackend/security')->assertOk();
    }

    public function test_the_grace_period_does_not_restart_each_visit(): void
    {
        config(['security.admin_2fa_grace_days' => 7]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/adminbackend/dashboard');
        $admin->forceFill(['two_factor_grace_ends_at' => now()->addDays(5)])->save(); // two days later

        $this->get('/adminbackend/dashboard')->assertOk()->assertSee('5 days');
        $this->assertEqualsWithDelta(now()->addDays(5)->timestamp, $admin->fresh()->two_factor_grace_ends_at->timestamp, 5);
    }

    public function test_a_trusted_device_skips_the_code(): void
    {
        $admin = $this->withTwoFactor();
        $cookie = app(TrustedDevice::class)->issue($admin);

        // Without the cookie the code is asked for.
        $this->actingAs($admin)->get('/adminbackend/dashboard')->assertRedirect(route('two-factor.challenge'));

        // With it, straight in, and the session is marked as passed.
        $this->withCookie(TrustedDevice::COOKIE, $cookie->getValue())->get('/adminbackend/dashboard')->assertOk();
    }

    public function test_a_trusted_device_cookie_cannot_be_forged_reused_or_outlive_a_credential_change(): void
    {
        $admin = $this->withTwoFactor();
        $other = $this->withTwoFactor();
        $good = app(TrustedDevice::class)->issue($admin)->getValue();
        $challenge = route('two-factor.challenge');

        // Someone else's cookie, a tampered one, and garbage.
        $this->actingAs($admin)->withCookie(TrustedDevice::COOKIE, app(TrustedDevice::class)->issue($other)->getValue())->get('/adminbackend/dashboard')->assertRedirect($challenge);
        $this->withCookie(TrustedDevice::COOKIE, $good.'x')->get('/adminbackend/dashboard')->assertRedirect($challenge);
        $this->withCookie(TrustedDevice::COOKIE, 'nonsense')->get('/adminbackend/dashboard')->assertRedirect($challenge);

        // Expired.
        $this->travel(31)->days();
        $this->withCookie(TrustedDevice::COOKIE, $good)->get('/adminbackend/dashboard')->assertRedirect($challenge);
        $this->travelBack();

        // Changing the password signs out every trusted device.
        $admin->forceFill(['password' => 'AnotherStrongPass123'])->save();
        $this->withCookie(TrustedDevice::COOKIE, $good)->get('/adminbackend/dashboard')->assertRedirect($challenge);

        // So does setting two-factor up again (new secret).
        $fresh = app(TrustedDevice::class)->issue($admin->fresh())->getValue();
        $admin->forceFill(['two_factor_secret' => 'KRSXG5CTMVRXEZLU'])->save();
        $this->withCookie(TrustedDevice::COOKIE, $fresh)->get('/adminbackend/dashboard')->assertRedirect($challenge);
    }

    public function test_ticking_trust_this_device_on_the_code_screen_issues_the_cookie(): void
    {
        $admin = $this->withTwoFactor();
        $this->actingAs($admin);
        $code = app(Google2FA::class)->getCurrentOtp(self::SECRET);

        Volt::test('pages.auth.two-factor-challenge')->set('code', $code)->set('trust', true)->call('verify')->assertHasNoErrors();

        $this->assertTrue(cookie()->hasQueued(TrustedDevice::COOKIE));
    }

    public function test_without_ticking_trust_no_cookie_is_issued(): void
    {
        $admin = $this->withTwoFactor();
        $this->actingAs($admin);
        $code = app(Google2FA::class)->getCurrentOtp(self::SECRET);

        Volt::test('pages.auth.two-factor-challenge')->set('code', $code)->call('verify')->assertHasNoErrors();

        $this->assertFalse(cookie()->hasQueued(TrustedDevice::COOKIE));
    }
}
