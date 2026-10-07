<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\NewsPost;
use App\Models\Property;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Support\SiteDefaults;

class HomeController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::current();

        // Featured, still-available listings first; fall back to the newest available ones.
        $featuredProperties = Property::query()
            ->where('status', 'available')
            ->orderByDesc('featured')
            ->latest()
            ->take(6)
            ->get();

        $gallery = GalleryItem::visible()->orderByDesc('is_featured')->take(6)->get()
            ->map(fn (GalleryItem $g) => ['image' => $g->imageUrl(), 'title' => $g->title, 'category' => $g->category]);

        return view('home', [
            'settings' => $settings,
            'heroImages' => $settings->heroImageUrls(),
            'featuredProperties' => $featuredProperties,
            'sectors' => Service::visible()->where('is_featured', true)->take(3)->get(),
            'services' => Service::visible()->get(),
            'gallery' => $gallery->isNotEmpty() ? $gallery->all() : array_slice(SiteDefaults::gallerySamples(), 0, 6),
            'testimonials' => Testimonial::where('approved', true)->latest()->take(6)->get(),
            'faqs' => array_slice($settings->faqItems(), 0, 4),
            'locations' => Property::locations(),
            'posts' => NewsPost::published()->take(3)->get(),
            'interests' => LeadController::interests(),
        ]);
    }
}
