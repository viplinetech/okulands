<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Commission;

class CommissionEarned extends AppNotification
{
    public function __construct(private Commission $commission) {}

    protected function payload(): array
    {
        return [
            'title' => 'Commission earned',
            'body' => '₦'.number_format((float) $this->commission->amount).' (tier '.$this->commission->tier.') was added to your earnings.',
            'url' => route('realtor.earnings'),
            'icon' => 'wallet',
        ];
    }
}
