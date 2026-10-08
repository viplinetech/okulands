{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Split-screen shell for the sign-in screens. Two looks:
      • Realtor: photo panel + programme benefits (login, register, password screens)
      • Admin (/adminbackend, and the two-factor step for an admin): plain, private, no public-site links
--}}
@php
    $screens = [
        'login' => ['Realtor Login', 'Welcome back. Sign in to your dashboard to track referrals, leads and commissions.'],
        'register' => ['Become a realtor', 'Create your free account and get your unique referral link instantly.'],
        'password.request' => ['Forgot password?', 'Enter your email and we will send you a link to reset it.'],
        'password.reset' => ['Reset password', 'Choose a new password for your account.'],
        'verification.notice' => ['Verify your email', 'One quick step before you get started.'],
        'password.confirm' => ['Confirm password', 'Please confirm your password to continue.'],
        'two-factor.challenge' => ['Two-step verification', 'Enter the 6-digit code from your authenticator app to finish signing in.'],
        'admin.login' => ['Admin sign-in', 'Enter your administrator credentials to continue.'],
    ];
    [$screenTitle, $screenText] = $screens[request()->route()?->getName()] ?? ['Welcome', 'Sign in to continue.'];
    $isAdminView = request()->routeIs('admin.login') || (request()->routeIs('two-factor.challenge') && auth()->user()?->isAdmin());
    $isLogin = request()->routeIs('login');
    $isRegister = request()->routeIs('register');
    $hero = $settings->heroImageUrls()[0] ?? null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F5F8FC">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $isAdminView ? $screenTitle.' | Admin' : $screenTitle.' | '.$settings->site_name }}</title>
    @if ($settings->faviconUrl())
        <link rel="icon" href="{{ $settings->faviconUrl() }}">
    @endif

    <x-theme-boot-script :default="$settings->default_theme ?? 'light'" />
    <script>document.documentElement.classList.add('js');</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-body min-h-screen font-sans">
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">

        {{-- Left panel (always dark) --}}
        <aside class="grain relative hidden overflow-hidden bg-navy-950 text-white lg:block">
            @if (! $isAdminView && $hero)
                {{-- object-position favours the lower two-thirds of the photo: this panel is tall and
                     narrow, and a plain centred crop pushes a photo's plain sky strip to the top,
                     under the gradient, looking like dead space instead of a photo. --}}
                <img src="{{ $hero }}" alt="" class="absolute inset-0 h-full w-full object-cover [object-position:50%_70%]" decoding="async">
            @endif
            <div class="absolute inset-0 bg-gradient-to-b {{ $isAdminView ? 'from-navy-900 via-navy-950 to-navy-950' : 'from-navy-950/70 via-navy-950/55 to-navy-950/95' }}"></div>
            <div class="aurora absolute -left-24 top-1/3 h-[28rem] w-[28rem] rounded-full bg-sky-500/25 blur-[120px]"></div>

            <div class="relative flex h-full flex-col justify-between p-12 xl:p-16">
                @if ($isAdminView)
                    <span class="flex items-center gap-3">
                        @if ($settings->logoDarkUrl())
                            <img src="{{ $settings->logoDarkUrl() }}" alt="{{ $settings->site_name }}" class="h-10 w-auto">
                        @else
                            <x-brand-mark class="h-10 w-10" />
                        @endif
                        <span class="text-xs font-bold uppercase tracking-[0.24em] text-sky-300">Admin console</span>
                    </span>

                    <div>
                        <span class="flex h-16 w-16 items-center justify-center rounded-3xl border border-white/15 bg-white/[0.06] text-sky-300"><x-icon name="shield" class="h-8 w-8" /></span>
                        <h2 class="display mt-8 text-6xl xl:text-7xl">Restricted<br><span class="italic text-shine-inv">access.</span></h2>
                        <ul class="mt-9 space-y-3 text-white/75">
                            @foreach (['Two-factor protected sign-in', 'Every action is recorded in the audit log', 'Automatic sign-out when idle'] as $point)
                                <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-400/20 text-sky-300"><x-icon name="check" class="h-3.5 w-3.5" /></span>{{ $point }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <p class="text-xs text-white/40">Authorised staff only</p>
                @else
                    <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ $settings->site_name }}">
                        @if ($settings->logoDarkUrl())
                            <img src="{{ $settings->logoDarkUrl() }}" alt="{{ $settings->site_name }}" class="h-10 w-auto">
                        @else
                            <x-brand-mark class="h-10 w-10" />
                            <span class="font-serif text-2xl">Oku Lands</span>
                        @endif
                    </a>

                    <div>
                        <p class="eyebrow text-sky-300">Realtor Management System</p>
                        <h2 class="display mt-6 text-6xl xl:text-7xl">Earn with<br><span class="italic text-shine-inv">every referral.</span></h2>
                        <ul class="mt-9 space-y-3 text-white/75">
                            @foreach (['Your own unique referral link', 'Real-time dashboard for leads and sales', 'Commission on referrals and your downline', 'Transparent, on-time payouts'] as $point)
                                <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-400/20 text-sky-300"><x-icon name="check" class="h-3.5 w-3.5" /></span>{{ $point }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <p class="text-xs text-white/40">&copy; {{ now()->year }} {{ $settings->site_name }}</p>
                @endif
            </div>
        </aside>

        {{-- Form panel --}}
        <main class="relative flex min-h-screen flex-col">
            <div class="flex items-center justify-between px-5 py-5 sm:px-10">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-mute transition hover:text-ink">
                    <x-icon name="arrow" class="h-4 w-4 rotate-180" /> Back to website
                </a>
                <x-theme-toggle class="text-ink" />
            </div>

            <div class="flex flex-1 items-center justify-center px-5 pb-12 sm:px-10">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex items-center gap-2.5 text-ink lg:hidden">
                        @if ($isAdminView)
                            <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="Back to the Oku Lands website">
                                @if ($settings->logoUrl())
                                    <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->site_name }}" class="h-9 w-auto dark:hidden">
                                    <img src="{{ $settings->logoDarkUrl() }}" alt="" aria-hidden="true" class="hidden h-9 w-auto dark:block">
                                @else
                                    <x-brand-mark class="h-9 w-9" />
                                @endif
                                <span class="text-xs font-bold uppercase tracking-[0.22em] text-brand">Admin console</span>
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="{{ $settings->site_name }}">
                                @if ($settings->logoUrl())
                                    <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->site_name }}" class="h-9 w-auto dark:hidden">
                                    <img src="{{ $settings->logoDarkUrl() }}" alt="" aria-hidden="true" class="hidden h-9 w-auto dark:block">
                                @else
                                    <x-brand-mark class="h-9 w-9" />
                                    <span class="font-serif text-2xl">Oku Lands</span>
                                @endif
                            </a>
                        @endif
                    </div>

                    <p class="eyebrow text-brand">{{ $isAdminView ? 'Admin console' : 'Realtor area' }}</p>
                    <h1 class="display mt-4 text-5xl text-ink sm:text-6xl">{{ $screenTitle }}</h1>
                    <p class="mt-4 leading-relaxed text-mute">{{ $screenText }}</p>

                    <div class="mt-9">
                        {{ $slot }}
                    </div>

                    @if ($isLogin && Route::has('register'))
                        <p class="mt-8 text-center text-sm text-mute">New to Oku Lands? <a href="{{ route('register') }}" class="font-semibold text-brand hover:underline">Become a realtor</a></p>
                    @elseif ($isRegister)
                        <p class="mt-8 text-center text-sm text-mute">Already a realtor? <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">Log in</a></p>
                    @endif
                </div>
            </div>
        </main>
    </div>
</body>
</html>
