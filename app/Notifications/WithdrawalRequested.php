<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Notifications\Messages\MailMessage;

/** Sent to every active admin when a realtor requests a withdrawal of their earnings. */
class WithdrawalRequested extends AppNotification
{
    public function __construct(private Withdrawal $withdrawal) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    protected function payload(): array
    {
        $w = $this->withdrawal;

        return [
            'title' => 'Withdrawal requested',
            'body' => $w->user->name.' requested ₦'.number_format((float) $w->amount).'.',
            'url' => route('admin.withdrawals.index'),
            'icon' => 'wallet',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $w = $this->withdrawal;

        return (new MailMessage)
            ->subject('Withdrawal requested: '.$w->user->name)
            ->greeting('Withdrawal requested')
            ->line('**Realtor:** '.$w->user->name)
            ->line('**Amount:** ₦'.number_format((float) $w->amount))
            ->line('**Bank:** '.($w->bank_name ?: '—').' · '.($w->account_name ?: '—').' · '.$w->maskedAccountNumber())
            ->action('Review in the admin', route('admin.withdrawals.index'))
            ->line('Pay or reject it from the Withdrawals page.');
    }
}
