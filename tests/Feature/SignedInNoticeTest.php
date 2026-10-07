<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Signed-in visitors see a clear notice on the sign-in pages instead of being redirected away from them. */
class SignedInNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_signed_in_admin_opening_the_realtor_login_sees_a_notice_not_a_redirect(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/login')
            ->assertOk()
            ->assertSee('You are signed in as')
            ->assertSee('Sign out');
    }

    public function test_a_signed_in_admin_opening_the_admin_login_sees_the_notice_too(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/adminbackend')->assertOk()->assertSee('You are signed in as');
    }

    public function test_guests_still_see_the_sign_in_form(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in')->assertDontSee('You are signed in as');
    }
}
