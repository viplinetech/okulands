<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Property;
use App\Models\ReferralVisit;
use App\Models\Sale;
use App\Models\User;
use App\Notifications\CommissionEarned;
use App\Notifications\HotLead;
use App\Notifications\NewDownline;
use App\Notifications\NewReferralLead;
use App\Services\CommissionCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * OPTIONAL, LOCAL PREVIEW ONLY: a small realtor network with leads, sales and commissions so the
 * realtor dashboard and admin can be reviewed with data. Not part of DatabaseSeeder.
 *   Run:    php artisan db:seed --class=DemoNetworkSeeder
 *   Remove: User::where('email', 'like', '%@demo.okulands.test')->delete()  (and demo leads/sales cascade)
 * Demo realtor sign-in: chinedu-okafor@demo.okulands.test / DemoPass123
 */
class DemoNetworkSeeder extends Seeder
{
    public function run(): void
    {
        $make = function (string $name, ?User $sponsor, string $phone) {
            $user = User::firstOrNew(['email' => Str::slug($name).'@demo.okulands.test']);
            $user->name = $name;
            $user->phone = $phone;
            $user->password = 'DemoPass123';
            $user->forceFill(['role' => 'realtor', 'status' => 'active', 'referred_by' => $sponsor?->id, 'email_verified_at' => now()])->save();

            return $user;
        };

        $chinedu = $make('Chinedu Okafor', null, '08031234567');
        $ada = $make('Ada Nwosu', $chinedu, '08055501122');
        $emeka = $make('Emeka Obi', $ada, '08099887766');
        $ngozi = $make('Ngozi Eze', $chinedu, '07011223344');

        $chinedu->forceFill(['bank_name' => 'Guaranty Trust Bank (GTBank)', 'account_name' => 'Chinedu Okafor', 'account_number' => '0123456789'])->save();

        // Link clicks
        foreach (range(1, 14) as $i) {
            ReferralVisit::firstOrCreate(['visitor_token' => (string) Str::uuid()], [
                'referrer_id' => $chinedu->id,
                'landing_url' => '/',
                'created_at' => now()->subDays(random_int(0, 25)),
            ]);
        }

        $property = Property::first();
        $mk = fn (string $name, string $phone, string $status, ?int $referrer, string $type = 'inspection') => Lead::firstOrCreate(
            ['name' => $name, 'phone' => $phone],
            ['email' => Str::slug($name).'@example.test', 'message' => 'Interested. Please share details.', 'type' => $type, 'property_id' => $property?->id, 'referrer_id' => $referrer, 'status' => $status, 'source' => 'website_property_page', 'created_at' => now()->subDays(random_int(0, 20))]
        );

        $leads = [
            $mk('Tunde Adeyemi', '08023456789', 'new', $chinedu->id),
            $mk('Blessing Udo', '08134567890', 'hot', $chinedu->id),
            $mk('Ifeoma Chukwu', '09012345678', 'contacted', $chinedu->id),
            $mk('Musa Bello', '08067890123', 'converted', $chinedu->id),
            $mk('Grace Okoro', '08145678901', 'closed', $chinedu->id),
            $mk('Samuel Etim', '07089012345', 'new', $ada->id),
            $mk('Walk-in enquiry', '08011112222', 'new', null, 'general'),
        ];

        // Sales -> commissions (tier 1 to the realtor, tier 2 to their sponsor)
        $calculator = app(CommissionCalculator::class);
        $sales = [
            [$chinedu, 'Musa Bello', 8_500_000, 'paid'],
            [$chinedu, 'Blessing Udo', 12_000_000, 'approved'],
            [$ada, 'Samuel Etim', 6_200_000, 'approved'],
        ];

        foreach ($sales as [$realtor, $buyer, $amount, $status]) {
            $sale = Sale::firstOrCreate(['buyer_name' => $buyer, 'realtor_id' => $realtor->id], [
                'property_id' => $property?->id ?? Property::factory()->create()->id,
                'amount' => $amount,
                'status' => $status,
                'approved_by' => User::where('role', 'admin')->value('id'),
                'approved_at' => now()->subDays(random_int(1, 20)),
                'reference' => 'RCT-'.random_int(10000, 99999),
            ]);

            $calculator->generate($sale)->each(function ($c) use ($status, $sale) {
                $c->forceFill(['created_at' => $sale->approved_at])->save();
                if ($status === 'paid') {
                    $c->update(['status' => 'paid', 'paid_at' => now()->subDays(2), 'payout_reference' => 'TRF-'.random_int(1000, 9999)]);
                }
                $c->user->notify(new CommissionEarned($c));
            });
        }

        // Alerts
        $chinedu->notify(new NewDownline($ada));
        $chinedu->notify(new NewReferralLead($leads[0]));
        if ($property) {
            $chinedu->notify(new HotLead($property, 3));
        }
        unset($emeka, $ngozi);
    }
}
