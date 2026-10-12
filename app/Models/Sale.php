<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'property_id', 'description', 'realtor_id', 'lead_id', 'buyer_name', 'buyer_email',
        'buyer_phone', 'amount', 'quantity', 'status', 'approved_by', 'approved_at', 'reference', 'notes',
    ];

    /** Human-readable label: property title if linked, otherwise the free-text description. */
    public function label(): string
    {
        return $this->property?->title ?? $this->description ?? 'General sale';
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'quantity' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function realtor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'realtor_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }
}
