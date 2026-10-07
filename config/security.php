<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Extra confirmation for sensitive admin actions
    |--------------------------------------------------------------------------
    | Off by default: the two-factor code at sign-in is the protection. Set ADMIN_STEP_UP=true in .env to also
    | ask for a fresh code before payments, sales, realtor account changes and exports (see RequireStepUp).
    */
    'step_up' => (bool) env('ADMIN_STEP_UP', false),

    /*
    | Two-factor is required for admins. A new admin (or one whose 2FA was reset) gets this many days to set it up,
    | with a reminder banner, before the rest of the admin is locked until they do. 0 = required immediately.
    */
    'admin_2fa_grace_days' => (int) env('ADMIN_2FA_GRACE_DAYS', 7),

    // "Trust this device" on the two-factor screen: how long the code can be skipped on that browser.
    'trusted_device_days' => (int) env('TRUSTED_DEVICE_DAYS', 30),

];
