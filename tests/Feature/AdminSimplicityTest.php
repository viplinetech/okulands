<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Nav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The admin should be easy to find your way around: short, task-based menu and clear pages. */
class AdminSimplicityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => ['aaaaa-bbbbb'], 'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_the_menu_is_short_and_grouped_by_task(): void
    {
        $groups = Nav::admin();

        $this->assertSame(['Today', 'Customers & sales', 'Website', 'Account'], array_keys($groups));

        $items = collect($groups)->flatten(1);
        $this->assertLessThanOrEqual(18, $items->count(), 'keep the admin menu short');

        // Every menu entry must point at a real admin page.
        $admin = $this->admin();
        foreach ($items as $item) {
            $this->actingAs($admin)->withSession(['two_factor_passed' => true])->get(route($item['route']))->assertOk();
        }
    }
}
