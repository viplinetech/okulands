<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Someone who came through this realtor's link booked an inspection or sent an enquiry.
 * PRIVACY: it says what happened and for which property, never who the person is.
 * Delivered both to the realtor's dashboard notification centre and to their email.
 */
class NewReferralLead extends AppNotification
{
    public function __construct(private Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    private function isInspection(): bool
    {
        return $this->lead->type === 'inspection';
    }

    protected function payload(): array
    {
        $inspection = $this->isInspection();
        $property = $this->lead->property?->title;

        return [
            'title' => $inspection ? 'Inspection booked through your link' : 'New enquiry through your link',
            'body' => $inspection
                ? 'Someone from your link booked an inspection'.($property ? ' for “'.$property.'”' : '').'. Oku Lands will follow up with them.'
                : 'Someone from your link sent an enquiry'.($property ? ' about “'.$property.'”' : '').'. Oku Lands will follow up with them.',
            'url' => route('realtor.leads'),
            'icon' => $inspection ? 'key' : 'mail',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payload = $this->payload();
        $property = $this->lead->property?->title;

        $mail = (new MailMessage)
            ->subject($payload['title'])
            ->greeting('Hello '.$notifiable->firstName().',')
            ->line($payload['body']);

        if ($property) {
            $mail->line('**Property:** '.$property);
        }

        return $mail->action('View in my dashboard', route('realtor.leads'))
            ->line('Keep an eye on your leads page so you can follow up as soon as Oku Lands reaches out to them.');
    }
}
