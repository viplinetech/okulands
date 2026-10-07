<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Rejects throwaway inbox providers so verification actually proves a real, reachable person. */
class NotDisposableEmail implements ValidationRule
{
    private const DOMAINS = [
        'mailinator.com', 'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org', 'guerrillamailblock.com', 'sharklasers.com', 'grr.la',
        '10minutemail.com', '10minutemail.net', 'tempmail.com', 'temp-mail.org', 'temp-mail.io', 'tempmail.net', 'tempmailo.com', 'tempail.com',
        'yopmail.com', 'yopmail.net', 'yopmail.fr', 'trashmail.com', 'trashmail.net', 'trashmail.de', 'throwawaymail.com', 'getnada.com', 'nada.email',
        'maildrop.cc', 'dispostable.com', 'fakeinbox.com', 'mailnesia.com', 'mintemail.com', 'mohmal.com', 'emailondeck.com', 'spamgourmet.com',
        'mytemp.email', 'burnermail.io', 'discard.email', 'moakt.com', 'mail.tm', 'inboxkitten.com', 'tmpmail.org', 'tmpmail.net', 'tempinbox.com',
        'harakirimail.com', 'mailcatch.com', 'mailnull.com', 'spambox.us', 'spam4.me', 'jetable.org', 'byom.de', 'anonbox.net', 'mailforspam.com',
        'linshiyouxiang.net', 'emailfake.com', 'fakemailgenerator.com', 'generator.email', 'tempr.email', 'dropmail.me', 'minuteinbox.com',
        'cs.email', 'luxusmail.org', 'trbvm.com', 'gufum.com', 'vomoto.com', 'fexbox.org', 'inboxbear.com', 'crazymailing.com', 'mailpoof.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower((string) substr(strrchr((string) $value, '@') ?: '', 1));

        foreach (self::DOMAINS as $blocked) {
            if ($domain === $blocked || str_ends_with($domain, '.'.$blocked)) {
                $fail('Please use a permanent email address, not a temporary one.');

                return;
            }
        }
    }
}
