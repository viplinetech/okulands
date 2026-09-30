<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionSetting extends Model
{
    protected $fillable = ['tier', 'rate'];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
        ];
    }
}
