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
            'heroImages' => $settings->heroImageUrls(),
            'featuredProperties' => $featuredProperties,
            'testimonials' => $testimonials,
        ]);
    }
}
