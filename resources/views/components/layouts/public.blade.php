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
    <meta property="og:title" content="{{ $title ?? $settings->site_name }}">
    <meta property="og:description" content="{{ $description ?? $settings->tagline }}">
    <meta property="og:type" content="website">
    @if ($settings->logoUrl())
        <meta property="og:image" content="{{ $settings->logoUrl() }}">
    @endif

    @if ($settings->faviconUrl())
        <link rel="icon" href="{{ $settings->faviconUrl() }}">
    @endif

    <x-theme-boot-script :default="$settings->default_theme" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|fraunces:500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => $settings->site_name,
            'telephone' => $settings->phone,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->address,
                'addressRegion' => 'Anambra State',
                'addressCountry' => 'NG',
            ],
            'url' => url('/'),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES) !!}</script>
</head>
<body class="min-h-screen bg-ivory font-sans text-slate-700 antialiased transition-colors duration-300 dark:bg-navy-950 dark:text-white">

    <header
        x-data="{ scrolled: false, mobileOpen: false, transparent: {{ ($transparentHero ?? false) ? 'true' : 'false' }} }"
        x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 60)"
        :class="(!transparent || scrolled) ? 'bg-ivory/95 border-navy-900/10 dark:bg-navy-950/95 dark:border-white/10 shadow-sm' : 'bg-transparent border-transparent'"
        class="fixed inset-x-0 top-0 z-50 border-b backdrop-blur-md transition-all duration-500"
    >
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                @if ($settings->logoUrl())
                    <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->site_name }}" class="h-9 w-auto dark:hidden" width="140" height="36">
                    <img src="{{ $settings->logoDarkUrl() }}" alt="{{ $settings->site_name }}" class="hidden h-9 w-auto dark:block" width="140" height="36">
                @else
                    <x-brand-mark class="h-9 w-9" />
                    <span
                        class="font-serif text-lg font-bold leading-tight tracking-tight transition-colors duration-500"
                        :class="(!transparent || scrolled) ? 'text-navy-900 dark:text-white' : 'text-white'"
                    >{{ $settings->site_name }}</span>
                @endif
            </a>

            <nav class="hidden items-center gap-9 text-sm font-medium lg:flex">
                @foreach ([['/', 'Home'], ['/properties', 'Properties'], ['/about', 'About'], ['/about#services', 'Services'], ['/news', 'News'], ['/contact', 'Contact']] as [$href, $label])
                    <a
                        href="{{ url($href) }}"
                        class="transition-colors duration-500"
                        :class="(!transparent || scrolled) ? 'text-navy-700 hover:text-sky-600 dark:text-white/80 dark:hover:text-sky-300' : 'text-white/90 hover:text-white'"
                    >{{ $label }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-4">
                <x-theme-toggle />
                <a
                    href="{{ route('login') }}"
                    class="hidden text-sm font-semibold transition sm:inline-block"
                    :class="(!transparent || scrolled) ? 'text-navy-800 hover:text-sky-600 dark:text-white' : 'text-white hover:text-white/80'"
                >
                    Realtor Login
                </a>
                <a href="{{ url('/contact') }}" class="hidden rounded-md bg-sky-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-400 sm:inline-block">
                    Book Inspection
                </a>
                <button type="button" @click="mobileOpen = true" class="flex h-9 w-9 items-center justify-center lg:hidden" :class="(!transparent || scrolled) ? 'text-navy-900 dark:text-white' : 'text-white'" aria-label="Open menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile drawer --}}
        <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-navy-950/60" x-show="mobileOpen" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" @click="mobileOpen = false"></div>
            <div
                class="absolute right-0 top-0 flex h-full w-80 max-w-[85vw] flex-col bg-ivory p-6 dark:bg-navy-950"
                x-show="mobileOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
            >
                <div class="flex items-center justify-between">
                    <span class="font-serif text-lg font-bold text-navy-900 dark:text-white">Menu</span>
                    <button type="button" @click="mobileOpen = false" aria-label="Close menu" class="text-navy-900 dark:text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <nav class="mt-10 flex flex-col gap-6 text-base font-medium">
                    @foreach ([['/', 'Home'], ['/properties', 'Properties'], ['/about', 'About'], ['/about#services', 'Services'], ['/news', 'News'], ['/contact', 'Contact']] as [$href, $label])
                        <a href="{{ url($href) }}" class="text-navy-800 hover:text-sky-600 dark:text-white/90">{{ $label }}</a>
                    @endforeach
                </nav>
                <div class="mt-auto flex flex-col gap-3 border-t border-navy-900/10 pt-6 dark:border-white/10">
                    <a href="{{ route('login') }}" class="text-center text-sm font-semibold text-navy-800 dark:text-white">Realtor Login</a>
                    <a href="{{ url('/contact') }}" class="rounded-md bg-sky-500 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-sky-400">Book Inspection</a>
                </div>
            </div>
        </div>
    </header>

    <main class="{{ ($transparentHero ?? false) ? '' : 'pt-20' }}">
        {{ $slot }}
    </main>

    <footer class="bg-navy-950 text-white">
        {{-- Final CTA band --}}
        <div class="border-b border-white/10">
            <div class="mx-auto max-w-7xl px-5 py-16 text-center sm:px-8">
                <h2 class="font-serif text-3xl font-bold tracking-tight sm:text-4xl">Ready to secure your future?</h2>
                <p class="mx-auto mt-3 max-w-xl text-white/60">Speak with our team today or become a realtor and start earning from every referral you make.</p>
                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ url('/contact') }}" class="w-full rounded-md bg-sky-500 px-8 py-3.5 text-center text-sm font-semibold text-white transition hover:bg-sky-400 sm:w-auto">
                        Book a Free Inspection
                    </a>
                    <a href="{{ route('register') }}" class="w-full rounded-md border border-white/25 px-8 py-3.5 text-center text-sm font-semibold text-white transition hover:border-white/50 sm:w-auto">
                        Become a Realtor
                    </a>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8">
            <div class="grid grid-cols-1 gap-12 md:grid-cols-4">
                <div>
                    <div class="flex items-center gap-3">
                        @if ($settings->logoDarkUrl())
                            <img src="{{ $settings->logoDarkUrl() }}" alt="{{ $settings->site_name }}" class="h-9 w-auto">
                        @else
                            <x-brand-mark class="h-9 w-9" />
                        @endif
                        <span class="font-serif text-lg font-bold">{{ $settings->site_name }}</span>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-white/50">{{ $settings->tagline }}</p>
                </div>

                <div>
                    <h4 class="mb-4 text-xs font-semibold uppercase tracking-[0.15em] text-sky-400">Company</h4>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        <li><a href="{{ url('/about') }}" class="hover:text-white">About Us</a></li>
                        <li><a href="{{ url('/properties') }}" class="hover:text-white">Properties</a></li>
                        <li><a href="{{ url('/news') }}" class="hover:text-white">News</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 text-xs font-semibold uppercase tracking-[0.15em] text-sky-400">Sectors</h4>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        <li>Real Estate</li>
                        <li>Construction</li>
                        <li>Agriculture</li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 text-xs font-semibold uppercase tracking-[0.15em] text-sky-400">Contact</h4>
                    <ul class="space-y-3 text-sm text-white/60">
                        @if ($settings->whatsapp)
                            <li>
                                <a href="https://wa.me/{{ $settings->whatsapp }}" target="_blank" rel="noopener" class="font-semibold text-white hover:text-sky-400">
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

            <div class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 text-xs text-white/40 sm:flex-row">
                <p>&copy; {{ now()->year }} {{ $settings->site_name }}. All rights reserved.</p>
                <p>Crafted &amp; Developed by <a href="https://www.viplinetech.com" target="_blank" rel="noopener" class="font-semibold text-sky-400 hover:text-sky-300">Vipline Technologies Limited</a></p>
            </div>
        </div>
    </footer>

</body>
</html>
