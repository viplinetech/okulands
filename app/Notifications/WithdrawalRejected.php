<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawalRejected extends AppNotification
{
    public function __construct(private Withdrawal $withdrawal) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    protected function payload(): array
    {
        return [
            'title' => 'Withdrawal declined',
            'body' => 'Your request for ₦'.number_format((float) $this->withdrawal->amount).' could not be processed.'.($this->withdrawal->notes ? ' '.$this->withdrawal->notes : ''),
            'url' => route('realtor.earnings'),
            'icon' => 'x',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $w = $this->withdrawal;

        $mail = (new MailMessage)
            ->subject('Your withdrawal request could not be processed')
            ->greeting('Hello '.$notifiable->firstName().',')
            ->line('Your request to withdraw ₦'.number_format((float) $w->amount).' could not be processed this time.');

        if ($w->notes) {
            $mail->line('**Reason:** '.$w->notes);
        }

        return $mail->line('The amount is still available in your balance, and you can submit a new request at any time.')
            ->action('View my earnings', route('realtor.earnings'));
    }
}
