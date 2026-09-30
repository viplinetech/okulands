<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['client_name', 'client_role', 'avatar', 'content', 'rating', 'approved'];

    protected function casts(): array
    {
        return [
            'approved' => 'boolean',
        ];
    }
}
