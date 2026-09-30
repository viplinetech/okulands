<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
<x-layouts.public :title="'Home'">

    <x-hero :images="$heroImages" />

    {{-- Stats band --}}
    <section class="border-y border-navy-100 bg-navy-50 py-14 dark:border-white/10 dark:bg-navy-900">
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-8 px-5 sm:px-8 md:grid-cols-4">
            @foreach ([
                ['n' => 500, 'suffix' => '+', 'label' => 'Properties Sold'],
                ['n' => 1200, 'suffix' => '+', 'label' => 'Happy Clients'],
                ['n' => 150, 'suffix' => '+', 'label' => 'Active Realtors'],
                ['n' => 3, 'suffix' => '', 'label' => 'Sectors, One Company'],
            ] as $index => $stat)
                <div data-reveal data-reveal-delay="{{ $index * 100 }}" class="text-center">
                    <div class="font-serif text-3xl font-extrabold text-gold-600 dark:text-gold-400 sm:text-4xl">
                        <span data-counter="{{ $stat['n'] }}" data-counter-suffix="{{ $stat['suffix'] }}">0</span>
                    </div>
                    <div class="mt-2 text-xs font-semibold uppercase tracking-[0.15em] text-navy-500 dark:text-white/50">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- What Sets Us Apart --}}
    <section class="bg-white py-24 dark:bg-navy-950">
        <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-14 px-5 sm:px-8 lg:grid-cols-2">
            <div data-reveal="left" class="relative">
                <div class="aspect-[4/5] overflow-hidden rounded-[2rem] border border-navy-100 bg-navy-100 shadow-premium dark:border-white/10 dark:bg-navy-800">
                    <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(212,175,55,.2),transparent_50%),radial-gradient(circle_at_80%_80%,rgba(42,69,147,.25),transparent_50%),linear-gradient(160deg,#eef1f8_0%,#d6ddef_100%)] dark:bg-[linear-gradient(160deg,#101c44_0%,#15265a_100%)]">
                        <x-brand-mark class="h-24 w-24 opacity-70" />
                    </div>
                </div>
            </div>

            <div>
                <span data-reveal class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">What Sets Us Apart</span>
                <h2 data-reveal class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                    Why Clients Choose Oku Lands
                </h2>

                <div class="mt-10 space-y-8">
                    @foreach ($differentiators as $index => $item)
                        <div data-reveal="left" data-reveal-delay="{{ $index * 120 }}" class="flex gap-5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-navy-900 font-serif text-base font-bold text-gold-400 dark:bg-gold-500 dark:text-navy-950">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <h3 class="font-bold text-navy-900 dark:text-white">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-navy-600 dark:text-white/60">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section class="bg-navy-50 py-24 dark:bg-navy-900">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="mx-auto max-w-2xl text-center">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Our Services</span>
                <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                    Everything You Need, Under One Roof
                </h2>
            </div>

            <div class="mt-16 grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach ($services as $index => $service)
                    <div data-reveal="scale" data-reveal-delay="{{ $index * 120 }}" class="group rounded-3xl border border-navy-100 bg-white p-8 text-center shadow-premium transition duration-500 hover:-translate-y-2 hover:shadow-premium-lg dark:border-white/10 dark:bg-navy-950">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-navy-800 to-navy-950 text-gold-400 transition duration-500 group-hover:scale-110 group-hover:rotate-3 dark:from-gold-500 dark:to-gold-600 dark:text-navy-950">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="{{ $service['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h3 class="mt-6 font-serif text-xl font-bold text-navy-900 dark:text-white">{{ $service['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-navy-600 dark:text-white/60">{{ $service['desc'] }}</p>
                        <a href="{{ url('/about') }}" class="mt-5 inline-flex items-center gap-1 text-sm font-bold text-gold-600 dark:text-gold-400">
                            Learn More
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Property Listing carousel --}}
    <section class="bg-white py-24 dark:bg-navy-950">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Featured Listings</span>
                    <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                        Property Listings
                    </h2>
                </div>
                <a href="{{ url('/properties') }}" class="group inline-flex items-center gap-2 rounded-full border border-navy-200 px-6 py-3 text-sm font-semibold text-navy-800 transition hover:border-gold-400 hover:text-gold-600 dark:border-white/20 dark:text-white">
                    Explore All
                    <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>

            <div class="mt-14">
                @if ($featuredProperties->isNotEmpty())
                    <x-property-carousel :properties="$featuredProperties" />
                @else
                    <div data-reveal class="rounded-3xl border border-dashed border-navy-200 bg-navy-50 p-14 text-center text-navy-400 dark:border-white/10 dark:bg-navy-900 dark:text-white/40">
                        Featured properties will appear here once listings are added from the admin dashboard.
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Guiding Principles: alternating rows --}}
    <section class="bg-navy-50 py-24 dark:bg-navy-900">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="mx-auto max-w-2xl text-center">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Guiding Principles</span>
                <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                    How We Work With You
                </h2>
            </div>

            <div class="mt-16 space-y-16">
                @foreach ($principles as $index => $principle)
                    <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2">
                        <div data-reveal="{{ $index % 2 === 0 ? 'left' : 'right' }}" class="{{ $index % 2 === 1 ? 'lg:order-2' : '' }}">
                            <div class="aspect-[16/10] overflow-hidden rounded-[2rem] border border-navy-100 bg-white shadow-premium dark:border-white/10 dark:bg-navy-950">
                                <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(212,175,55,.18),transparent_50%),radial-gradient(circle_at_80%_80%,rgba(42,69,147,.2),transparent_50%)]">
                                    <x-brand-mark class="h-16 w-16 opacity-50" />
                                </div>
                            </div>
                        </div>
                        <div data-reveal="{{ $index % 2 === 0 ? 'right' : 'left' }}" class="{{ $index % 2 === 1 ? 'lg:order-1' : '' }}">
                            <h3 class="font-serif text-2xl font-bold text-navy-900 dark:text-white">{{ $principle['title'] }}</h3>
                            <p class="mt-4 text-sm leading-relaxed text-navy-600 dark:text-white/60">{{ $principle['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- RMS Banner --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-navy-900 to-navy-950 py-20">
        <div class="absolute -left-20 top-1/2 h-72 w-72 -translate-y-1/2 rounded-full bg-gold-500/15 blur-[100px]"></div>
        <div class="absolute -right-20 bottom-0 h-72 w-72 rounded-full bg-blue-500/15 blur-[100px]"></div>

        <div class="relative mx-auto grid max-w-6xl grid-cols-1 items-center gap-12 px-5 sm:px-8 lg:grid-cols-2">
            <div data-reveal="left">
                <span class="inline-flex items-center rounded-full border border-gold-400/40 bg-white/5 px-5 py-2 text-xs font-bold uppercase tracking-[0.2em] text-gold-400">
                    RMS &middot; Realtor Management System
                </span>
                <h2 class="mt-6 font-serif text-3xl font-extrabold text-white sm:text-4xl">
                    Earn With the Oku Lands RMS
                </h2>
                <p class="mt-4 max-w-lg text-white/70">
                    Become an Oku Lands realtor and earn commission on every sale you refer, plus a
                    percentage from anyone you bring into the network. Simple to join, transparent to track.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ url('/about') }}" class="rounded-full border border-white/30 px-7 py-3.5 text-sm font-bold text-white transition hover:border-white/60 hover:bg-white/10">
                        Get More Info
                    </a>
                    <a href="{{ route('register') }}" class="rounded-full bg-gradient-to-r from-gold-400 to-gold-600 px-7 py-3.5 text-sm font-bold text-navy-950 shadow-premium-lg transition hover:-translate-y-0.5">
                        Register Now
                    </a>
                </div>
            </div>

            <div data-reveal="right" class="grid grid-cols-2 gap-4">
                @foreach ([
                    ['icon' => 'M12 8c-3 0-4 2-4 4s1 4 4 4 4-2 4-4M12 4v2m0 12v2', 'title' => 'Earn Per Sale', 'desc' => 'Commission on every direct referral.'],
                    ['icon' => 'M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m5-5.13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-2a4 4 0 1 1 0-8', 'title' => 'Grow a Downline', 'desc' => 'Earn from realtors you recruit too.'],
                    ['icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'title' => 'Transparent Tracking', 'desc' => 'See every click, lead and sale live.'],
                    ['icon' => 'M12 4v16m0 0-4-4m4 4 4-4', 'title' => 'Instant Referral Link', 'desc' => 'Get your unique link the moment you sign up.'],
                ] as $index => $feature)
                    <div data-reveal="scale" data-reveal-delay="{{ $index * 100 }}" class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                        <svg class="h-6 w-6 text-gold-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="{{ $feature['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <p class="mt-3 text-sm font-bold text-white">{{ $feature['title'] }}</p>
                        <p class="mt-1 text-xs text-white/60">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    @if ($testimonials->isNotEmpty())
        <section class="bg-white py-24 dark:bg-navy-950">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div data-reveal class="mx-auto max-w-2xl text-center">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Client Stories</span>
                    <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                        What Our Clients &amp; Realtors Are Saying
                    </h2>
                </div>
                <div class="mt-14">
                    <x-testimonial-carousel :testimonials="$testimonials" />
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    <section class="bg-navy-50 py-24 dark:bg-navy-900">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="mx-auto max-w-2xl text-center">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">FAQ</span>
                <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                    Frequently Asked Questions
                </h2>
            </div>
            <div data-reveal data-reveal-delay="150" class="mt-14">
                <x-faq-accordion :items="$faqs" />
            </div>
        </div>
    </section>

    {{-- CTA Strip --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-navy-900 to-navy-950 py-20">
        <div data-reveal="scale" class="relative mx-auto max-w-4xl px-5 text-center sm:px-8">
            <h2 class="font-serif text-3xl font-extrabold text-white sm:text-4xl">
                Ready to Secure Your Future?
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-white/70">
                Speak with our team today or become a realtor and start earning from every referral you make.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ url('/contact') }}" class="w-full rounded-full bg-gradient-to-r from-gold-400 to-gold-600 px-8 py-4 text-center text-sm font-bold text-navy-950 shadow-premium-lg transition hover:-translate-y-1 sm:w-auto">
                    Book a Free Inspection
                </a>
                <a href="{{ route('register') }}" class="w-full rounded-full border border-white/30 px-8 py-4 text-center text-sm font-bold text-white transition hover:border-white/60 hover:bg-white/10 sm:w-auto">
                    Become a Realtor
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
