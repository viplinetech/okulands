<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace Database\Seeders;

use App\Support\SiteDefaults;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Starter services, all editable from the admin. Images are stand-ins until
 * the admin uploads real ones. Safe to re-run: existing slugs are untouched.
 */
class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Real Estate',
                'slug' => 'real-estate',
                'sector' => 'real_estate',
                'icon' => 'map',
                'is_featured' => true,
                'summary' => 'Verified residential and commercial land across prime Nigerian locations.',
                'description' => "We source, verify and sell residential and commercial land and property in locations with real growth potential.\n\nEvery listing is backed by documentation checks, and our team walks you through inspection, payment and title transfer.",
                'features' => ['Verified residential and commercial plots', 'Guided site inspections', 'Clear documentation and title transfer', 'Instalment plans on select properties'],
                'image' => SiteDefaults::ngPath('34432716'),
            ],
            [
                'title' => 'Construction',
                'slug' => 'construction',
                'sector' => 'construction',
                'icon' => 'building',
                'is_featured' => true,
                'summary' => 'Full-service architectural design and building delivery, foundation to finishing.',
                'description' => "Own the land? Our construction division takes your project from drawing board to handover.\n\nWe combine design, project management and quality workmanship so your build is delivered to plan and to standard.",
                'features' => ['Architectural design and drawings', 'Residential and commercial builds', 'Project management and supervision', 'Quality finishing and handover'],
                'image' => SiteDefaults::ngPath('38511754'),
            ],
            [
                'title' => 'Agriculture',
                'slug' => 'agriculture',
                'sector' => 'agriculture',
                'icon' => 'leaf',
                'is_featured' => true,
                'summary' => 'Sustainable farmland investment and agribusiness ventures for long-term returns.',
                'description' => "Farmland is one of the most durable assets you can hold. We offer verified agricultural land and agribusiness opportunities built for long-term, sustainable returns.\n\nFrom acquisition to guidance on use, our agriculture division supports you at every stage.",
                'features' => ['Verified farmland parcels', 'Agribusiness investment opportunities', 'Guidance on land use and management', 'Long-term sustainable returns'],
                'image' => SiteDefaults::ngPath('32956482'),
            ],
            [
                'title' => 'Title Verification',
                'slug' => 'title-verification',
                'sector' => 'general',
                'icon' => 'shield',
                'is_featured' => false,
                'summary' => 'Legal due diligence on every land purchase, so you buy with confidence.',
                'description' => 'Before any property is listed, we verify ownership and documentation. You can also request verification support on land you are considering elsewhere.',
                'features' => ['Ownership and documentation checks', 'Survey and title review', 'Clear explanation of findings'],
                'image' => SiteDefaults::ngPath('38512126'),
            ],
            [
                'title' => 'Property Consultation',
                'slug' => 'property-consultation',
                'sector' => 'general',
                'icon' => 'compass',
                'is_featured' => false,
                'summary' => 'Expert guidance on acquisition, compliance and where to invest next.',
                'description' => 'Not sure where to start? Speak with our team for honest guidance on locations, budgets, compliance and the right next step for your goals.',
                'features' => ['Location and budget guidance', 'Acquisition and compliance advice', 'Investment planning'],
                'image' => SiteDefaults::ngPath('34557960'),
            ],
            [
                'title' => 'Land in Instalments',
                'slug' => 'land-in-instalments',
                'sector' => 'general',
                'icon' => 'wallet',
                'is_featured' => false,
                'summary' => 'Flexible payment plans that make owning land more within reach.',
                'description' => 'Select properties can be paid for in structured instalments, with clear schedules and documentation so you always know where you stand.',
                'features' => ['Structured payment schedules', 'Transparent receipts and records', 'Available on select properties'],
                'image' => SiteDefaults::ngPath('27938904'),
            ],
        ];

        foreach ($services as $i => $service) {
            $row = Service::firstOrCreate(['slug' => $service['slug']], [...$service, 'sort_order' => $i + 1]);

            // Refresh rows that still carry the old third-party stock URL; an image the admin
            // has uploaded themselves is never touched.
            if (! $row->wasRecentlyCreated && str_starts_with((string) $row->image, 'https://images.unsplash.com')) {
                $row->update(['image' => $service['image']]);
            }
        }
    }
}
