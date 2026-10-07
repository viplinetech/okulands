<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Support\SiteDefaults;

/** Static-ish content pages. All copy and imagery comes from the admin-managed models. */
class PageController extends Controller
{
    public function about()
    {
        return view('pages.about', [
            'settings' => SiteSetting::current(),
            'testimonials' => Testimonial::where('approved', true)->latest()->take(6)->get(),
        ]);
    }

    public function services()
    {
        $settings = SiteSetting::current();

        return view('pages.services', [
            'settings' => $settings,
            'services' => Service::visible()->get(),
            'faqs' => array_slice($settings->faqItems(), 0, 5),
        ]);
    }

    /** Privacy Policy and Terms of Use. The wording lives in SiteCopy and is edited in the admin. */
    public function legal(string $page)
    {
        $pages = [
            'privacy-policy' => ['Privacy Policy', 'legal.privacy', 'How we collect, use and protect your personal information.'],
            'terms' => ['Terms of Use', 'legal.terms', 'The rules for using our website and realtor programme.'],
        ];

        abort_unless(isset($pages[$page]), 404);

        [$title, $key, $summary] = $pages[$page];

        return view('pages.legal', ['settings' => SiteSetting::current(), 'title' => $title, 'key' => $key, 'summary' => $summary]);
    }

    public function gallery()
    {
        $items = GalleryItem::visible()->get()->map(fn (GalleryItem $g) => [
            'image' => $g->imageUrl(),
            'title' => $g->title,
            'caption' => $g->caption,
            'category' => $g->category,
        ]);

        return view('pages.gallery', [
            'settings' => SiteSetting::current(),
            'items' => $items->isNotEmpty() ? $items->all() : SiteDefaults::gallerySamples(),
        ]);
    }

    public function contact()
    {
        $settings = SiteSetting::current();

        return view('pages.contact', [
            'settings' => $settings,
            'faqs' => $settings->faqItems(),
            'interests' => LeadController::interests(),
        ]);
    }
}
