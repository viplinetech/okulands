<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace Database\Seeders;

use App\Models\CommissionSetting;
use Illuminate\Database\Seeder;

class CommissionSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Tier 1 = the directly referring realtor, Tier 2 = their upline.
        // Admin can change these anytime from the admin dashboard.
        CommissionSetting::firstOrCreate(['tier' => 1], ['rate' => 5.00]);
        CommissionSetting::firstOrCreate(['tier' => 2], ['rate' => 2.00]);
    }
}
