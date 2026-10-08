<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Confirmation emailed to the visitor themselves once their inspection booking is received. */
class InspectionBooked extends Notification
{
    public function __construct(private Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;
        $property = $lead->property?->title;

        $mail = (new MailMessage)
            ->subject('Your inspection booking is received')
            ->greeting('Hello '.$lead->name.',')
            ->line('Thank you for booking an inspection with Oku Lands'.($property ? ' for “'.$property.'”' : '').'.');

        if ($property) {
            $mail->line('**Property:** '.$property);
        }

        return $mail->line('Our team will reach out to you shortly on '.($lead->phone ?: $lead->email).' to confirm a convenient date and time.')
            ->line('Thank you for choosing Oku Lands. Homes Built on Trust.');
    }
}
