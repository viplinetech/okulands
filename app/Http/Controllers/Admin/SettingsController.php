<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ImageProcessor;
use App\Support\Audit;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Everything the public website shows that is not a list of records:
 * branding, hero, About, CEO, team, banners, contact details and programme switches.
 */
class SettingsController extends Controller
{
    public const BANNER_PAGES = [
        'properties' => 'Properties', 'about' => 'About Us', 'services' => 'Services',
        'gallery' => 'Gallery', 'blog' => 'Blog', 'contact' => 'Contact & FAQ',
    ];

    public function edit()
    {
        return view('admin.settings', ['s' => SiteSetting::current(), 'bannerPages' => self::BANNER_PAGES]);
    }

    public function update(Request $request, ImageProcessor $images): RedirectResponse
    {
        $s = SiteSetting::current();

        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'default_theme' => ['required', 'in:light,dark'],
            'rc_number' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'regex:/^\d{8,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:400'],
            'office_hours' => ['nullable', 'string', 'max:160'],
            'map_embed_url' => ['nullable', 'url:https', 'max:1000'],
            'facebook_url' => ['nullable', 'url:https', 'max:255'],
            'instagram_url' => ['nullable', 'url:https', 'max:255'],
            'tiktok_url' => ['nullable', 'url:https', 'max:255'],
            'seo_meta_title' => ['nullable', 'string', 'max:70'],
            'seo_meta_description' => ['nullable', 'string', 'max:320'],
            'seo_meta_keywords' => ['nullable', 'string', 'max:500'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'google_analytics_id' => ['nullable', 'string', 'max:40'],
            'realtor_stat.label' => ['nullable', 'string', 'max:60'],
            'realtor_stat.suffix' => ['nullable', 'string', 'max:4'],
            'realtor_stat.extra' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'stats' => ['nullable', 'array', 'max:8'],
            'stats.*.label' => ['nullable', 'string', 'max:60'],
            'stats.*.value' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'stats.*.suffix' => ['nullable', 'string', 'max:4'],
            'team' => ['nullable', 'array', 'max:16'],
            'team.*.name' => ['nullable', 'string', 'max:120'],
            'team.*.role' => ['nullable', 'string', 'max:120'],
            'team.*.bio' => ['nullable', 'string', 'max:400'],
            'team.*.photo' => ['nullable', 'string', 'max:255'],
            'hero_images.*' => ['nullable', 'file', 'max:12288'],
            'remove_hero_images' => ['nullable', 'array'],
        ], [
            'whatsapp.regex' => 'WhatsApp must be digits only with the country code, e.g. 2348012345678.',
            'map_embed_url.url' => 'The map link must be a secure https:// address.',
        ]);

        try {
            $update = collect($data)->only([
                'site_name', 'tagline', 'default_theme', 'rc_number', 'phone', 'whatsapp', 'email',
                'address', 'office_hours', 'map_embed_url', 'facebook_url', 'instagram_url', 'tiktok_url',
                'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords', 'google_site_verification', 'google_analytics_id',
            ])->map(fn ($v) => $v === '' ? null : $v)->all();

            $update['realtor_registration_enabled'] = $request->boolean('realtor_registration_enabled');
            $update['seo_indexing_enabled'] = $request->boolean('seo_indexing_enabled');

            // Single images: logo, dark logo, favicon, About photos, CEO portrait
            foreach ([
                'logo_path' => ['logo', 'branding', 600], 'logo_dark_path' => ['logo_dark', 'branding', 600], 'favicon_path' => ['favicon', 'branding', 128],
                'about_image' => ['about_image', 'about', 1800], 'about_image_2' => ['about_image_2', 'about', 1800], 'ceo_photo' => ['ceo_photo', 'team', 900],
            ] as $column => [$input, $folder, $width]) {
                $update[$column] = $this->image($request, $images, $input, $s->{$column}, $folder, $width);
            }

            // Hero images (several)
            $hero = array_values((array) ($s->hero_images ?? []));
            foreach ((array) $request->input('remove_hero_images', []) as $path) {
                if (in_array($path, $hero, true)) {
                    $images->delete($path);
                    $hero = array_values(array_diff($hero, [$path]));
                }
            }
            foreach ((array) $request->file('hero_images', []) as $file) {
                if (count($hero) < 8) {
                    $hero[] = $images->store($file, 'hero', 2200, 78);
                }
            }
            $update['hero_images'] = $hero;

            // Page banners
            $banners = (array) ($s->banners ?? []);
            foreach (array_keys(self::BANNER_PAGES) as $page) {
                $banners[$page] = $this->image($request, $images, 'banner_'.$page, $banners[$page] ?? null, 'banners', 2200, 78);
                if (! $banners[$page]) {
                    unset($banners[$page]);
                }
            }
            $update['banners'] = $banners;

            // Automatic realtor counter: only its label, suffix and "existing realtors" figure are typed in.
            $update['realtor_stat'] = [
                'show' => $request->boolean('realtor_stat_show'),
                'label' => trim((string) ($data['realtor_stat']['label'] ?? '')) ?: 'Active realtors',
                'suffix' => (string) ($data['realtor_stat']['suffix'] ?? '+'),
                'extra' => (int) ($data['realtor_stat']['extra'] ?? 0),
            ];

            // Repeaters
            $update['stats'] = collect($data['stats'] ?? [])
                ->filter(fn ($r) => filled($r['label'] ?? null) && ($r['value'] ?? '') !== '')
                ->map(fn ($r) => ['value' => (int) $r['value'], 'suffix' => (string) ($r['suffix'] ?? ''), 'label' => $r['label']])->values()->all() ?: null;


            $team = [];
            foreach ((array) ($data['team'] ?? []) as $key => $row) {
                if (! filled($row['name'] ?? null)) {
                    continue;
                }
                $photo = $row['photo'] ?? null;
                if ($request->boolean("team_remove.$key")) {
                    $images->delete($photo);
                    $photo = null;
                }
                if ($file = $request->file("team_files.$key")) {
                    $images->delete($photo);
                    $photo = $images->store($file, 'team', 900);
                }
                $team[] = ['name' => $row['name'], 'role' => (string) ($row['role'] ?? ''), 'bio' => (string) ($row['bio'] ?? ''), 'photo' => $photo];
            }
            $update['team'] = $team ?: null;
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['upload' => $e->getMessage()]);
        }

        $indexingChanged = $update['seo_indexing_enabled'] !== (bool) ($s->seo_indexing_enabled ?? true);

        $s->update($update);

        Audit::log('admin.settings.updated', null, 'Website settings updated');
        if ($indexingChanged) {
            Audit::log('admin.seo.indexing_toggled', null, $update['seo_indexing_enabled']
                ? 'Search engine indexing turned ON — the site is crawlable again'
                : 'Search engine indexing turned OFF — the site now tells every crawler not to index it');
        }

        return back()->with('success', 'Website settings saved. Changes are live now.');
    }

    /** Replace / remove a single image field. Returns the path to store (or null). */
    private function image(Request $request, ImageProcessor $images, string $input, ?string $current, string $folder, int $width, int $quality = 82): ?string
    {
        if ($request->boolean('remove_'.$input)) {
            $images->delete($current);
            $current = null;
        }

        if ($request->hasFile($input)) {
            $stored = $images->store($request->file($input), $folder, $width, $quality);
            $images->delete($current);
            $current = $stored;
        }

        return $current;
    }
}
