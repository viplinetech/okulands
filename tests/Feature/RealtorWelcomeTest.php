<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/** After confirming their email, a realtor sees a welcome page with the WhatsApp group and the dashboard. */
class RealtorWelcomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_email_sends_a_realtor_to_the_welcome_page(): void
    {
        config(['services.realtor_whatsapp_group' => 'https://chat.whatsapp.com/example']);
        $realtor = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $realtor->id, 'hash' => sha1($realtor->getEmailForVerification())]);
        $this->actingAs($realtor)->get($url)->assertRedirect(route('realtor.welcome'));

        $this->actingAs($realtor)->get('/realtor/welcome')
            ->assertOk()
            ->assertSee('Registration successful')
            ->assertSee('Join the Realtor WhatsApp Group')
            ->assertSee('https://chat.whatsapp.com/example')
            ->assertSee('target="_blank"', false)
            ->assertSee('Access my Realtor Dashboard')
            ->assertSee($realtor->referral_code)
            // Standalone: no dashboard menu to wander off to before joining the group or opening the dashboard.
            ->assertDontSee('app-shell', false)
            ->assertDontSee('Bookings')
            ->assertDontSee('Earnings')
            ->assertSee('Sign out');
    }

    public function test_the_whatsapp_button_is_hidden_until_the_group_link_is_set(): void
    {
        config(['services.realtor_whatsapp_group' => null]);
        $realtor = User::factory()->create();

        $this->actingAs($realtor)->get('/realtor/welcome')->assertOk()->assertDontSee('Join the Realtor WhatsApp Group')->assertSee('Access my Realtor Dashboard');
    }

    public function test_the_verification_link_works_as_a_guest_with_no_session_at_all(): void
    {
        // Opening the link in a different browser or device, with no prior sign-in here, must still work:
        // the signed link itself proves ownership of the inbox and signs the account in.
        $realtor = User::factory()->unverified()->create();
        $this->assertGuest();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $realtor->id, 'hash' => sha1($realtor->getEmailForVerification())]);

        $this->get($url)->assertRedirect(route('realtor.welcome'));

        $this->assertAuthenticatedAs($realtor);
        $this->assertNotNull($realtor->fresh()->email_verified_at);
        $this->get('/realtor')->assertOk();
        $this->get('/realtor/welcome')->assertOk()->assertSee('Registration successful');
    }

    public function test_a_tampered_or_expired_link_is_refused_and_signs_nobody_in(): void
    {
        $realtor = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $realtor->id, 'hash' => 'wrong-hash']);
        $this->get($url)->assertForbidden();

        $this->assertGuest();
        $this->assertNull($realtor->fresh()->email_verified_at);
    }
}
