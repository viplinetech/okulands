<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\ReferralController;
use App\Models\ReferralVisit;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ReferralAttribution;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.register_min_seconds' => 0]);
    }

    private function fill($component, array $overrides = [])
    {
        $data = array_merge([
            'name' => 'Test Realtor',
            'email' => 'realtor@example.com',
            'phone' => '08012345678',
            'gender' => 'male',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'terms' => true,
        ], $overrides);

        foreach ($data as $key => $value) {
            $component->set($key, $value);
        }

        return $component;
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk()->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_become_active_realtors_with_a_referral_code(): void
    {
        $component = $this->fill(Volt::test('pages.auth.register'));
        $component->call('register');

        $component->assertHasNoErrors()->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $user = User::where('email', 'realtor@example.com')->firstOrFail();
        $this->assertSame('realtor', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertNotEmpty($user->referral_code);
        $this->assertNull($user->referred_by);
    }

    public function test_someone_arriving_through_a_realtors_link_joins_their_downline(): void
    {
        $sponsor = User::factory()->create();
        $visit = ReferralVisit::create(['visitor_token' => (string) Str::uuid(), 'referrer_id' => $sponsor->id]);

        // Register normally, then attribute using a request that carries the browser's referral cookie.
        $this->fill(Volt::test('pages.auth.register'))->call('register');
        $user = User::where('email', 'realtor@example.com')->firstOrFail();
        $this->assertNull($user->referred_by);

        $request = Request::create('/', 'GET', [], [ReferralController::COOKIE => $visit->visitor_token]);
        app(ReferralAttribution::class)->attachToNewUser($user, $request);

        $this->assertSame($sponsor->id, $user->fresh()->referred_by);
        $this->assertNotNull($visit->fresh()->converted_at);
        $this->assertSame(1, $sponsor->unreadNotifications()->count());
    }

    public function test_the_role_can_never_be_mass_assigned(): void
    {
        $user = User::create(['name' => 'Sneaky', 'email' => 'sneaky@example.com', 'password' => 'StrongPass123', 'role' => 'admin', 'status' => 'active', 'referred_by' => 999]);

        $this->assertNotSame('admin', $user->fresh()->role);
        $this->assertNull($user->fresh()->referred_by);
    }

    public function test_weak_passwords_and_bad_phones_are_rejected(): void
    {
        $this->fill(Volt::test('pages.auth.register'), ['password' => 'short', 'password_confirmation' => 'short'])
            ->call('register')->assertHasErrors('password');

        $this->fill(Volt::test('pages.auth.register'), ['phone' => 'abc'])
            ->call('register')->assertHasErrors('phone');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_can_be_closed_by_the_admin(): void
    {
        SiteSetting::current()->update(['realtor_registration_enabled' => false]);

        $this->fill(Volt::test('pages.auth.register'))->call('register')->assertHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_bots_filling_the_honeypot_are_ignored(): void
    {
        $this->fill(Volt::test('pages.auth.register'), ['website' => 'http://spam.example'])->call('register');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_new_realtors_must_verify_their_email_before_using_the_app(): void
    {
        Notification::fake();
        $this->fill(Volt::test('pages.auth.register'))->call('register');

        $user = User::where('email', 'realtor@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->get('/realtor')->assertRedirect(route('verification.notice'));
        $this->get('/realtor/earnings')->assertRedirect(route('verification.notice'));

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]);
        $this->get($url);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->get('/realtor')->assertOk();
    }

    public function test_disposable_email_addresses_are_rejected(): void
    {
        $this->fill(Volt::test('pages.auth.register'), ['email' => 'bot@mailinator.com'])->call('register')->assertHasErrors('email');
        $this->fill(Volt::test('pages.auth.register'), ['email' => 'bot@sub.yopmail.com'])->call('register')->assertHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_instant_submissions_are_rejected_as_too_fast(): void
    {
        config(['auth.register_min_seconds' => 3]);

        $this->fill(Volt::test('pages.auth.register'))->call('register')->assertHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_the_turnstile_check_blocks_failed_and_missing_tokens_when_enabled(): void
    {
        config(['services.turnstile.site_key' => 'site', 'services.turnstile.secret' => 'secret']);

        // No token at all.
        $this->fill(Volt::test('pages.auth.register'))->call('register')->assertHasErrors('email');

        // Cloudflare rejects the token.
        Http::fake(['challenges.cloudflare.com/*' => Http::sequence()->push(['success' => false])->push(['success' => true])]);
        $this->fill(Volt::test('pages.auth.register'), ['cfToken' => 'bad'])->call('register')->assertHasErrors('email');
        $this->assertDatabaseCount('users', 0);

        // Cloudflare accepts it.
        $this->fill(Volt::test('pages.auth.register'), ['cfToken' => 'good'])->call('register')->assertHasNoErrors();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_gender_is_required_for_realtor_sign_up(): void
    {
        $this->fill(Volt::test('pages.auth.register'), ['gender' => ''])->call('register')->assertHasErrors('gender');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_agreeing_to_the_terms_is_required_and_the_page_links_to_them(): void
    {
        $this->fill(Volt::test('pages.auth.register'), ['terms' => false])->call('register')->assertHasErrors('terms');
        $this->assertDatabaseCount('users', 0);

        $this->get('/register')
            ->assertSee('Terms of Use')
            ->assertSee('Privacy Policy')
            ->assertSee(route('legal', 'terms'), false)
            ->assertSee(route('legal', 'privacy-policy'), false);
    }
}
