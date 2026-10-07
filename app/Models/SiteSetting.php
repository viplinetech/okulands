<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use App\Support\SiteDefaults;
use App\Support\StaticText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name', 'tagline', 'logo_path', 'logo_dark_path', 'favicon_path',
        'hero_images', 'phone', 'whatsapp', 'email', 'address',
        'facebook_url', 'instagram_url', 'tiktok_url', 'default_theme',
        'hero_headline', 'hero_subheadline', 'about_headline', 'about_body', 'about_image',
        'mission', 'vision', 'values', 'commitments', 'stats', 'faqs', 'team', 'banners',
        'office_hours', 'map_embed_url', 'rc_number',
        'ceo_name', 'ceo_title', 'ceo_photo', 'ceo_message', 'about_image_2', 'realtor_registration_enabled', 'copy', 'realtor_stat',
        'seo_indexing_enabled', 'seo_meta_title', 'seo_meta_description', 'seo_meta_keywords', 'google_site_verification', 'google_analytics_id',
    ];

    protected function casts(): array
    {
        return [
            'hero_images' => 'array',
            'values' => 'array',
            'commitments' => 'array',
            'stats' => 'array',
            'faqs' => 'array',
            'team' => 'array',
            'banners' => 'array',
            'copy' => 'array',
            'realtor_stat' => 'array',
            'realtor_registration_enabled' => 'boolean',
            'seo_indexing_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site-settings'));
        static::deleted(fn () => Cache::forget('site-settings'));
    }

    /** The single site-wide settings row, cached and created on first access. */
    public static function current(): self
    {
        $attributes = Cache::rememberForever('site-settings', function () {
            return static::query()->firstOrCreate(['id' => 1])->getAttributes();
        });

        return (new static)->newFromBuilder($attributes);
    }

    /** Resolve an uploaded path (or a full URL) to a public URL, with a fallback. */
    public static function media(?string $path, ?string $fallback = null): ?string
    {
        if (! $path) {
            return $fallback;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // A leading slash means a file shipped in /public (e.g. the bundled Nigeria photos).
        return str_starts_with($path, '/') ? asset(ltrim($path, '/')) : Storage::url($path);
    }

    public function logoUrl(): ?string
    {
        return static::media($this->logo_path);
    }

    public function logoDarkUrl(): ?string
    {
        return static::media($this->logo_dark_path) ?? $this->logoUrl();
    }

    public function faviconUrl(): ?string
    {
        return static::media($this->favicon_path);
    }

    /** Admin-uploaded hero images, or the stand-in set until some are uploaded. */
    public function heroImageUrls(): array
    {
        $urls = collect($this->hero_images ?? [])
            ->filter()
            ->map(fn (string $path) => static::media($path))
            ->values()
            ->all();

        return $urls ?: SiteDefaults::heroImages();
    }

    public function heroHeadline(): string
    {
        return StaticText::heroHeadline();
    }

    public function heroSubheadline(): string
    {
        return StaticText::heroSubheadline();
    }

    public function aboutHeadline(): string
    {
        return StaticText::aboutHeadline();
    }

    public function aboutImageUrl(): string
    {
        return static::media($this->about_image, SiteDefaults::aboutImage());
    }

    /** The two photos that crossfade in the home page About block (admin: about_image, about_image_2). */
    public function aboutImageUrls(): array
    {
        return [
            $this->aboutImageUrl(),
            static::media($this->about_image_2, SiteDefaults::aboutImage2()),
        ];
    }

    public function missionText(): string
    {
        return StaticText::mission();
    }

    public function visionText(): string
    {
        return StaticText::vision();
    }

    public function valueItems(): array
    {
        return StaticText::values();
    }

    public function commitmentItems(): array
    {
        return StaticText::commitments();
    }

    /** The numbers the admin types in (happy clients, properties sold and any extras). */
    public function manualStats(): array
    {
        return $this->stats ?: SiteDefaults::stats();
    }

    /** Settings of the automatic realtor counter, with defaults. */
    public function realtorStat(): array
    {
        return ((array) $this->realtor_stat) + ['show' => true, 'label' => 'Active realtors', 'suffix' => '+', 'extra' => 0];
    }

    /** Email-verified, active realtors registered on the site. */
    public static function verifiedRealtorCount(): int
    {
        return User::whereIn('role', User::AFFILIATE_ROLES)->where('status', 'active')->whereNotNull('email_verified_at')->count();
    }

    /**
     * Every counter shown on the home and About pages. The realtor figure is worked out by the system
     * (verified realtors + any existing realtors the admin adds) and always sits third.
     */
    public function statItems(): array
    {
        // The services counter is always the number of ACTIVE services, so it can't drift from the Services list.
        $items = collect($this->manualStats())->map(function ($item) {
            if (str_contains(strtolower((string) ($item['label'] ?? '')), 'service')) {
                $item['value'] = \App\Models\Service::visible()->count();
            }

            return $item;
        })->values()->all();
        $auto = $this->realtorStat();

        if ($auto['show']) {
            array_splice($items, min(2, count($items)), 0, [[
                'value' => static::verifiedRealtorCount() + (int) $auto['extra'],
                'suffix' => (string) $auto['suffix'],
                'label' => (string) $auto['label'],
            ]]);
        }

        return $items;
    }

    /**
     * Short question/answer pairs for the FAQ sections on other pages. Sourced from the
     * knowledge base (faq_items) first, then the legacy settings list, then built-in defaults.
     */
    public function faqItems(): array
    {
        $fromKb = FaqItem::visible()->get(['question', 'answer'])
            ->map(fn (FaqItem $f) => ['q' => $f->question, 'a' => $f->answer])
            ->all();

        return $fromKb ?: ($this->faqs ?: SiteDefaults::faqs());
    }

    /** The CEO's message block, or null until the admin has written one (the sections stay hidden). */
    public function ceo(): ?array
    {
        $message = StaticText::ceoMessage();

        return [
            'name' => StaticText::ceoName(),
            'title' => StaticText::ceoTitle(),
            'photo' => static::media($this->ceo_photo, \App\Support\Placeholder::url('About · CEO portrait')),
            'message' => $message,
            // Plain-text opening, trimmed at a word boundary, for the home-page teaser.
            'excerpt' => \Illuminate\Support\Str::limit(\App\Support\RichText::text($message), 260, '…', preserveWords: true),
        ];
    }

    /** Team members (name, role, optional photo path, bio). The section is hidden when empty. */
    public function teamItems(): array
    {
        return collect($this->team ?? [])
            ->filter(fn ($m) => ! empty($m['name']))
            ->map(fn ($m) => [...$m, 'photo_url' => static::media($m['photo'] ?? null, \App\Support\Placeholder::url('About · Team photo'))])
            ->values()
            ->all();
    }

    /** Banner image for an inner page: properties, about, services, gallery or contact. */
    public function bannerUrl(string $page): string
    {
        return static::media($this->banners[$page] ?? null, SiteDefaults::pageBanner($page));
    }

    /** Whether search engines are currently allowed to crawl and index the site. */
    public function indexingEnabled(): bool
    {
        return (bool) ($this->seo_indexing_enabled ?? true);
    }

    public function whatsappUrl(?string $text = null): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        return 'https://wa.me/'.$this->whatsapp.($text ? '?text='.rawurlencode($text) : '');
    }
}
