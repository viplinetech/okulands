<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

/**
 * Sent to admins for every enquiry that reaches the website: in the admin notification
 * centre and by email. It says plainly whether it is a booked inspection or a contact message.
 */
class NewLeadAlert extends AppNotification
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

    private function heading(): string
    {
        return $this->isInspection() ? 'Inspection booked' : 'New contact message';
    }

    protected function payload(): array
    {
        $lead = $this->lead;
        $via = $lead->referrer ? ' (via '.$lead->referrer->name.')' : '';
        $about = $lead->property ? ' for “'.$lead->property->title.'”' : '';
        $reach = $lead->phone ?: $lead->email;
        $preview = $lead->message ? ' “'.Str::limit($lead->message, 90).'”' : '';

        return [
            'title' => $this->heading(),
            'body' => $lead->name.$about.$via.($reach ? ' · '.$reach : '').'.'.$preview,
            'url' => route('admin.leads.show', $lead),
            'icon' => $this->isInspection() ? 'key' : 'mail',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;

        $mail = (new MailMessage)
            ->subject($this->heading().': '.$lead->name)
            ->greeting($this->heading())
            ->line('**Name:** '.$lead->name);

        if ($lead->phone) {
            $mail->line('**Phone:** '.$lead->phone);
        }
        if ($lead->email) {
            $mail->line('**Email:** '.$lead->email);
        }
        if ($lead->property) {
            $mail->line('**Property:** '.$lead->property->title);
        }
        if ($lead->referrer) {
            $mail->line('**Referred by:** '.$lead->referrer->name);
        }
        if ($lead->message) {
            $mail->line('**Message:** '.$lead->message);
        }

        return $mail->action('Open in the admin', route('admin.leads.show', $lead))
            ->line('Reply quickly: fast follow-up is what turns enquiries into sales.');
    }
}
