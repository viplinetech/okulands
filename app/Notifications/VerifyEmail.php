<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/** Same email as Laravel's default, just addressed to the person by name instead of a generic "Hello!". */
class VerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Confirm your email to activate your realtor account')
            ->greeting('Hello '.$notifiable->firstName().',')
            ->line('Thank you for registering as a realtor with Oku Lands & Properties. Before you can sign in and access your dashboard, please confirm that this is your correct email address.')
            ->action('Verify Email Address', $url)
            ->line('Once verified, you will have full access to your referral link, leads and earnings.')
            ->line('If you did not create this account, no further action is required.');
    }
}
