<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use App\Support\SiteDefaults;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'title', 'slug', 'sector', 'type', 'description', 'price', 'discounted_price', 'units_total',
        'location', 'bedrooms', 'bathrooms', 'size', 'images',
        'status', 'featured', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'discounted_price' => 'decimal:2',
            'units_total' => 'integer',
            'featured' => 'boolean',
        ];
    }

    /** True when a discounted price is set and lower than the regular price. */
    public function hasDiscount(): bool
    {
        return filled($this->discounted_price) && (float) $this->discounted_price < (float) $this->price;
    }

    /** The price to show to visitors: discounted if one is set, otherwise full price. */
    public function displayPrice(): string
    {
        return number_format((float) ($this->hasDiscount() ? $this->discounted_price : $this->price));
    }

    /** Savings amount when a discount is active. */
    public function savings(): string
    {
        return $this->hasDiscount() ? number_format((float) $this->price - (float) $this->discounted_price) : '0';
    }

    /** Whether this listing is sold off in multiple units/plots rather than as one whole. */
    public function isMultiUnit(): bool
    {
        return $this->units_total !== null;
    }

    /** Units already spoken for: every sale not cancelled reserves its quantity. */
    public function unitsSold(): int
    {
        return (int) $this->sales()->whereIn('status', ['pending', 'approved', 'paid'])->sum('quantity');
    }

    /** Units still available to sell. Null when this listing isn't divided into units. */
    public function unitsRemaining(): ?int
    {
        return $this->isMultiUnit() ? max(0, $this->units_total - $this->unitsSold()) : null;
    }

    public function isSoldOut(): bool
    {
        return $this->isMultiUnit() && $this->unitsRemaining() <= 0;
    }

    /** Legacy sector → label map; kept so old property records with underscore keys still display correctly. */
    public const SECTORS = [
        'real_estate' => 'Real Estate',
        'construction' => 'Construction',
        'agriculture' => 'Agriculture',
    ];

    /** Service slug → title map from the live services table, for the property form and filters. */
    public static function serviceOptions(): array
    {
        return \App\Models\Service::visible()->orderBy('sort_order')->pluck('title', 'slug')->all();
    }

    /** For the public search filter: active services from the DB, keyed by their slug. */
    public static function activeSectors(): array
    {
        return \App\Models\Service::visible()->orderBy('sort_order')->pluck('title', 'slug')->all();
    }

    /** Distinct listing locations, used for search autocomplete. */
    public static function locations(): array
    {
        return static::query()->whereNotNull('location')->distinct()->orderBy('location')->limit(40)->pluck('location')->all();
    }

    /** Human-readable service label: tries the live services table first, then the legacy constant. */
    public function sectorLabel(): string
    {
        static $map = null;
        $map ??= \App\Models\Service::query()->pluck('title', 'slug')->all();
        return $map[$this->sector]
            ?? self::SECTORS[$this->sector]
            ?? ucwords(str_replace(['_', '-'], ' ', $this->sector));
    }

    /** All uploaded image URLs (paths or full URLs), in the admin's order. */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->filter()
            ->map(fn (string $path) => SiteSetting::media($path))
            ->values()
            ->all();
    }

    public function coverUrl(): string
    {
        return $this->imageUrls()[0] ?? \App\Support\Placeholder::url('Properties · Listing photo');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
