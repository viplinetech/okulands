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
            ->take(8)
            ->get();

        $testimonials = Testimonial::query()
            ->where('approved', true)
            ->latest()
            ->take(6)
            ->get();

        return view('home', [
            'heroImages' => $settings->heroImageUrls(),
            'featuredProperties' => $featuredProperties,
            'testimonials' => $testimonials,
            'differentiators' => $this->differentiators(),
            'services' => $this->services(),
            'principles' => $this->principles(),
            'faqs' => $this->faqs(),
        ]);
    }

    /** Placeholder copy, editable from the admin CMS in a later phase. */
    private function differentiators(): array
    {
        return [
            [
                'title' => 'Multi-Sector Expertise, One Company',
                'desc' => 'Real estate, construction and agriculture under one roof, so your land, your build, and your investment are handled by a team that understands all three.',
            ],
            [
                'title' => 'Secured & Verified Land Deals',
                'desc' => 'Every property is legally verified and properly documented before it reaches our listings, giving you peace of mind with every purchase.',
            ],
            [
                'title' => 'Realtor Management System (RMS)',
                'desc' => 'Our referral-powered platform rewards realtors and their downlines for every successful sale, easy to join, transparent to track.',
            ],
        ];
    }

    private function services(): array
    {
        return [
            ['icon' => 'M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5', 'title' => 'Property Sales', 'desc' => 'Verified residential and commercial land across prime Nigerian locations.'],
            ['icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'title' => 'Title Verification', 'desc' => 'Documentation support and due diligence on every land purchase.'],
            ['icon' => 'M3 21h18M6 21V9l6-4 6 4v12M10 21v-6h4v6', 'title' => 'Construction & Build', 'desc' => 'Full-service building delivery, from foundation to finishing.'],
        ];
    }

    private function principles(): array
    {
        return [
            [
                'title' => 'Smart Land Solutions Tailored for You',
                'desc' => "We match your dream with the right property. Our team is on hand to help you find land that suits your lifestyle, budget and goals, whether you're a first-time buyer or an experienced investor.",
            ],
            [
                'title' => 'Prime Locations You Can Trust',
                'desc' => 'We offer land in areas with real growth potential. Whether building a home, farming land, or making an investment, location is never a compromise with Oku Lands.',
            ],
            [
                'title' => 'Secure & Transparent Purchase',
                'desc' => "Every step of the buying process is protected, with clear documentation and honest communication. You'll always know exactly what you're paying for.",
            ],
        ];
    }

    private function faqs(): array
    {
        return [
            ['q' => 'How do I join the Oku Lands Realtor Management System (RMS)?', 'a' => 'Register as a realtor on our platform, get your unique referral link instantly, and start earning commission on every successful referral, no fees to join.'],
            ['q' => 'Are your land titles verified?', 'a' => 'Yes. Every property listed goes through legal verification and documentation checks before it appears on our platform.'],
            ['q' => 'Can I pay for land in installments?', 'a' => 'Flexible payment plans are available on select properties. Contact our team to discuss options for the property you are interested in.'],
            ['q' => 'Do you offer construction services on purchased land?', 'a' => 'Yes, our construction division can handle your build from foundation to finishing once you own the land.'],
            ['q' => 'Can I become a realtor and a client at the same time?', 'a' => 'Absolutely. Many of our realtors started as clients and now earn commission by referring others while continuing to invest themselves.'],
        ];
    }
}
