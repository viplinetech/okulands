<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\User;

class NewDownline extends AppNotification
{
    public function __construct(private User $member) {}

    protected function payload(): array
    {
        return [
            'title' => 'New person in your downline',
            'body' => $this->member->name.' joined through your referral link.',
            'url' => route('realtor.downline'),
            'icon' => 'users',
        ];
    }
}
