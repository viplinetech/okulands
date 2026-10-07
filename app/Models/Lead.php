<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'message', 'type',
        'property_id', 'referrer_id', 'status', 'view_count', 'source', 'notes', 'contacted_at',
    ];

    protected function casts(): array
    {
        return ['contacted_at' => 'datetime'];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /** A hot lead: repeated interest that should trigger a realtor alert. */
    public function isHot(): bool
    {
        return $this->view_count >= 3 || $this->status === 'hot';
    }
}
