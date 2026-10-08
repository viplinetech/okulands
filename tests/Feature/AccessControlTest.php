<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Who can reach what: guests, realtors and admins, including the mandatory admin two-factor gate. */
class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN_URLS = [
        '/adminbackend/dashboard', '/adminbackend/leads', '/adminbackend/sales', '/adminbackend/commissions', '/adminbackend/realtors', '/adminbackend/properties',
        '/adminbackend/services', '/adminbackend/gallery', '/adminbackend/posts', '/adminbackend/faqs', '/adminbackend/testimonials', '/adminbackend/settings',
        '/adminbackend/reports', '/adminbackend/activity', '/adminbackend/security', '/adminbackend/notifications',
    ];

    private const REALTOR_URLS = [
        '/realtor', '/realtor/leads', '/realtor/team', '/realtor/earnings', '/realtor/share', '/realtor/listings',
        '/realtor/alerts', '/realtor/profile', '/realtor/security',
    ];

    /** An admin who has two-factor on and has passed the challenge this session. */
    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_recovery_codes' => ['aaaaa-bbbbb'],
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_guests_are_sent_to_the_right_door_for_every_private_area(): void
    {
        foreach (self::ADMIN_URLS as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
        foreach (self::REALTOR_URLS as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_the_old_admin_path_no_longer_exists(): void
    {
        $this->get('/admin')->assertNotFound();
        $this->get('/admin/leads')->assertNotFound();
        $this->get('/admin/login')->assertNotFound();
    }

    public function test_the_admin_door_is_a_separate_screen_with_no_public_links(): void
    {
        $this->assertSame('/adminbackend', route('admin.login', absolute: false));

        $this->get('/adminbackend')
            ->assertOk()
            ->assertSee('Admin sign-in')
            ->assertSee('Restricted')
            ->assertDontSee('Become a realtor')
            ->assertSee('Back to website');

        $this->get('/login')->assertOk()->assertSee('Realtor Login')->assertDontSee('Admin sign-in');
    }

    public function test_the_public_website_never_links_to_the_admin(): void
    {
        foreach (['/', '/about', '/services', '/gallery', '/blog', '/contact', '/faqs', '/login', '/register'] as $url) {
            $this->get($url)->assertDontSee('adminbackend');
        }

        $this->get('/robots.txt')->assertSee('Disallow: /adminbackend', false);
    }

    public function test_realtors_get_a_404_on_every_admin_url(): void
    {
        $realtor = User::factory()->create();

        foreach (self::ADMIN_URLS as $url) {
            $this->actingAs($realtor)->get($url)->assertNotFound();
        }
    }

    public function test_realtors_cannot_post_to_admin_actions(): void
    {
        $realtor = User::factory()->create();
        $lead = Lead::create(['name' => 'X', 'phone' => '0800']);

        $this->actingAs($realtor)->put(route('admin.leads.update', $lead), ['status' => 'closed'])->assertNotFound();
        $this->actingAs($realtor)->post(route('admin.realtors.status', $realtor), ['status' => 'suspended'])->assertNotFound();
        $this->assertSame('new', $lead->fresh()->status);
    }

    public function test_admins_are_redirected_out_of_the_realtor_app(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->get('/realtor')->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_pages_render_for_a_verified_admin(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true]);

        foreach (self::ADMIN_URLS as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_an_admin_without_two_factor_can_use_the_whole_backend_normally(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/adminbackend/dashboard')->assertOk();
        $this->get('/adminbackend/leads')->assertOk();
        $this->get('/adminbackend/security')->assertOk();
    }

    public function test_an_admin_with_two_factor_must_pass_the_challenge_first(): void
    {
        $this->actingAs($this->admin());

        $this->get('/adminbackend/dashboard')->assertRedirect(route('two-factor.challenge'));
        $this->get('/adminbackend/leads')->assertRedirect(route('two-factor.challenge'));
    }

    public function test_a_realtor_only_sees_their_own_leads(): void
    {
        $mine = User::factory()->create();
        $other = User::factory()->create();
        Lead::create(['name' => 'My Lead Person', 'phone' => '0801', 'referrer_id' => $mine->id]);
        Lead::create(['name' => 'Someone Elses Lead', 'phone' => '0802', 'referrer_id' => $other->id]);
        Lead::create(['name' => 'Direct Enquiry Person', 'phone' => '0803']);

        // A realtor sees only their own bookings, and never who the people are (see RealtorPrivacyTest).
        $this->actingAs($mine)->get('/realtor/leads')
            ->assertOk()
            ->assertSee('Someone from your link')
            ->assertDontSee('My Lead Person')
            ->assertDontSee('Someone Elses Lead')
            ->assertDontSee('Direct Enquiry Person')
            ->assertDontSee('0801');
    }

    public function test_security_headers_are_sent_on_every_response(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty($response->headers->get('Permissions-Policy'));
    }

    public function test_pages_lock_zooming_and_never_index_private_areas(): void
    {
        $this->get('/')->assertSee('user-scalable=no', false);
        $this->get('/login')->assertSee('user-scalable=no', false);
        $this->actingAs(User::factory()->create())->get('/realtor')->assertSee('user-scalable=no', false)->assertSee('noindex', false);
    }
}
