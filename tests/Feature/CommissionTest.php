<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionSetting;
use App\Models\Property;
use App\Models\Sale;
use App\Models\User;
use App\Services\CommissionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CommissionSetting::create(['tier' => 1, 'rate' => 5]);
        CommissionSetting::create(['tier' => 2, 'rate' => 2]);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => [], 'two_factor_confirmed_at' => now(),
        ]);
    }

    private function sale(User $realtor, float $amount = 10_000_000): Sale
    {
        $property = Property::create([
            'title' => 'Plot', 'slug' => 'plot-'.uniqid(), 'sector' => 'real_estate', 'price' => $amount,
            'location' => 'Awka', 'status' => 'available', 'created_by' => $realtor->id,
        ]);

        $sale = new Sale(['property_id' => $property->id, 'realtor_id' => $realtor->id, 'buyer_name' => 'Buyer', 'amount' => $amount]);
        $sale->status = 'pending';
        $sale->save();

        return $sale;
    }

    public function test_tier_one_goes_to_the_realtor_and_tier_two_to_their_sponsor(): void
    {
        $sponsor = User::factory()->create();
        $realtor = User::factory()->create();
        $realtor->forceFill(['referred_by' => $sponsor->id])->save();

        $commissions = app(CommissionCalculator::class)->generate($this->sale($realtor));

        $this->assertCount(2, $commissions);
        $this->assertEquals(500_000, $commissions->firstWhere('user_id', $realtor->id)->amount);
        $this->assertSame(1, $commissions->firstWhere('user_id', $realtor->id)->tier);
        $this->assertEquals(200_000, $commissions->firstWhere('user_id', $sponsor->id)->amount);
        $this->assertSame(2, $commissions->firstWhere('user_id', $sponsor->id)->tier);
    }

    public function test_a_personal_rate_overrides_the_tier_one_rate(): void
    {
        $realtor = User::factory()->create();
        $realtor->forceFill(['commission_rate_override' => 8])->save();

        $commissions = app(CommissionCalculator::class)->generate($this->sale($realtor));

        $this->assertEquals(800_000, $commissions->first()->amount);
    }

    public function test_the_chain_stops_where_no_rate_is_configured(): void
    {
        $top = User::factory()->create();
        $mid = User::factory()->create();
        $low = User::factory()->create();
        $mid->forceFill(['referred_by' => $top->id])->save();
        $low->forceFill(['referred_by' => $mid->id])->save();

        // Only tiers 1 and 2 have rates, so the third person up earns nothing.
        $commissions = app(CommissionCalculator::class)->generate($this->sale($low));

        $this->assertCount(2, $commissions);
        $this->assertNull($commissions->firstWhere('user_id', $top->id));
    }

    public function test_generating_twice_never_pays_a_sale_twice(): void
    {
        $realtor = User::factory()->create();
        $sale = $this->sale($realtor);
        $calculator = app(CommissionCalculator::class);

        $calculator->generate($sale);
        $calculator->generate($sale);

        $this->assertSame(1, Commission::where('sale_id', $sale->id)->count());
    }

    public function test_paid_commissions_cannot_be_reversed(): void
    {
        $realtor = User::factory()->create();
        $sale = $this->sale($realtor);
        $calculator = app(CommissionCalculator::class);
        $calculator->generate($sale)->first()->forceFill(['status' => 'paid'])->save();

        $this->expectException(\DomainException::class);
        $calculator->revoke($sale);
    }

    public function test_a_referral_loop_cannot_cause_endless_commissions(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $a->forceFill(['referred_by' => $b->id])->save();
        $b->forceFill(['referred_by' => $a->id])->save();

        CommissionSetting::create(['tier' => 3, 'rate' => 1]);
        CommissionSetting::create(['tier' => 4, 'rate' => 1]);

        $commissions = app(CommissionCalculator::class)->generate($this->sale($a));

        $this->assertLessThanOrEqual(2, $commissions->count());
    }

    public function test_approving_a_sale_creates_commissions_marks_the_property_sold_and_alerts_realtors(): void
    {
        $realtor = User::factory()->create();
        $sale = $this->sale($realtor);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()])
            ->post(route('admin.sales.approve', $sale))->assertRedirect();

        $sale->refresh();
        $this->assertSame('approved', $sale->status);
        $this->assertSame('sold', $sale->property->status);
        $this->assertSame(1, Commission::where('sale_id', $sale->id)->count());
        $this->assertSame(1, $realtor->unreadNotifications()->count());
        $this->assertDatabaseHas('activity_logs', ['action' => 'sale.approved']);
    }

    public function test_a_sale_can_only_be_approved_once(): void
    {
        $realtor = User::factory()->create();
        $sale = $this->sale($realtor);
        $admin = $this->admin();

        $this->actingAs($admin)->withSession(['two_factor_passed' => true, 'step_up_at' => time()])->post(route('admin.sales.approve', $sale));
        $this->post(route('admin.sales.approve', $sale));

        $this->assertSame(1, Commission::where('sale_id', $sale->id)->count());
    }

    public function test_paying_every_commission_marks_the_sale_paid(): void
    {
        $realtor = User::factory()->create();
        $sale = $this->sale($realtor);
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()]);
        $this->post(route('admin.sales.approve', $sale));

        $commission = Commission::where('sale_id', $sale->id)->firstOrFail();
        $this->post(route('admin.commissions.pay', $commission), ['payout_reference' => 'TRF-1'])->assertRedirect();

        $this->assertSame('paid', $commission->fresh()->status);
        $this->assertSame('paid', $sale->fresh()->status);
        $this->assertSame('TRF-1', $commission->fresh()->payout_reference);
    }

    public function test_cancelling_an_approved_sale_returns_the_property_and_removes_unpaid_commission(): void
    {
        $realtor = User::factory()->create();
        $sale = $this->sale($realtor);
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()]);
        $this->post(route('admin.sales.approve', $sale));
        $this->post(route('admin.sales.cancel', $sale));

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertSame('available', $sale->property->fresh()->status);
        $this->assertSame(0, Commission::where('sale_id', $sale->id)->count());
    }

    public function test_rate_changes_only_affect_future_sales(): void
    {
        $realtor = User::factory()->create();
        $first = $this->sale($realtor);
        app(CommissionCalculator::class)->generate($first);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()])
            ->put(route('admin.commissions.rates'), ['rates' => [1 => 10, 2 => 2]])->assertRedirect();

        $this->assertEquals(500_000, Commission::where('sale_id', $first->id)->first()->amount); // still 5%

        $second = $this->sale($realtor);
        $this->assertEquals(1_000_000, app(CommissionCalculator::class)->generate($second)->first()->amount); // now 10%
    }

    public function test_rate_validation_rejects_nonsense(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()])
            ->put(route('admin.commissions.rates'), ['rates' => [1 => 150]])->assertSessionHasErrors('rates.*');
    }
}
