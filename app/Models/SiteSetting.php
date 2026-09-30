<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name', 'tagline', 'logo_path', 'logo_dark_path', 'favicon_path',
        'hero_images', 'phone', 'whatsapp', 'email', 'address',
        'facebook_url', 'instagram_url', 'tiktok_url', 'default_theme',
    ];

    protected function casts(): array
    {
        return [
            'hero_images' => 'array',
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

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }

    public function logoDarkUrl(): ?string
    {
        return $this->logo_dark_path ? Storage::url($this->logo_dark_path) : $this->logoUrl();
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon_path ? Storage::url($this->favicon_path) : null;
    }

    public function heroImageUrls(): array
    {
        return collect($this->hero_images ?? [])
            ->map(fn (string $path) => Storage::url($path))
            ->all();
    }
}
