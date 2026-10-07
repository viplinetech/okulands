<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Property;
use App\Models\Sale;
use App\Models\User;
use App\Services\CommissionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** A line of realtors: A brings B, B brings C. When C closes a sale, A and B each see their share under their recruit. */
class DownlineEarningsTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_realtor_sees_what_they_earned_from_their_recruits_line(): void
    {
        $admin = User::factory()->admin()->create();
        foreach ([1 => 10, 2 => 3, 3 => 1] as $tier => $rate) {
            \App\Models\CommissionSetting::updateOrCreate(['tier' => $tier], ['rate' => $rate]);
        }
        $a = User::factory()->create(['name' => 'Ada Root']);
        $b = User::factory()->create(['name' => 'Bola Middle', 'referred_by' => $a->id]);
        $c = User::factory()->create(['name' => 'Chidi Closer', 'referred_by' => $b->id]);

        $property = Property::create(['title' => 'Plot', 'slug' => 'plot-x', 'sector' => 'real_estate', 'price' => 1000000, 'location' => 'Awka', 'status' => 'available', 'created_by' => $admin->id]);
        $sale = new Sale(['property_id' => $property->id, 'realtor_id' => $c->id, 'buyer_name' => 'Buyer', 'amount' => 1000000]);
        $sale->status = 'approved';
        $sale->save();

        app(CommissionCalculator::class)->generate($sale);

        // Rates set in the admin (10 / 3 / 1 by default): C gets 10%, B gets 3%, A gets 1%.
        $this->assertSame(10000.0, (float) Commission::where('user_id', $a->id)->value('amount'));

        // A sees B's line, and what A earned from it.
        $this->actingAs($a)->get('/realtor/team')
            ->assertOk()
            ->assertSee('Bola Middle')
            ->assertSee("₦10,000")
            ->assertSee('Their line');

        // B sees C's line.
        $this->actingAs($b)->get('/realtor/team')->assertSee('Chidi Closer')->assertSee('₦30,000');
    }
}
