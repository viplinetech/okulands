<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionSetting;
use App\Models\Property;
use App\Models\Sale;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkUnitsAndWithdrawalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CommissionSetting::create(['tier' => 1, 'rate' => 5]);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => [], 'two_factor_confirmed_at' => now(),
        ]);
    }

    private function realtor(): User
    {
        return User::factory()->create([
            'bank_name' => 'Zenith Bank', 'account_name' => 'Jane Realtor', 'account_number' => '0123456789',
        ]);
    }

    /* ---------- Bulk land units ---------- */

    public function test_recording_a_sale_with_quantity_reduces_remaining_units_without_marking_the_listing_sold(): void
    {
        $realtor = $this->realtor();
        $property = Property::create([
            'title' => 'Estate Plot', 'slug' => 'estate-plot', 'sector' => 'real_estate', 'price' => 1_000_000,
            'units_total' => 10, 'location' => 'Awka', 'status' => 'available', 'created_by' => $realtor->id,
        ]);

        $admin = $this->admin();
        $this->actingAs($admin)->withSession(['two_factor_passed' => true, 'step_up_at' => time()])->post(route('admin.sales.store'), [
            'property_id' => $property->id, 'realtor_id' => $realtor->id, 'buyer_name' => 'Buyer A',
            'quantity' => 3, 'amount' => 3_000_000,
        ])->assertRedirect();

        $sale = Sale::firstOrFail();
        $this->assertSame(3, $sale->quantity);
        $this->assertSame(7, $property->fresh()->unitsRemaining());

        $this->post(route('admin.sales.approve', $sale))->assertRedirect();

        // Still on the market: a multi-unit listing never auto-flips to sold.
        $this->assertSame('available', $property->fresh()->status);
        $this->assertSame(7, $property->fresh()->unitsRemaining());
        $this->assertEquals(150_000, Commission::where('sale_id', $sale->id)->first()->amount); // 5% of 3,000,000
    }

    public function test_a_second_sale_cannot_exceed_the_remaining_units(): void
    {
        $realtor = $this->realtor();
        $property = Property::create([
            'title' => 'Small Estate', 'slug' => 'small-estate', 'sector' => 'real_estate', 'price' => 500_000,
            'units_total' => 5, 'location' => 'Enugu', 'status' => 'available', 'created_by' => $realtor->id,
        ]);

        $admin = $this->admin();
        $session = $this->actingAs($admin)->withSession(['two_factor_passed' => true, 'step_up_at' => time()]);
        $session->post(route('admin.sales.store'), [
            'property_id' => $property->id, 'realtor_id' => $realtor->id, 'buyer_name' => 'Buyer A', 'quantity' => 5, 'amount' => 2_500_000,
        ]);

        $session->post(route('admin.sales.store'), [
            'property_id' => $property->id, 'realtor_id' => $realtor->id, 'buyer_name' => 'Buyer B', 'quantity' => 1, 'amount' => 500_000,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(1, Sale::count());
    }

    public function test_cancelling_a_single_unit_sale_returns_the_property_to_the_market(): void
    {
        $realtor = $this->realtor();
        $property = Property::create([
            'title' => 'One House', 'slug' => 'one-house', 'sector' => 'real_estate', 'price' => 20_000_000,
            'location' => 'Awka', 'status' => 'available', 'created_by' => $realtor->id,
        ]);

        $sale = new Sale(['property_id' => $property->id, 'realtor_id' => $realtor->id, 'buyer_name' => 'Buyer', 'amount' => 20_000_000, 'quantity' => 1]);
        $sale->status = 'pending';
        $sale->save();

        $session = $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()]);
        $session->post(route('admin.sales.approve', $sale));
        $this->assertSame('sold', $property->fresh()->status);

        $session->post(route('admin.sales.cancel', $sale));
        $this->assertSame('available', $property->fresh()->status);
    }

    /* ---------- Withdrawals ---------- */

    private function paidSaleFor(User $realtor, float $amount): Sale
    {
        $property = Property::create([
            'title' => 'Plot '.uniqid(), 'slug' => 'plot-'.uniqid(), 'sector' => 'real_estate', 'price' => $amount,
            'location' => 'Awka', 'status' => 'available', 'created_by' => $realtor->id,
        ]);

        $sale = new Sale(['property_id' => $property->id, 'realtor_id' => $realtor->id, 'buyer_name' => 'Buyer', 'amount' => $amount, 'quantity' => 1]);
        $sale->status = 'pending';
        $sale->save();

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()])
            ->post(route('admin.sales.approve', $sale));

        return $sale;
    }

    public function test_a_realtor_can_request_a_withdrawal_up_to_their_pending_balance(): void
    {
        $realtor = $this->realtor();
        $this->paidSaleFor($realtor, 2_000_000); // 5% = 100,000 pending commission

        $this->actingAs($realtor)->post(route('realtor.withdrawals.store'), ['amount' => 60_000])->assertRedirect();

        $this->assertDatabaseHas('withdrawals', ['user_id' => $realtor->id, 'amount' => 60_000, 'status' => 'pending']);
        $this->assertEquals(40_000, $realtor->availableBalance()); // the rest is tied up by the pending request
    }

    public function test_a_withdrawal_above_the_available_balance_is_rejected(): void
    {
        $realtor = $this->realtor();
        $this->paidSaleFor($realtor, 1_000_000); // 5% = 50,000

        $this->actingAs($realtor)->post(route('realtor.withdrawals.store'), ['amount' => 100_000])
            ->assertSessionHasErrors('amount');
    }

    public function test_a_withdrawal_requires_bank_details_on_file(): void
    {
        $realtor = User::factory()->create(); // no bank details
        $this->paidSaleFor($realtor, 1_000_000);

        $this->actingAs($realtor)->post(route('realtor.withdrawals.store'), ['amount' => 10_000])
            ->assertRedirect()->assertSessionHas('error');

        $this->assertSame(0, Withdrawal::count());
    }

    public function test_paying_a_withdrawal_settles_the_realtors_pending_commissions_and_notifies_them(): void
    {
        $realtor = $this->realtor();
        $this->paidSaleFor($realtor, 2_000_000); // 100,000 pending commission

        $this->actingAs($realtor)->post(route('realtor.withdrawals.store'), ['amount' => 100_000]);
        $withdrawal = Withdrawal::firstOrFail();

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()])
            ->post(route('admin.withdrawals.pay', $withdrawal), ['reference' => 'PAY-1'])->assertRedirect();

        $this->assertSame('paid', $withdrawal->fresh()->status);
        $this->assertSame('paid', Commission::where('user_id', $realtor->id)->first()->status);
        $this->assertSame(2, $realtor->unreadNotifications()->count());
    }

    public function test_rejecting_a_withdrawal_frees_the_balance_again_and_notifies_the_realtor(): void
    {
        $realtor = $this->realtor();
        $this->paidSaleFor($realtor, 2_000_000);

        $this->actingAs($realtor)->post(route('realtor.withdrawals.store'), ['amount' => 100_000]);
        $withdrawal = Withdrawal::firstOrFail();
        $this->assertEquals(0, $realtor->availableBalance());

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()])
            ->post(route('admin.withdrawals.reject', $withdrawal), ['notes' => 'Bank details mismatch']);

        $this->assertSame('rejected', $withdrawal->fresh()->status);
        $this->assertEquals(100_000, $realtor->availableBalance());
        $this->assertSame(2, $realtor->unreadNotifications()->count());
    }
}
