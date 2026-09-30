<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
<x-layouts.public :title="'Home'">

    <x-hero :images="$heroImages" />

    {{-- About --}}
    <section class="bg-white py-24 dark:bg-navy-950">
        <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-16 px-5 sm:px-8 lg:grid-cols-2">
            <div data-reveal="left" class="pt-6">
                <x-about-image :image="$heroImages[1] ?? null" stat-value="{{ $featuredProperties->count() ?: '500' }}+" stat-label="Available Listings" />
            </div>

            <div data-reveal="right">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">About Us</span>
                <h2 class="mt-3 font-serif text-3xl font-extrabold leading-tight text-navy-900 dark:text-white sm:text-4xl">
                    Oku Lands &amp; Properties Is Redefining Real Estate, Construction &amp; Agriculture in Nigeria
                </h2>
                <p class="mt-5 text-sm leading-relaxed text-navy-600 dark:text-white/60 sm:text-base">
                    Oku Lands &amp; Properties Limited is a forward-thinking, multi-sector company committed to
                    delivering secure, verified and high-value real estate solutions, backed by our own
                    construction and agriculture divisions.
                </p>

                <div class="mt-8 grid grid-cols-2 gap-x-6 gap-y-4">
                    @foreach ($aboutChecklist as $index => $item)
                        <div data-reveal data-reveal-delay="{{ $index * 80 }}" class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-500/20 dark:text-gold-400">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7 7a1 1 0 0 1-1.4 0l-3-3a1 1 0 1 1 1.4-1.4L9 11.6l6.3-6.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            </span>
                            <span class="text-sm font-semibold text-navy-800 dark:text-white/80">{{ $item }}</span>
                        </div>
                    @endforeach
                </div>

                <a href="{{ url('/about') }}" class="mt-9 inline-flex items-center gap-2 rounded-full bg-navy-900 px-7 py-3.5 text-sm font-bold text-white shadow-premium transition hover:-translate-y-0.5 hover:shadow-premium-lg dark:bg-gold-500 dark:text-navy-950">
                    Learn More
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Services: 2x3 grid, RMS highlighted --}}
    <section class="bg-navy-50 py-24 dark:bg-navy-900">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="max-w-2xl">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">What We Do</span>
                <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                    We make real estate investment easy, accessible, and profitable.
                </h2>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $index => $service)
                    <div
                        data-reveal="scale"
                        data-reveal-delay="{{ $index * 90 }}"
                        class="group rounded-2xl border p-7 transition duration-500 hover:-translate-y-2 {{ $service['highlight']
                            ? 'border-gold-400 bg-white shadow-premium-lg ring-1 ring-gold-400/60 dark:bg-navy-950'
                            : 'border-navy-100 bg-white shadow-premium hover:shadow-premium-lg dark:border-white/10 dark:bg-navy-950' }}"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl transition duration-500 group-hover:scale-110 {{ $service['highlight'] ? 'bg-gold-500 text-navy-950' : 'bg-navy-900 text-gold-400 dark:bg-white/10 dark:text-gold-400' }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="{{ $service['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h3 class="mt-5 font-serif text-lg font-bold text-navy-900 dark:text-white">{{ $service['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-navy-600 dark:text-white/60">{{ $service['desc'] }}</p>
                        <a href="{{ url('/about') }}" class="mt-4 inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-gold-600 dark:text-gold-400">
                            Get Started
                            <svg class="h-3.5 w-3.5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Latest Properties</span>
                    <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                        Explore Secure, High-Value Properties
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

    {{-- Get In Touch --}}
    <section class="bg-white py-24 dark:bg-navy-950">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="max-w-2xl">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Get In Touch</span>
                <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                    Looking for the Perfect Property or Need a Quote?
                </h2>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-10 lg:grid-cols-5">
                <div data-reveal="left" class="lg:col-span-2">
                    <div class="rounded-3xl border border-navy-100 bg-navy-50 p-8 dark:border-white/10 dark:bg-navy-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-navy-900 text-gold-400 dark:bg-gold-500 dark:text-navy-950">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.1-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <p class="mt-5 text-sm leading-relaxed text-navy-600 dark:text-white/60">
                            At Oku Lands, we're here to meet all your real estate needs. Need help? Fill out
                            the form and our team will reach out shortly.
                        </p>
                        @if ($settings->whatsapp ?? false)
                            <a href="https://wa.me/{{ $settings->whatsapp }}" target="_blank" rel="noopener" class="mt-6 inline-flex items-center gap-2 text-lg font-extrabold text-navy-900 dark:text-white">
                                +{{ $settings->whatsapp }}
                            </a>
                        @endif
                    </div>
                </div>

                <div data-reveal="right" class="lg:col-span-3">
                    @if (session('status'))
                        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form action="{{ route('contact.store') }}" method="POST" class="grid grid-cols-1 gap-5 rounded-3xl border border-navy-100 bg-white p-8 shadow-premium dark:border-white/10 dark:bg-navy-900 sm:grid-cols-2">
                        @csrf
                        <input type="hidden" name="type" value="general">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-navy-500 dark:text-white/50">Your Name</label>
                            <input type="text" name="name" required class="w-full rounded-xl border-navy-200 bg-white text-sm focus:border-gold-400 focus:ring-gold-400 dark:border-white/10 dark:bg-navy-950 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-navy-500 dark:text-white/50">Your Phone</label>
                            <input type="text" name="phone" class="w-full rounded-xl border-navy-200 bg-white text-sm focus:border-gold-400 focus:ring-gold-400 dark:border-white/10 dark:bg-navy-950 dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-navy-500 dark:text-white/50">Your Email</label>
                            <input type="email" name="email" class="w-full rounded-xl border-navy-200 bg-white text-sm focus:border-gold-400 focus:ring-gold-400 dark:border-white/10 dark:bg-navy-950 dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-navy-500 dark:text-white/50">Your Message</label>
                            <textarea name="message" rows="4" class="w-full rounded-xl border-navy-200 bg-white text-sm focus:border-gold-400 focus:ring-gold-400 dark:border-white/10 dark:bg-navy-950 dark:text-white"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-gold-500 to-gold-600 py-4 text-sm font-bold text-navy-950 shadow-premium transition hover:-translate-y-0.5 hover:shadow-premium-lg">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    @if ($testimonials->isNotEmpty())
        <section class="bg-navy-50 py-24 dark:bg-navy-900">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div data-reveal class="mx-auto max-w-2xl text-center">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Client Stories</span>
                    <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                        Your Satisfaction Is Our Top Priority
                    </h2>
                </div>
                <div class="mt-14">
                    <x-testimonial-carousel :testimonials="$testimonials" />
                </div>
            </div>
        </section>
    @endif

    {{-- Stats band: dark with blueprint pattern --}}
    <section class="relative overflow-hidden bg-navy-950 py-20">
        <div class="absolute inset-0 opacity-[0.06] [background-image:linear-gradient(rgba(255,255,255,.4)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.4)_1px,transparent_1px)] [background-size:48px_48px]"></div>
        <div class="relative mx-auto grid max-w-6xl grid-cols-2 gap-8 px-5 sm:px-8 md:grid-cols-4">
            @foreach ([
                ['icon' => 'M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m5-5.13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-2a4 4 0 1 1 0-8', 'n' => 1200, 'suffix' => '+', 'label' => 'Happy Clients'],
                ['icon' => 'M3 21h18M6 21V9l6-4 6 4v12M10 21v-6h4v6', 'n' => 500, 'suffix' => '+', 'label' => 'Properties Sold'],
                ['icon' => 'M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m5-5.13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-2a4 4 0 1 1 0-8', 'n' => 150, 'suffix' => '+', 'label' => 'Active Realtors'],
                ['icon' => 'M12 8c-3 0-4 2-4 4s1 4 4 4 4-2 4-4M12 4v2m0 12v2', 'n' => 3, 'suffix' => '', 'label' => 'Sectors, One Company'],
            ] as $index => $stat)
                <div data-reveal data-reveal-delay="{{ $index * 100 }}" class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full border border-gold-400/30 text-gold-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="{{ $stat['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div class="font-serif text-3xl font-extrabold text-white sm:text-4xl">
                        <span data-counter="{{ $stat['n'] }}" data-counter-suffix="{{ $stat['suffix'] }}">0</span>
                    </div>
                    <div class="mt-2 text-xs font-semibold uppercase tracking-[0.15em] text-white/50">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Latest News --}}
    @if ($latestNews->isNotEmpty())
        <section class="bg-white py-24 dark:bg-navy-950">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div data-reveal class="mx-auto max-w-2xl text-center">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Latest Blog &amp; News</span>
                    <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                        Insights to Make Your Real Estate Investment Easier
                    </h2>
                </div>
                <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-3">
                    @foreach ($latestNews as $index => $post)
                        <a href="{{ url('/news/'.$post->slug) }}" data-reveal data-reveal-delay="{{ $index * 100 }}" class="group overflow-hidden rounded-3xl border border-navy-100 bg-white shadow-premium transition duration-500 hover:-translate-y-2 hover:shadow-premium-lg dark:border-white/10 dark:bg-navy-900">
                            <div class="relative aspect-[16/10] overflow-hidden bg-navy-100 dark:bg-navy-800">
                                @if ($post->cover_image)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($post->cover_image) }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-110">
                                @else
                                    <div class="flex h-full w-full items-center justify-center"><x-brand-mark class="h-12 w-12 opacity-40" /></div>
                                @endif
                                <span class="absolute left-4 top-4 rounded-lg bg-navy-900/90 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                                    {{ $post->published_at->format('d M') }}
                                </span>
                            </div>
                            <div class="p-6">
                                <h3 class="font-serif text-base font-bold leading-snug text-navy-900 transition group-hover:text-gold-600 dark:text-white dark:group-hover:text-gold-400">{{ $post->title }}</h3>
                                <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-gold-600 dark:text-gold-400">
                                    Read More
                                    <svg class="h-3.5 w-3.5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
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
