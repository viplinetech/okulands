<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? $settings->site_name }} | {{ $settings->tagline }}</title>
    <meta name="description" content="{{ $description ?? $settings->tagline }}">

    @if ($settings->faviconUrl())
        <link rel="icon" href="{{ $settings->faviconUrl() }}">
    @endif

    <x-theme-boot-script :default="$settings->default_theme" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|playfair-display:600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-navy-900 antialiased transition-colors duration-300 dark:bg-navy-950 dark:text-white">

    <header class="sticky top-0 z-50 border-b border-navy-100/70 bg-white/85 backdrop-blur-md transition-colors duration-300 dark:border-white/10 dark:bg-navy-950/85">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                @if ($settings->logoUrl())
                    <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->site_name }}" class="h-10 w-auto dark:hidden">
                    <img src="{{ $settings->logoDarkUrl() }}" alt="{{ $settings->site_name }}" class="hidden h-10 w-auto dark:block">
                @else
                    <x-brand-mark class="h-10 w-10" />
                    <span class="font-serif text-lg font-extrabold leading-tight">
                        {{ $settings->site_name }}
                    </span>
                @endif
            </a>

            <nav class="hidden items-center gap-8 text-sm font-semibold lg:flex">
                <a href="{{ url('/') }}" class="text-navy-700 transition hover:text-gold-600 dark:text-white/80 dark:hover:text-gold-400">Home</a>
                <a href="{{ url('/properties') }}" class="text-navy-700 transition hover:text-gold-600 dark:text-white/80 dark:hover:text-gold-400">Properties</a>
                <a href="{{ url('/about') }}" class="text-navy-700 transition hover:text-gold-600 dark:text-white/80 dark:hover:text-gold-400">About</a>
                <a href="{{ url('/news') }}" class="text-navy-700 transition hover:text-gold-600 dark:text-white/80 dark:hover:text-gold-400">News</a>
                <a href="{{ url('/contact') }}" class="text-navy-700 transition hover:text-gold-600 dark:text-white/80 dark:hover:text-gold-400">Contact</a>
            </nav>

            <div class="flex items-center gap-3">
                <x-theme-toggle />
                <a href="{{ route('login') }}" class="hidden rounded-full border border-navy-200 px-5 py-2 text-sm font-semibold text-navy-800 transition hover:border-gold-400 hover:text-gold-600 dark:border-white/20 dark:text-white sm:inline-block">
                    Realtor Login
                </a>
                <a href="{{ url('/contact') }}" class="rounded-full bg-gradient-to-r from-gold-500 to-gold-600 px-5 py-2 text-sm font-bold text-navy-950 shadow-premium transition hover:shadow-premium-lg hover:-translate-y-0.5">
                    Book Inspection
                </a>
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-navy-800 bg-navy-950 text-white">
        <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8">
            <div class="grid grid-cols-1 gap-12 md:grid-cols-4">
                <div>
                    <div class="flex items-center gap-3">
                        @if ($settings->logoDarkUrl())
                            <img src="{{ $settings->logoDarkUrl() }}" alt="{{ $settings->site_name }}" class="h-10 w-auto">
                        @else
                            <x-brand-mark class="h-10 w-10" />
                        @endif
                        <span class="font-serif text-lg font-extrabold">{{ $settings->site_name }}</span>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-white/60">{{ $settings->tagline }}</p>
                </div>

                <div>
                    <h4 class="mb-4 text-xs font-bold uppercase tracking-widest text-gold-400">Company</h4>
                    <ul class="space-y-2 text-sm text-white/70">
                        <li><a href="{{ url('/about') }}" class="hover:text-white">About Us</a></li>
                        <li><a href="{{ url('/properties') }}" class="hover:text-white">Properties</a></li>
                        <li><a href="{{ url('/news') }}" class="hover:text-white">News</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 text-xs font-bold uppercase tracking-widest text-gold-400">Sectors</h4>
                    <ul class="space-y-2 text-sm text-white/70">
                        <li>Real Estate</li>
                        <li>Construction</li>
                        <li>Agriculture</li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 text-xs font-bold uppercase tracking-widest text-gold-400">Contact</h4>
                    <ul class="space-y-3 text-sm text-white/70">
                        @if ($settings->whatsapp)
                            <li>
                                <a href="https://wa.me/{{ $settings->whatsapp }}" target="_blank" rel="noopener" class="font-bold text-white hover:text-gold-400">
                                    WhatsApp: {{ $settings->phone }}
                                </a>
                            </li>
                        @endif
                        @if ($settings->email)
                            <li><a href="mailto:{{ $settings->email }}" class="hover:text-white">{{ $settings->email }}</a></li>
                        @endif
                        @if ($settings->address)
                            <li>{{ $settings->address }}</li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 text-xs text-white/50 sm:flex-row">
                <p>&copy; {{ now()->year }} {{ $settings->site_name }}. All rights reserved.</p>
                <p>Crafted &amp; Developed by <a href="https://www.viplinetech.com" target="_blank" rel="noopener" class="font-semibold text-gold-400 hover:text-gold-300">Vipline Technologies Limited</a></p>
            </div>
        </div>
    </footer>

</body>
</html>
