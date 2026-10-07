<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    A single-purpose page for a signed-in user: no sidebar, no bottom bar, no other menu to wander off to.
    Only the actions on the page itself (and a small "sign out" escape hatch) are available.
-->
@props(['title' => 'Oku Lands'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-app.head :title="$title.' | '.$settings->site_name" :settings="$settings" />
</head>
<body class="app-body">
<div class="mx-auto flex min-h-screen w-full max-w-2xl flex-col px-5 py-6 sm:px-8 sm:py-10">
    <div class="flex items-center justify-between">
        <span class="flex items-center gap-2.5 text-ink">
            @if ($settings->logoUrl() || $settings->logoDarkUrl())
                <img src="{{ $settings->logoDarkUrl() }}" alt="{{ $settings->site_name }}" class="h-8 w-auto dark:hidden">
                <img src="{{ $settings->logoUrl() ?? $settings->logoDarkUrl() }}" alt="" aria-hidden="true" class="hidden h-8 w-auto dark:block">
            @else
                <x-brand-mark class="h-8 w-8" />
                <span class="font-serif text-xl">{{ $settings->site_name }}</span>
            @endif
        </span>
        <div class="flex items-center gap-2">
            <x-theme-toggle class="text-ink" />
            @auth
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="text-xs font-semibold text-mute underline-offset-2 hover:text-ink hover:underline">Sign out</button>
                </form>
            @endauth
        </div>
    </div>

    <main id="main" class="flex flex-1 items-center py-8">
        <div class="w-full">
            {{ $slot }}
        </div>
    </main>
</div>
</body>
</html>
