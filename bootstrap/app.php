<?php

use App\Http\Middleware\EnforceTwoFactor;
use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\IdleTimeout;
use App\Http\Middleware\NoStore;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hardening headers on every web response.
        $middleware->appendToGroup('web', SecurityHeaders::class);

        // Behind a load balancer / proxy, trust its forwarded headers so HTTPS, host and IP are correct.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES') ? explode(',', env('TRUSTED_PROXIES')) : null);

        // Signed-out visitors are sent to the right door: /adminbackend for the admin area, /login otherwise.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('adminbackend*') ? route('admin.login') : route('login'));

        $middleware->alias([
            'role' => EnsureRole::class,
            'active' => EnsureAccountActive::class,
            'twofactor' => EnforceTwoFactor::class,
            'idle' => IdleTimeout::class,
            'nostore' => NoStore::class,
            'stepup' => \App\Http\Middleware\RequireStepUp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
