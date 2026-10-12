<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Someone who came through this realtor's link booked an inspection or sent an enquiry.
 * PRIVACY: shows the person's name only — no phone, email or message is shared with the realtor.
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
        $name = $this->lead->name;
        $property = $this->lead->property?->title;

        return [
            'title' => $inspection ? $name.' booked an inspection through your link' : $name.' sent an enquiry through your link',
            'body' => $inspection
                ? $name.' booked a site inspection'.($property ? ' for “'.$property.'”' : '').'. Oku Lands will follow up with them.'
                : $name.' sent an enquiry'.($property ? ' about “'.$property.'”' : '').'. Oku Lands will follow up with them.',
            'url' => route('realtor.leads'),
            'icon' => $inspection ? 'key' : 'mail',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payload = $this->payload();
        $name = $this->lead->name;
        $property = $this->lead->property?->title;

        $mail = (new MailMessage)
            ->subject($payload['title'])
            ->greeting('Hello '.$notifiable->firstName().',')
            ->line($payload['body']);

        if ($property) {
            $mail->line('**Property:** '.$property);
        }

        return $mail->action('View in my dashboard', route('realtor.leads'))
            ->line($name.' is being handled by the Oku Lands team. You will be credited once a sale is recorded.');
    }
}
