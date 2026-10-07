<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace Database\Seeders;

use App\Models\NewsPost;
use App\Models\Property;
use App\Models\User;
use App\Support\SiteDefaults;
use Illuminate\Database\Seeder;

/**
 * OPTIONAL and for local previews only. Adds sample listings so the
 * Properties page can be reviewed with data. NOT part of DatabaseSeeder.
 * Run with:  php artisan db:seed --class=DemoContentSeeder
 * Remove with: Property::where('slug','like','demo-%')->delete(); NewsPost::where('slug','like','demo-%')->delete();
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        if (! $admin) {
            $this->command?->warn('No user found; run AdminUserSeeder first.');

            return;
        }

        $img = fn (string ...$ids) => array_map(fn ($id) => SiteDefaults::ngPath($id), $ids);

        $rows = [
            ['Sample: Prime Residential Plot, Awka', 'real_estate', 'Land', 'Amansea, Awka', 8500000, '600', null, null, $img('34432716', '39004741', '27938900'), 'available', true],
            ['Sample: Executive 4-Bedroom Duplex', 'real_estate', 'House', 'Awka G.R.A', 65000000, '450', '4', '4', $img('36622014', '36622005', '34557960'), 'available', true],
            ['Sample: Commercial Land, Enugu Road', 'real_estate', 'Land', 'Enugu Old Road, Awka', 22000000, '1200', null, null, $img('38512126', '27938904'), 'reserved', true],
            ['Sample: 5-Hectare Farmland', 'agriculture', 'Farmland', 'Anambra State', 12000000, '50000', null, null, $img('32847478', '32956482', '32860685'), 'available', true],
            ['Sample: Build-Ready Estate Plot', 'construction', 'Land', 'Awka South', 4200000, '450', null, null, $img('38513265', '38511754'), 'available', false],
            ['Sample: Serviced Estate Plot', 'real_estate', 'Land', 'Amansea', 3500000, '450', null, null, $img('27938900'), 'sold', false],
        ];

        foreach ($rows as $i => [$title, $sector, $type, $location, $price, $size, $beds, $baths, $images, $status, $featured]) {
            Property::firstOrCreate(['slug' => 'demo-'.($i + 1)], [
                'title' => $title,
                'sector' => $sector,
                'type' => $type,
                'description' => "Sample listing for previewing the site.\n\nReplace or delete this from the admin. Real listings include verified title documents, survey details and payment options.",
                'price' => $price,
                'location' => $location,
                'bedrooms' => $beds,
                'bathrooms' => $baths,
                'size' => $size,
                'images' => $images,
                'status' => $status,
                'featured' => $featured,
                'created_by' => $admin->id,
            ]);
        }

        $posts = [
            ['Sample: 5 checks before you buy land in Nigeria', 'Buying guide', 'A short checklist to protect your purchase.', "Buying land is one of the biggest decisions you will make, so it deserves a careful process.\n\nFirst, confirm who owns the land and that the seller has the right to sell. Second, ask to see the documents and have them explained in plain terms. Third, visit the site in person before paying.\n\nFinally, make sure every payment is documented and that you receive a receipt. This is placeholder text for previewing the blog design.", '34432716'],
            ['Sample: Why farmland is a smart long-term asset', 'Agriculture', 'Land that works for you, season after season.', "Farmland combines two things investors value: a tangible asset and a productive one.\n\nThis is placeholder text used to preview the blog layout. Replace it from the admin with your own articles.", '32956482'],
            ['Sample: From plot to home, planning your build', 'Construction', null, "Once you own land, the next step is planning. Start with a clear brief and a realistic budget.\n\nThis is placeholder text used to preview the blog layout.", '38511754'],
        ];

        foreach ($posts as $i => [$title, $category, $excerpt, $body, $cover]) {
            NewsPost::firstOrCreate(['slug' => 'demo-'.($i + 1)], [
                'title' => $title,
                'category' => $category,
                'excerpt' => $excerpt,
                'body' => $body,
                'cover_image' => SiteDefaults::ngPath($cover),
                'author_id' => $admin->id,
                'published_at' => now()->subDays($i * 6 + 1),
            ]);
        }
    }
}
