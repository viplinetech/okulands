<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Commission;

class CommissionPaid extends AppNotification
{
    public function __construct(private Commission $commission) {}

    protected function payload(): array
    {
        return [
            'title' => 'Commission paid',
            'body' => '₦'.number_format((float) $this->commission->amount).' has been paid out to you.',
            'url' => route('realtor.earnings'),
            'icon' => 'check',
        ];
    }
}
