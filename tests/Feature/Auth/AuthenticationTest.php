<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSeeVolt('pages.auth.login');
    }

    public function test_realtors_can_authenticate_and_land_in_the_realtor_app(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasNoErrors()->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        // The generic dashboard URL sends each role to its own home.
        $this->get('/dashboard')->assertRedirect(route('realtor.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component->assertHasErrors()->assertNoRedirect();
        $this->assertGuest();
    }

    public function test_suspended_accounts_cannot_sign_in_even_with_the_right_password(): void
    {
        $user = User::factory()->suspended()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasErrors('form.email')->assertNoRedirect();
        $this->assertGuest();
    }

    public function test_a_session_is_ended_when_the_account_is_suspended_mid_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/realtor')->assertOk();

        $user->forceFill(['status' => 'suspended'])->save();

        $this->get('/realtor')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admins_sign_in_through_their_own_door_and_land_in_the_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $component = Volt::test('pages.auth.admin-login')
            ->set('form.email', $admin->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasNoErrors()->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_realtor_cannot_use_the_admin_door(): void
    {
        $realtor = User::factory()->create();

        $component = Volt::test('pages.auth.admin-login')
            ->set('form.email', $realtor->email)
            ->set('form.password', 'password');

        $component->call('login');

        // Right password, wrong door: it fails exactly like a bad password and starts no session.
        $component->assertHasErrors('form.email')->assertNoRedirect();
        $this->assertGuest();
        $this->assertSame(trans('auth.failed'), collect($component->errors()->get('form.email'))->first());
    }

    public function test_an_admin_cannot_use_the_realtor_door(): void
    {
        $admin = User::factory()->admin()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $admin->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasErrors('form.email')->assertNoRedirect();
        $this->assertGuest();
    }

    public function test_the_admin_door_rejects_suspended_and_wrong_password_the_same_safe_way(): void
    {
        $admin = User::factory()->admin()->suspended()->create();

        Volt::test('pages.auth.admin-login')->set('form.email', $admin->email)->set('form.password', 'wrong')->call('login')->assertHasErrors('form.email');
        Volt::test('pages.auth.admin-login')->set('form.email', $admin->email)->set('form.password', 'password')->call('login')->assertHasErrors('form.email');

        $this->assertGuest();
    }

    public function test_admin_logout_returns_to_the_admin_door(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->post('/logout')->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_private_pages_are_never_cached(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/realtor');

        $response->assertOk();
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('noindex', (string) $response->headers->get('X-Robots-Tag'));
    }
}
