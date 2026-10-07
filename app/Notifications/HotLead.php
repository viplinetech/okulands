<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Property;

/** A referred visitor keeps returning to the same property: a serious buyer worth a follow-up. */
class HotLead extends AppNotification
{
    public function __construct(private Property $property, private int $views) {}

    protected function payload(): array
    {
        return [
            'title' => 'Hot lead: follow up now',
            'body' => 'A visitor from your link has viewed “'.$this->property->title.'” '.$this->views.' times.',
            'url' => route('realtor.leads'),
            'icon' => 'tag',
        ];
    }
}
