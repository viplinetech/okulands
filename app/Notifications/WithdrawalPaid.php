<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawalPaid extends AppNotification
{
    public function __construct(private Withdrawal $withdrawal) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    protected function payload(): array
    {
        return [
            'title' => 'Withdrawal paid',
            'body' => '₦'.number_format((float) $this->withdrawal->amount).' has been paid to your bank account.',
            'url' => route('realtor.earnings'),
            'icon' => 'check',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $w = $this->withdrawal;

        $mail = (new MailMessage)
            ->subject('Your withdrawal has been paid')
            ->greeting('Hello '.$notifiable->firstName().',')
            ->line('₦'.number_format((float) $w->amount).' has been paid to your account.');

        if ($w->reference) {
            $mail->line('**Reference:** '.$w->reference);
        }

        return $mail->action('View my earnings', route('realtor.earnings'));
    }
}
