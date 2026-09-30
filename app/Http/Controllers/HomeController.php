<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\SiteSetting;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::current();

        $featuredProperties = Property::query()
            ->where('status', 'available')
            ->latest()
            ->take(6)
            ->get();

        $testimonials = Testimonial::query()
            ->where('approved', true)
            ->latest()
            ->take(6)
            ->get();

        return view('home', [
            'settings' => $settings,
            'heroImages' => $settings->heroImageUrls(),
            'featuredProperties' => $featuredProperties,
            'testimonials' => $testimonials,
            'aboutChecklist' => $this->aboutChecklist(),
            'sectors' => $this->sectors(),
            'smallFeatures' => $this->smallFeatures(),
            'whyOku' => $this->whyOku(),
            'rmsBenefits' => $this->rmsBenefits(),
            'faqs' => $this->faqs(),
        ]);
    }

    /** Placeholder copy, editable from the admin CMS in a later phase. */
    private function aboutChecklist(): array
    {
        return ['Integrity', 'Excellence', 'Due Diligence', 'Tech-Driven', 'Customer-Centricity', 'Sustainability'];
    }

    private function sectors(): array
    {
        return [
            [
                'title' => 'Real Estate',
                'desc' => 'Verified residential and commercial land across prime Nigerian locations.',
                'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Construction',
                'desc' => 'Full-service architectural design and building delivery, foundation to finishing.',
                'image' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'title' => 'Agriculture',
                'desc' => 'Sustainable farmland investment and agribusiness ventures for long-term returns.',
                'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=900&q=80',
            ],
        ];
    }

    private function smallFeatures(): array
    {
        return [
            ['title' => 'Title Verification', 'desc' => 'Legal due diligence on every land purchase.'],
            ['title' => 'Property Consultation', 'desc' => 'Expert guidance on acquisition and compliance.'],
            ['title' => 'Land in Instalments', 'desc' => 'Flexible payment plans on select properties.'],
        ];
    }

    private function whyOku(): array
    {
        return [
            ['n' => '01', 'title' => 'Verified Titles', 'desc' => 'Every property is legally verified before it reaches our listings.'],
            ['n' => '02', 'title' => 'Prime Locations', 'desc' => 'Land in areas with real, measurable growth potential.'],
            ['n' => '03', 'title' => 'Transparent Purchase', 'desc' => 'Clear documentation and honest communication, always.'],
            ['n' => '04', 'title' => 'Flexible Payment Plans', 'desc' => 'Instalment options available on select properties.'],
        ];
    }

    private function rmsBenefits(): array
    {
        return [
            ['title' => 'Personal Referral Link', 'desc' => 'Your own unique link, ready the moment you register.'],
            ['title' => 'Real-Time Dashboard', 'desc' => 'Track clicks, leads and conversions as they happen.'],
            ['title' => 'Downline Commissions', 'desc' => 'Earn a share from realtors you bring into the network.'],
            ['title' => 'Transparent Payouts', 'desc' => 'Know exactly what you have earned and when it is paid.'],
        ];
    }

    private function faqs(): array
    {
        return [
            ['q' => 'How do I join the Oku Lands Realtor Management System (RMS)?', 'a' => 'Register as a realtor on our platform, get your unique referral link instantly, and start earning commission on every successful referral, no fees to join.'],
            ['q' => 'Are your land titles verified?', 'a' => 'Yes. Every property listed goes through legal verification and documentation checks before it appears on our platform.'],
            ['q' => 'Can I pay for land in instalments?', 'a' => 'Flexible payment plans are available on select properties. Contact our team to discuss options for the property you are interested in.'],
            ['q' => 'Do you offer construction services on purchased land?', 'a' => 'Yes, our construction division can handle your build from foundation to finishing once you own the land.'],
            ['q' => 'Can I become a realtor and a client at the same time?', 'a' => 'Absolutely. Many of our realtors started as clients and now earn commission by referring others while continuing to invest themselves.'],
        ];
    }
}
