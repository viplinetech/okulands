<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::firstOrCreate(['id' => 1], [
            'site_name' => 'Oku Lands & Properties',
            'tagline' => 'Buy Today, Build Tomorrow.',
            'phone' => '08085355245',
            'whatsapp' => '2348085355245',
            'address' => 'First Floor, Zim Center, Enugu Old Road, by Army Check Point Agu Awka G.R.A, Awka South L.G.A, Anambra State',
            'default_theme' => 'light',
        ]);
    }
}
