<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Each realtor gets a short, sequential referral code: OK001, OK002, OK003. */
class ReferralCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_realtors_get_sequential_ok_codes(): void
    {
        $first = User::factory()->create(['name' => 'Chinedu Okafor']);
        $second = User::factory()->create(['name' => 'Ada Obi']);
        $third = User::factory()->create(['name' => 'Bola Eze']);

        $this->assertSame(['OK001', 'OK002', 'OK003'], [$first->referral_code, $second->referral_code, $third->referral_code]);
    }

    public function test_the_code_continues_from_the_highest_existing_number(): void
    {
        User::factory()->create(['referral_code' => 'OK009']);

        $this->assertSame('OK010', User::factory()->create()->referral_code);
    }

    public function test_the_referral_link_uses_the_short_code(): void
    {
        $realtor = User::factory()->create();

        $this->get('/ref/'.$realtor->referral_code)->assertRedirect('/')->assertCookie(\App\Http\Controllers\ReferralController::COOKIE);
    }
}
