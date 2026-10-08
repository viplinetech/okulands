<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Providers;

use App\Models\SiteSetting;
use App\Support\Audit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // c(), ch(), cl(), cp(): admin-editable page wording for Blade views.
        require_once app_path('Support/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Some hosts still run MySQL/MariaDB with the older 767-byte index-key limit (no "large
        // prefix" / Barracuda row format), which errors on a utf8mb4 varchar(255) unique/primary
        // key. 191 chars still comfortably fits an email address and keeps the index within limits.
        Schema::defaultStringLength(191);

        // Every public-facing view (and the auth screens) can read the admin-managed settings.
        View::composer(
            ['components.layouts.public', 'components.layouts.realtor', 'components.layouts.admin', 'components.layouts.standalone', 'layouts.guest', 'home', 'pages.*', 'properties.*', 'blog.*', 'errors.*'],
            fn ($view) => $view->with('settings', SiteSetting::current())
        );

        // Password rules for every account: 10+ characters, mixed case, a number; in production
        // also checked against known data breaches.
        Password::defaults(fn () => app()->isProduction()
            ? Password::min(8)->mixedCase()->numbers()->uncompromised()
            : Password::min(8)->mixedCase()->numbers());

        $this->registerAuthAuditing();
    }

    /** Sign-in trail + audit log for successful, failed, locked-out and ended sessions. */
    private function registerAuthAuditing(): void
    {
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;
            $user->forceFill(['last_login_at' => now(), 'last_login_ip' => request()->ip()])->saveQuietly();
            Audit::log('auth.login', $user, $user->name.' signed in', [], $user->id);
        });

        Event::listen(Failed::class, function (Failed $event) {
            Audit::log('auth.failed', null, 'Failed sign-in attempt', ['email' => strtolower((string) ($event->credentials['email'] ?? ''))]);
        });

        Event::listen(Lockout::class, function () {
            Audit::log('auth.lockout', null, 'Sign-in locked out after repeated failures', ['email' => strtolower((string) request()->input('email', ''))]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                Audit::log('auth.logout', $event->user, $event->user->name.' signed out', [], $event->user->id);
            }
        });
    }
}
