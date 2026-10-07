<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * In-app alert shown in the notification centre of the realtor and admin areas.
 * Subclasses only describe the message; delivery is the database channel.
 */
abstract class AppNotification extends Notification
{
    /** @return array{title: string, body: string, url: string|null, icon: string} */
    abstract protected function payload(): array;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }
}
