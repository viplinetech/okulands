<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * No default admin password ever ships. Set ADMIN_EMAIL and ADMIN_PASSWORD in .env for a one-off
 * seed, or (preferred) run:  php artisan okulands:admin you@example.com
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('No admin created. Run: php artisan okulands:admin your@email.com');

            return;
        }

        $user = User::firstOrNew(['email' => strtolower($email)]);
        $user->name = $user->name ?: 'Oku Lands Admin';
        $user->password = $password;
        $user->forceFill(['role' => 'admin', 'status' => 'active', 'email_verified_at' => $user->email_verified_at ?? now()])->save();
    }
}
