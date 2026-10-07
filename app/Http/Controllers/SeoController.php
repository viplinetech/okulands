<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\NewsPost;
use App\Models\Property;
use App\Models\SiteSetting;
use Illuminate\Http\Response;

/**
 * robots.txt and sitemap.xml, both generated live from the admin's SEO settings — no static
 * files to forget about. Turning indexing off in the admin blocks every crawler instantly here,
 * without touching a single page.
 */
class SeoController extends Controller
{
    public function robots(): Response
    {
        $settings = SiteSetting::current();

        if (! $settings->indexingEnabled()) {
            $body = "User-agent: *\nDisallow: /\n";
        } else {
            $body = implode("\n", [
                'User-agent: *',
                'Disallow: /adminbackend',
                'Disallow: /realtor',
                'Disallow: /login',
                'Disallow: /register',
                'Disallow: /ref/',
                'Disallow: /two-factor-challenge',
                '',
                'Sitemap: '.url('/sitemap.xml'),
                '',
            ]);
        }

        return response($body, 200)->header('Content-Type', 'text/plain');
    }

    public function sitemap(): Response
    {
        $settings = SiteSetting::current();

        if (! $settings->indexingEnabled()) {
            // Nothing to offer a crawler while indexing is switched off.
            return response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>', 200)
                ->header('Content-Type', 'application/xml');
        }

        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('about'), 'priority' => '0.8'],
            ['loc' => route('properties.index'), 'priority' => '0.9'],
            ['loc' => route('services'), 'priority' => '0.8'],
            ['loc' => route('gallery'), 'priority' => '0.5'],
            ['loc' => route('blog.index'), 'priority' => '0.6'],
            ['loc' => route('contact'), 'priority' => '0.6'],
            ['loc' => route('faq'), 'priority' => '0.4'],
        ]);

        Property::query()->latest('updated_at')->get(['slug', 'updated_at'])->each(
            fn (Property $p) => $urls->push(['loc' => route('properties.show', $p->slug), 'lastmod' => $p->updated_at, 'priority' => '0.7'])
        );

        NewsPost::published()->get(['slug', 'updated_at'])->each(
            fn (NewsPost $p) => $urls->push(['loc' => route('blog.show', $p->slug), 'lastmod' => $p->updated_at, 'priority' => '0.6'])
        );

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
