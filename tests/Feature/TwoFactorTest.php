<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function enabledUser(string $role = 'realtor'): array
    {
        $secret = (new Google2FA)->generateSecretKey(32);
        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['abcde-fghij', 'klmno-pqrst'],
            'two_factor_confirmed_at' => now(),
        ]);
        if ($role === 'admin') {
            $user->forceFill(['role' => 'admin'])->save();
        }

        return [$user, $secret];
    }

    public function test_a_user_can_enable_two_factor_with_a_valid_code(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('two-factor.start'))->assertRedirect(route('realtor.security'));
        $secret = session('two_factor_pending_secret');
        $this->assertNotEmpty($secret);

        // A wrong code is refused and nothing is saved.
        $this->post(route('two-factor.confirm'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());

        $this->post(route('two-factor.confirm'), ['code' => (new Google2FA)->getCurrentOtp($secret)])
            ->assertRedirect(route('realtor.security'))
            ->assertSessionHas('recovery_codes');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertCount(8, $user->two_factor_recovery_codes);
    }

    public function test_the_secret_is_stored_encrypted(): void
    {
        [$user, $secret] = $this->enabledUser();

        $raw = \DB::table('users')->where('id', $user->id)->value('two_factor_secret');

        $this->assertNotSame($secret, $raw);
        $this->assertSame($secret, $user->fresh()->two_factor_secret);
    }

    public function test_the_challenge_accepts_a_valid_code_only_once(): void
    {
        [$user, $secret] = $this->enabledUser();
        $this->actingAs($user);
        $code = (new Google2FA)->getCurrentOtp($secret);

        Volt::test('pages.auth.two-factor-challenge')->set('code', $code)->call('verify')->assertHasNoErrors();
        $this->assertTrue(session('two_factor_passed'));

        // The same code cannot be replayed in a new session.
        session()->forget('two_factor_passed');
        Volt::test('pages.auth.two-factor-challenge')->set('code', $code)->call('verify')->assertHasErrors('code');
        $this->assertNull(session('two_factor_passed'));
    }

    public function test_a_wrong_code_is_rejected(): void
    {
        [$user] = $this->enabledUser();
        $this->actingAs($user);

        Volt::test('pages.auth.two-factor-challenge')->set('code', '123456')->call('verify')->assertHasErrors('code');
        $this->assertNull(session('two_factor_passed'));
    }

    public function test_repeated_wrong_codes_lock_the_challenge(): void
    {
        [$user, $secret] = $this->enabledUser();
        $this->actingAs($user);

        $component = Volt::test('pages.auth.two-factor-challenge');
        foreach (range(1, 5) as $i) {
            $component->set('code', '99999'.$i)->call('verify');
        }

        // Even the right code is refused while locked out.
        $component->set('code', (new Google2FA)->getCurrentOtp($secret))->call('verify')->assertHasErrors('code');
        $this->assertNull(session('two_factor_passed'));
    }

    public function test_recovery_codes_work_once(): void
    {
        [$user] = $this->enabledUser();
        $this->actingAs($user);

        Volt::test('pages.auth.two-factor-challenge')->set('recovery', true)->set('code', 'abcde-fghij')->call('verify')->assertHasNoErrors();
        $this->assertTrue(session('two_factor_passed'));
        $this->assertSame(['klmno-pqrst'], $user->fresh()->two_factor_recovery_codes);

        session()->forget('two_factor_passed');
        Volt::test('pages.auth.two-factor-challenge')->set('recovery', true)->set('code', 'abcde-fghij')->call('verify')->assertHasErrors('code');
    }

    public function test_a_realtor_can_disable_two_factor_with_password_and_code(): void
    {
        [$user, $secret] = $this->enabledUser();
        $this->actingAs($user)->withSession(['two_factor_passed' => true]);

        $this->delete(route('two-factor.disable'), ['password' => 'wrong', 'code' => (new Google2FA)->getCurrentOtp($secret)])
            ->assertSessionHasErrors('password');
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        $this->delete(route('two-factor.disable'), ['password' => 'password', 'code' => (new Google2FA)->getCurrentOtp($secret)])
            ->assertRedirect(route('realtor.security'));
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_an_admin_can_never_disable_two_factor(): void
    {
        [$admin, $secret] = $this->enabledUser('admin');
        $this->actingAs($admin)->withSession(['two_factor_passed' => true]);

        $this->delete(route('two-factor.disable'), ['password' => 'password', 'code' => (new Google2FA)->getCurrentOtp($secret)])
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->hasTwoFactorEnabled());
    }

    public function test_changing_password_needs_the_current_one_and_a_strong_new_one(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('account.password'), ['current_password' => 'nope', 'password' => 'NewStrongPass123', 'password_confirmation' => 'NewStrongPass123'])
            ->assertSessionHasErrors('current_password');

        $this->post(route('account.password'), ['current_password' => 'password', 'password' => 'weak', 'password_confirmation' => 'weak'])
            ->assertSessionHasErrors('password');

        $this->post(route('account.password'), ['current_password' => 'password', 'password' => 'NewStrongPass123', 'password_confirmation' => 'NewStrongPass123'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(\Hash::check('NewStrongPass123', $user->fresh()->password));
        $this->assertDatabaseHas('activity_logs', ['action' => 'auth.password_changed']);
    }

    public function test_an_admin_who_lost_their_phone_and_codes_can_be_reset_from_the_server(): void
    {
        $admin = User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => ['aaaaa-bbbbb'], 'two_factor_confirmed_at' => now(),
        ]);
        $realtor = User::factory()->create();

        $this->artisan('okulands:admin', ['email' => $realtor->email, '--reset-2fa' => true])->assertFailed(); // only admins
        $this->artisan('okulands:admin', ['email' => $admin->email, '--reset-2fa' => true])->assertSuccessful();

        $fresh = $admin->fresh();
        $this->assertNull($fresh->two_factor_secret);
        $this->assertNull($fresh->two_factor_confirmed_at);
        $this->assertTrue($fresh->isAdmin());
        $this->assertDatabaseHas('activity_logs', ['action' => 'admin.two_factor_reset']);

        // 2FA is optional, not enforced: the admin can keep working normally without it.
        $this->actingAs($fresh)->get('/adminbackend/dashboard')->assertOk();
    }
}
