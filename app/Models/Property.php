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
        'title', 'slug', 'sector', 'type', 'description', 'price', 'units_total',
        'location', 'bedrooms', 'bathrooms', 'size', 'images',
        'status', 'featured', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'units_total' => 'integer',
            'featured' => 'boolean',
        ];
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

    public const SECTORS = [
        'real_estate' => 'Real Estate',
        'construction' => 'Construction',
        'agriculture' => 'Agriculture',
    ];

    /** The property types offered in search: only those whose service is currently active. */
    public static function activeSectors(): array
    {
        $active = \App\Models\Service::visible()->pluck('sector')->unique()->all();

        return array_intersect_key(self::SECTORS, array_flip($active));
    }

    /** Distinct listing locations, used for search autocomplete. */
    public static function locations(): array
    {
        return static::query()->whereNotNull('location')->distinct()->orderBy('location')->limit(40)->pluck('location')->all();
    }

    public function sectorLabel(): string
    {
        return self::SECTORS[$this->sector] ?? ucwords(str_replace('_', ' ', $this->sector));
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
