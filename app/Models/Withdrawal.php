<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A realtor's request to withdraw their earned commission balance to their bank account. */
class Withdrawal extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'status', 'bank_name', 'account_name', 'account_number',
        'reference', 'notes', 'decided_by', 'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'account_number' => 'encrypted',
            'decided_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /** Masked for display: only the last 4 digits are ever shown. */
    public function maskedAccountNumber(): ?string
    {
        return $this->account_number ? '•••• '.substr($this->account_number, -4) : null;
    }
}
