<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
@props(['title' => null, 'description' => null, 'image' => null, 'hideFab' => false])
@php
    $nav = [
        ['Home', route('home'), 'home'],
        ['About Us', route('about'), 'about'],
        ['Properties', route('properties.index'), 'properties.*'],
        ['Services', route('services'), 'services'],
        ['Gallery', route('gallery'), 'gallery'],
        ['Blog', route('blog.index'), 'blog.*'],
        ['Contact Us', route('contact'), 'contact'],
    ];
    $pageTitle = $title ? $title.' | '.$settings->site_name : ($settings->seo_meta_title ?: $settings->site_name.' | '.\App\Support\StaticText::tagline());
    $pageDesc = $description ?: ($settings->seo_meta_description ?: \App\Support\StaticText::tagline());
    $ogImage = $image ?: $settings->logoUrl();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F5F8FC">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    @if ($settings->seo_meta_keywords)
        <meta name="keywords" content="{{ $settings->seo_meta_keywords }}">
    @endif
    <meta name="robots" content="{{ $settings->indexingEnabled() ? 'index, follow' : 'noindex, nofollow' }}">
    @if ($settings->google_site_verification)
        <meta name="google-site-verification" content="{{ $settings->google_site_verification }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">
    <meta property="og:title" content="{{ $title ?? $settings->site_name }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    @if ($settings->google_analytics_id && $settings->indexingEnabled())
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings->google_analytics_id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '{{ $settings->google_analytics_id }}');
        </script>
    @endif

    @if ($settings->faviconUrl())
        <link rel="icon" href="{{ $settings->faviconUrl() }}">
    @endif

    {{-- Applies the saved (or admin-default, light) theme before first paint, and gates reveal styles behind JS. --}}
    <x-theme-boot-script :default="$settings->default_theme ?? 'light'" />
    <script>document.documentElement.classList.add('js');</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => $settings->site_name,
            'telephone' => $settings->phone,
            'email' => $settings->email,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->address,
                'addressRegion' => 'Anambra State',
                'addressCountry' => 'NG',
            ],
            'url' => url('/'),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode(array_filter($schema), JSON_UNESCAPED_SLASHES) !!}</script>
    {{ $head ?? '' }}
    @stack('head')

    {{-- Instant navigation: Chromium browsers prerender a page as soon as a visitor hovers (desktop) or touches
         (phone) a link, so the click just reveals a page that is already built. Other browsers use the
         prefetch fallback in app.js. Pages with side effects or private areas are excluded. --}}
    <script type="speculationrules">
    {
        "prerender": [{
            "where": { "and": [
                { "href_matches": "/*" },
                { "not": { "href_matches": ["/ref/*", "/login", "/register", "/logout", "/dashboard", "/profile", "/forgot-password", "/reset-password/*"] } },
                { "not": { "selector_matches": "[target=_blank], [download], [rel~=nofollow]" } }
            ] },
            "eagerness": "moderate"
        }]
    }
    </script>
</head>
<body class="site-body min-h-screen overflow-x-clip font-sans">

    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-brand focus:px-5 focus:py-3 focus:text-brand-fg">Skip to content</a>

    {{-- Reading progress hairline --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-[2px]" aria-hidden="true">
        <div id="progress" class="h-full origin-left scale-x-0 bg-gradient-to-r from-sky-500 via-sky-300 to-white"></div>
    </div>

    @php
        // Social links from Website settings: only the ones filled in are shown.
        $socials = collect([
            ['url' => $settings->facebook_url, 'icon' => 'facebook', 'label' => 'Facebook', 'class' => 'soc-facebook'],
            ['url' => $settings->instagram_url, 'icon' => 'instagram', 'label' => 'Instagram', 'class' => 'soc-instagram'],
            ['url' => $settings->tiktok_url, 'icon' => 'tiktok', 'label' => 'TikTok', 'class' => 'soc-tiktok'],
        ])->filter(fn ($s) => filled($s['url']))->values();
    @endphp

    {{-- Floating glass header --}}
    <header id="site-header" class="site-header fixed inset-x-3 top-3 z-[58] mx-auto max-w-6xl rounded-full border backdrop-blur-xl sm:inset-x-6 sm:top-5">
        <div class="flex items-center justify-between gap-2 py-2 pl-4 pr-1.5 sm:gap-3 sm:pl-7 sm:pr-2">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ $settings->site_name }}, home">
                @if ($settings->logoUrl() || $settings->logoDarkUrl())
                    <img src="{{ $settings->logoDarkUrl() }}" alt="{{ $settings->site_name }}" class="logo-dark h-10 w-auto sm:h-12" width="160" height="44">
                    <img src="{{ $settings->logoUrl() ?? $settings->logoDarkUrl() }}" alt="" aria-hidden="true" class="logo-light h-10 w-auto sm:h-12" width="160" height="44">
                @else
                    <x-brand-mark class="h-8 w-8" />
                    <span class="font-serif text-[1.2rem] sm:text-[1.4rem] leading-none tracking-tight">Oku Lands</span>
                @endif
            </a>

            <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Primary">
                {{-- The logo is the Home link (current convention); Home stays in the mobile menu and footer. --}}
                @foreach (array_slice($nav, 1) as [$label, $href, $pattern])
                    <a href="{{ $href }}" class="nav-link {{ request()->routeIs($pattern) ? 'is-active' : '' }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-1.5 sm:gap-2">
                <x-theme-toggle />
                {{-- Always visible, beside the theme toggle. "Realtor" is dropped only where space is tight (phones, and the narrow desktop band). --}}
                <a href="{{ route('login') }}" class="pill-link !px-3.5 sm:!px-4" aria-label="Realtor Login">
                    <x-icon name="user" class="h-4 w-4" />
                    <span><span class="hidden sm:inline lg:hidden xl:inline">Realtor </span>Login</span>
                </a>
                <a href="{{ route('contact', ['interest' => 'inspection']) }}" data-magnetic class="btn btn-primary hidden !px-6 !py-3 text-[0.82rem] xl:inline-flex">Book Inspection</a>
                <button type="button" data-menu-toggle aria-expanded="false" aria-controls="menu" aria-label="Open menu" class="icon-btn relative lg:hidden">
                    <span class="bar-1 absolute block h-px w-4 -translate-y-[4px] bg-current"></span>
                    <span class="bar-2 absolute block h-px w-4 translate-y-[4px] bg-current"></span>
                </button>
            </div>
        </div>
    </header>

    {{-- Full-screen mobile menu --}}
    <div id="menu" class="menu-panel fixed inset-0 z-[55] flex flex-col overflow-y-auto bg-page px-6 pb-8 pt-28 text-ink lg:hidden">
        <nav class="flex flex-col" aria-label="Mobile">
            @foreach ($nav as $i => [$label, $href, $pattern])
                <a href="{{ $href }}" style="--i: {{ $i }}" class="menu-link flex items-baseline justify-between border-b border-ink/10 py-4 font-serif text-[2.6rem] leading-none tracking-tight {{ request()->routeIs($pattern) ? 'text-brand' : '' }}">
                    {{ $label }}
                    <span class="font-sans text-xs font-semibold tracking-widest text-mute">0{{ $i + 1 }}</span>
                </a>
            @endforeach
        </nav>
        @if ($socials->isNotEmpty())
            <div class="menu-link mt-8 flex items-center justify-center gap-3" style="--i: 6">
                @foreach ($socials as $i => $so)
                    <a href="{{ $so['url'] }}" target="_blank" rel="noopener" aria-label="{{ $so['label'] }}" class="soc-btn soc-lg {{ $so['class'] }}" style="--i: {{ $i }}"><x-icon :name="$so['icon']" class="h-5 w-5" /><span>{{ $so['label'] }}</span></a>
                @endforeach
            </div>
        @endif
        <div class="menu-link mt-10 flex flex-col gap-3" style="--i: 7">
            <a href="{{ route('contact', ['interest' => 'inspection']) }}" class="btn btn-primary">Book Inspection</a>
            <a href="{{ route('login') }}" class="btn btn-outline"><x-icon name="user" class="h-4 w-4" /> Realtor Login</a>
        </div>
        @if ($settings->phone)
            <p class="menu-link mt-8 text-sm text-mute" style="--i: 8">Call us: <a href="tel:{{ preg_replace('/\s+/', '', $settings->phone) }}" class="font-semibold text-ink">{{ $settings->phone }}</a></p>
        @endif
    </div>

    {{-- Social icons: one fixed column on the edge, visible on every screen and while scrolling. --}}
    @if ($socials->isNotEmpty())
        <div class="soc-rail fixed right-2 top-[40%] z-[57] flex -translate-y-1/2 flex-col items-center gap-1.5 rounded-full border border-white/20 bg-card/25 px-1 py-2.5 shadow-soft backdrop-blur-sm sm:gap-2 sm:border-ink/10 sm:bg-card/85 sm:px-1.5 sm:py-3 sm:backdrop-blur-xl sm:right-3" aria-label="Follow Oku Lands">
            <span class="mb-0.5 rounded-full bg-navy-950 px-1.5 py-2 text-[0.55rem] font-bold uppercase tracking-[0.2em] text-white shadow-soft [writing-mode:vertical-rl] rotate-180">Follow us</span>
            @foreach ($socials as $i => $so)
                <a href="{{ $so['url'] }}" target="_blank" rel="noopener" aria-label="{{ $so['label'] }}" title="{{ $so['label'] }}" class="soc-btn soc-sm {{ $so['class'] }}" style="--i: {{ $i }}"><x-icon :name="$so['icon']" class="h-4 w-4" /></a>
            @endforeach
        </div>
    @endif

    <main id="main">
        {{ $slot }}
    </main>

    {{-- WhatsApp shortcut --}}
    @if ($settings->whatsapp && ! $hideFab)
        <a href="{{ $settings->whatsappUrl('Hello Oku Lands, I would like to make an enquiry.') }}" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"
           class="fab fixed bottom-4 right-4 z-[60] flex h-[3.4rem] w-[3.4rem] items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg shadow-black/20 transition duration-300 hover:scale-105 sm:bottom-6 sm:right-6">
            <x-icon name="whatsapp" class="h-7 w-7" />
        </a>
    @endif

    {{-- Footer (always navy, in both themes) --}}
    <footer class="relative overflow-hidden bg-navy-950 text-white">
        <div class="relative z-10 mx-auto max-w-7xl px-5 pb-8 pt-14 sm:px-8 md:pt-16">
            <div class="grid grid-cols-1 gap-10 border-b border-white/10 pb-10 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <p class="eyebrow text-sky-300">{{ \App\Support\StaticText::tagline() }}</p>
                    <h2 class="display mt-6 text-5xl sm:text-6xl">{{ ch('footer.title', 'italic text-white/50') }}</h2>
                    <div class="mt-9 flex flex-wrap gap-3">
                        <a href="{{ route('contact', ['interest' => 'inspection']) }}" class="btn btn-sky">Book a free inspection</a>
                        <a href="{{ route('login') }}" class="btn btn-ghost">Realtor Login</a>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-10 sm:grid-cols-[0.8fr_0.8fr_1.5fr] lg:col-span-7 lg:pl-10">
                    <div>
                        <h4 class="mb-5 text-[0.7rem] font-semibold uppercase tracking-[0.25em] text-white/40">Explore</h4>
                        <ul class="space-y-3 text-sm text-white/70">
                            @foreach ($nav as [$label, $href])
                                <li><a href="{{ $href }}" class="transition hover:text-sky-300">{{ $label }}</a></li>
                            @endforeach
                            <li><a href="{{ route('faq') }}" class="transition hover:text-sky-300">FAQs &amp; Help</a></li>
                            <li><a href="{{ route('login') }}" class="transition hover:text-sky-300">Realtor Login</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-5 text-[0.7rem] font-semibold uppercase tracking-[0.25em] text-white/40">Services</h4>
                        <ul class="space-y-3 text-sm text-white/70">
                            @foreach (\App\Models\Service::visible()->pluck('title') as $footerService)
                                <li><a href="{{ route('services') }}" class="transition hover:text-sky-300">{{ $footerService }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <h4 class="mb-5 text-[0.7rem] font-semibold uppercase tracking-[0.25em] text-white/40">Reach us</h4>
                        <ul class="space-y-3 text-sm text-white/70">
                            @if ($settings->phone)
                                <li><a href="tel:{{ preg_replace('/\s+/', '', $settings->phone) }}" class="font-semibold text-white transition hover:text-sky-300">{{ $settings->phone }}</a></li>
                            @endif
                            @if ($settings->email)
                                <li><a href="mailto:{{ $settings->email }}" class="[overflow-wrap:anywhere] transition hover:text-sky-300">{{ $settings->email }}</a></li>
                            @endif
                            @if ($settings->address)
                                <li class="leading-relaxed text-white/50">{{ $settings->address }}</li>
                            @endif
                        </ul>
                        <div class="mt-6 flex gap-2">
                            @foreach ($socials as $i => $so)
                                <a href="{{ $so['url'] }}" target="_blank" rel="noopener" aria-label="{{ $so['label'] }}" title="{{ $so['label'] }}" class="soc-btn {{ $so['class'] }}" style="--i: {{ $i }}"><x-icon :name="$so['icon']" class="h-4 w-4" /></a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-start justify-between gap-3 pt-8 text-xs text-white/40 sm:flex-row sm:items-center">
                <p>&copy; {{ now()->year }} {{ $settings->site_name }}.@if ($settings->rc_number) {{ $settings->rc_number }}.@endif All rights reserved.</p>
                <p class="flex flex-wrap items-center gap-x-5 gap-y-1">
                    <a href="{{ route('legal', 'privacy-policy') }}" class="transition hover:text-white">Privacy Policy</a>
                    <a href="{{ route('legal', 'terms') }}" class="transition hover:text-white">Terms of Use</a>
                </p>
                <p>Crafted &amp; Developed by <a href="https://www.viplinetech.com" target="_blank" rel="noopener" class="font-semibold text-sky-300 transition hover:text-white">Vipline Technologies Limited</a></p>
            </div>
        </div>

    </footer>

</body>
</html>
