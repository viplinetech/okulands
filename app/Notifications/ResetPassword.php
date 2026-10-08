<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/** Same email as Laravel's default, just addressed to the person by name instead of a generic "Hello!". */
class ResetPassword extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Reset your password')
            ->greeting('Hello '.$notifiable->firstName().',')
            ->line('We received a request to reset the password for your account. Click the button below to choose a new one.')
            ->action('Reset Password', $url)
            ->line('This link will expire in '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
