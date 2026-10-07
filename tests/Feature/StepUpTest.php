<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireStepUp;
use App\Models\CommissionSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/** Sensitive admin actions need a fresh two-factor code on top of the sign-in code. */
class StepUpTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'JBSWY3DPEHPK3PXP';

    protected function setUp(): void
    {
        parent::setUp();
        config(['security.step_up' => true]); // the feature is off by default; these tests exercise it switched on
    }

    public function test_by_default_the_sign_in_code_is_enough_and_nothing_extra_is_asked(): void
    {
        config(['security.step_up' => false]);
        $this->signedIn();

        $this->get(route('admin.sales.create'))->assertOk();
        $this->put(route('admin.commissions.rates'), ['rates' => [1 => 9, 2 => 3]])->assertSessionHasNoErrors();
        $this->assertEquals(9, (float) CommissionSetting::where('tier', 1)->value('rate'));
    }

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => self::SECRET, 'two_factor_recovery_codes' => ['aaaaa-bbbbb'], 'two_factor_confirmed_at' => now(),
        ]);
    }

    /** An admin who signed in with 2FA (no step-up yet). */
    private function signedIn(): static
    {
        return $this->actingAs($this->admin())->withSession(['two_factor_passed' => true]);
    }

    private function code(): string
    {
        return app(Google2FA::class)->getCurrentOtp(self::SECRET);
    }

    public function test_sensitive_actions_are_blocked_until_a_fresh_code_is_confirmed(): void
    {
        $this->signedIn();

        $this->put(route('admin.commissions.rates'), ['rates' => [1 => 9, 2 => 3]])->assertRedirect(route('admin.confirm'));
        $this->get(route('admin.sales.create'))->assertRedirect(route('admin.confirm'));
        $this->get(route('admin.realtors.create'))->assertRedirect(route('admin.confirm'));
        $this->get(route('admin.reports.export', 'realtors'))->assertRedirect(route('admin.confirm'));
        $this->post(route('admin.realtors.status', User::factory()->create()), ['status' => 'suspended'])->assertRedirect(route('admin.confirm'));

        $this->assertNotEquals(9, (float) CommissionSetting::where('tier', 1)->value('rate'));
    }

    public function test_ordinary_admin_pages_do_not_ask_for_a_code(): void
    {
        $this->signedIn();

        foreach (['/adminbackend/dashboard', '/adminbackend/sales', '/adminbackend/commissions', '/adminbackend/realtors', '/adminbackend/reports', '/adminbackend/settings', '/adminbackend/posts'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_confirming_with_a_correct_code_opens_a_window_and_returns_to_the_page(): void
    {
        $this->signedIn();

        $this->get(route('admin.sales.create'))->assertRedirect(route('admin.confirm'));
        $this->get(route('admin.confirm'))->assertOk()->assertSee('Confirm it is you');

        $this->post(route('admin.confirm.verify'), ['code' => $this->code()])->assertRedirect(route('admin.sales.create'));

        $this->get(route('admin.sales.create'))->assertOk();
        $this->put(route('admin.commissions.rates'), ['rates' => [1 => 9, 2 => 3]])->assertSessionHasNoErrors();
        $this->assertEquals(9, (float) CommissionSetting::where('tier', 1)->value('rate'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor.step_up']);
    }

    public function test_wrong_codes_are_refused_and_lock_out_after_five_tries(): void
    {
        $this->signedIn();

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('admin.confirm'))->post(route('admin.confirm.verify'), ['code' => '000000'])->assertSessionHasErrors('code');
        }

        // Even the right code is refused while locked out.
        $this->post(route('admin.confirm.verify'), ['code' => $this->code()])->assertSessionHasErrors('code');
        $this->get(route('admin.sales.create'))->assertRedirect(route('admin.confirm'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'two_factor.step_up_failed']);
    }

    public function test_the_confirmation_expires(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time() - RequireStepUp::WINDOW - 5]);

        $this->get(route('admin.sales.create'))->assertRedirect(route('admin.confirm'));
    }

    public function test_a_recovery_code_also_confirms_and_is_used_up(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->withSession(['two_factor_passed' => true]);

        $this->post(route('admin.confirm.verify'), ['code' => 'aaaaa-bbbbb', 'recovery' => 1])->assertRedirect();
        $this->get(route('admin.sales.create'))->assertOk();
        $this->assertNotContains('aaaaa-bbbbb', (array) $admin->fresh()->two_factor_recovery_codes);
    }

    public function test_realtors_and_guests_cannot_use_the_confirm_screen(): void
    {
        $this->get('/adminbackend/confirm')->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create())->get('/adminbackend/confirm')->assertNotFound();
        $this->post('/adminbackend/confirm', ['code' => '123456'])->assertNotFound();
    }
}
