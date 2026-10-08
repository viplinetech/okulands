<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Admin shell: fixed sidebar on desktop; on phones/tablets a top bar with a hamburger that opens a
    slide-in drawer. (No bottom bar in the admin, by design.)
-->
@props(['title' => 'Dashboard', 'wide' => true])
@php
    $user = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $counts = [
        'Leads' => \App\Models\Lead::where('status', 'new')->count(),
        'Sales' => \App\Models\Sale::where('status', 'pending')->count(),
        'Reviews' => \App\Models\Testimonial::where('approved', false)->count(),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-app.head :title="$title.' | Admin | '.$settings->site_name" :settings="$settings" />
</head>
<body class="app-body">
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-[100] focus:rounded-full focus:bg-brand focus:px-4 focus:py-2 focus:text-brand-fg">Skip to content</a>

<div class="app-shell">

    {{-- Desktop sidebar --}}
    <aside class="app-sidebar" aria-label="Admin menu">
        <a href="{{ route('admin.dashboard') }}" class="mb-2 flex items-center gap-2.5 px-2 text-ink">
            @if ($settings->logoUrl())
                <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->site_name }}" class="h-9 w-auto dark:hidden">
                <img src="{{ $settings->logoDarkUrl() }}" alt="" aria-hidden="true" class="hidden h-9 w-auto dark:block">
            @else
                <x-brand-mark class="h-9 w-9" />
            @endif
            <span class="leading-tight"><span class="block font-serif text-xl">{{ $settings->site_name ?: 'Oku Lands' }}</span><span class="block text-[0.6rem] font-bold uppercase tracking-[0.22em] text-brand">Admin console</span></span>
        </a>
        <x-app.admin-nav :counts="$counts" />
        <div class="mt-auto pt-6">
            <div class="flex items-center gap-3 rounded-2xl bg-soft p-3">
                <x-app.avatar :user="$user" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-ink">{{ $user->name }}</p>
                    <p class="truncate text-xs text-mute">Administrator</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="shrink-0 rounded-full px-3 py-1.5 text-xs font-bold text-mute transition hover:bg-soft hover:text-ink">Sign out</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-col">
        <header class="app-topbar">
            <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" data-drawer-toggle class="burger lg:hidden" aria-label="Open menu" aria-controls="admin-drawer">
                        <span></span><span></span><span></span>
                    </button>
                    <div class="min-w-0">
                        <p class="kicker hidden sm:block">Admin</p>
                        <p class="truncate font-serif text-xl leading-none text-ink lg:text-2xl">{{ $title }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="hidden items-center gap-1.5 rounded-full px-3 py-2 text-[0.8rem] font-semibold text-mute transition hover:text-ink sm:inline-flex">View website <x-icon name="external" class="h-3.5 w-3.5" /></a>
                    <x-theme-toggle class="text-ink" />
                    <a href="{{ route('admin.notifications') }}" class="icon-btn relative text-ink" aria-label="Notifications{{ $unread ? ' ('.$unread.' unread)' : '' }}">
                        <x-icon name="bell" class="h-[1.15rem] w-[1.15rem]" />
                        @if ($unread)<span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-flag-500 ring-2 ring-page"></span>@endif
                    </a>
                </div>
            </div>
        </header>

        <main id="main" class="app-main app-content {{ $wide ? 'app-content-wide' : '' }}">
            <x-app.flash />
            {{ $slot }}
        </main>
    </div>
</div>

{{-- Mobile / tablet drawer (opened by the hamburger) --}}
<div class="drawer-backdrop" data-drawer-close aria-hidden="true"></div>
<aside id="admin-drawer" class="drawer" data-drawer-panel aria-label="Admin menu">
    <div class="mb-2 flex items-center justify-between px-2">
        <span class="flex items-center gap-2.5 text-ink">
            @if ($settings->logoUrl())
                <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->site_name }}" class="h-9 w-auto dark:hidden">
                <img src="{{ $settings->logoDarkUrl() }}" alt="" aria-hidden="true" class="hidden h-9 w-auto dark:block">
            @else
                <x-brand-mark class="h-9 w-9" />
            @endif
            <span class="leading-tight"><span class="block font-serif text-xl">{{ $settings->site_name ?: 'Oku Lands' }}</span><span class="block text-[0.6rem] font-bold uppercase tracking-[0.22em] text-brand">Admin console</span></span>
        </span>
        <button type="button" data-drawer-close class="icon-action" aria-label="Close menu"><x-icon name="x" class="h-4 w-4" /></button>
    </div>
    <x-app.admin-nav :counts="$counts" />
    <div class="mt-auto space-y-2 pt-6">
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="side-link"><x-icon name="external" class="h-[1.15rem] w-[1.15rem]" /> View website</a>
        <div class="flex items-center gap-3 rounded-2xl bg-soft p-3">
            <x-app.avatar :user="$user" />
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-ink">{{ $user->name }}</p>
                <p class="truncate text-xs text-mute">Administrator</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" class="shrink-0 rounded-full px-3 py-1.5 text-xs font-bold text-mute transition hover:bg-soft hover:text-ink">Sign out</button>
            </form>
        </div>
    </div>
</aside>

<x-app.confirm />
</body>
</html>
