<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
<x-layouts.public :title="'Home'" :transparent-hero="true">

    {{-- 2. Hero (includes trust-stat strip + floating search bar) --}}
    <x-hero :images="$heroImages" />

    {{-- Spacer to clear the floating search bar --}}
    <div class="h-16 bg-ivory dark:bg-navy-950 sm:h-14"></div>

    {{-- 3. About --}}
    <section class="bg-ivory py-16 dark:bg-navy-950 md:py-24">
        <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-16 px-5 sm:px-8 lg:grid-cols-2">
            <div data-reveal class="pb-6 pr-6">
                <x-about-image :image="$heroImages[1] ?? null" />
            </div>

            <div data-reveal data-reveal-delay="120">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">About Us</span>
                <h2 class="mt-3 font-serif text-3xl font-semibold leading-tight tracking-tight text-navy-900 dark:text-white sm:text-4xl">
                    Redefining Real Estate, Construction &amp; Agriculture <span class="text-gradient-brand">in Nigeria</span>
                </h2>
                <p class="mt-5 text-sm leading-relaxed text-slate-500 dark:text-white/60 sm:text-base">
                    Oku Lands &amp; Properties is a multi-sector company delivering secure, verified real
                    estate solutions, backed by our own construction and agriculture divisions.
                </p>

                <div class="mt-8 grid grid-cols-2 gap-x-6 gap-y-4">
                    @foreach ($aboutChecklist as $item)
                        <div class="flex items-center gap-3">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-400">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7 7a1 1 0 0 1-1.4 0l-3-3a1 1 0 1 1 1.4-1.4L9 11.6l6.3-6.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            </span>
                            <span class="text-sm font-medium text-navy-800 dark:text-white/80">{{ $item }}</span>
                        </div>
                    @endforeach
                </div>

                <a href="{{ url('/about') }}" class="mt-9 inline-flex items-center gap-2 text-sm font-semibold text-navy-900 dark:text-white">
                    Learn More
                    <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- 4. Three Sectors + Services (bento) --}}
    <section class="bg-white py-16 dark:bg-navy-900 md:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="max-w-2xl">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">What We Do</span>
                <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-navy-900 dark:text-white sm:text-4xl">
                    <span class="text-gradient-brand">Three Sectors</span>, One Trusted Company
                </h2>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($sectors as $index => $sector)
                    <a href="{{ url('/about') }}" data-reveal data-reveal-delay="{{ $index * 100 }}" class="group relative block aspect-[3/4] overflow-hidden rounded-lg">
                        <img src="{{ $sector['image'] }}" alt="{{ $sector['title'] }}" class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105" loading="lazy" width="450" height="600">
                        <div class="absolute inset-0 bg-gradient-to-t from-navy-950/90 via-navy-950/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6">
                            <h3 class="font-serif text-xl font-semibold text-white">{{ $sector['title'] }}</h3>
                            <p class="mt-1 text-sm text-white/70">{{ $sector['desc'] }}</p>
                            <span class="mt-4 inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-sky-300">
                                Explore
                                <svg class="h-3.5 w-3.5 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6 grid grid-cols-1 divide-y divide-navy-900/10 rounded-lg border border-navy-900/10 dark:divide-white/10 dark:border-white/10 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                @foreach ($smallFeatures as $feature)
                    <div data-reveal class="p-6">
                        <h4 class="font-serif text-base font-semibold text-navy-900 dark:text-white">{{ $feature['title'] }}</h4>
                        <p class="mt-1.5 text-sm text-slate-500 dark:text-white/50">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. Featured Properties (conditional, fully hidden if none exist) --}}
    @if ($featuredProperties->isNotEmpty())
        <section class="bg-ivory py-16 dark:bg-navy-950 md:py-24">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div data-reveal class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">Featured Listings</span>
                        <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-navy-900 dark:text-white sm:text-4xl">
                            Explore Secure, <span class="text-gradient-brand">High-Value</span> Properties
                        </h2>
                    </div>
                    <a href="{{ url('/properties') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-navy-900 dark:text-white">
                        View All Properties
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>

                <div class="mt-12">
                    <x-property-carousel :properties="$featuredProperties" />
                </div>
            </div>
        </section>
    @endif

    {{-- 6. Why Oku: numbered row --}}
    <section class="bg-white py-16 dark:bg-navy-900 md:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="max-w-2xl">
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">Why Oku Lands</span>
                <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-navy-900 dark:text-white sm:text-4xl">
                    Built on <span class="text-gradient-brand">Trust</span>, Backed by Process
                </h2>
            </div>

            <div class="mt-12 grid grid-cols-1 divide-y divide-navy-900/10 border-t border-navy-900/10 dark:divide-white/10 dark:border-white/10 sm:grid-cols-4 sm:divide-x sm:divide-y-0">
                @foreach ($whyOku as $index => $item)
                    <div data-reveal data-reveal-delay="{{ $index * 80 }}" class="px-0 py-6 sm:px-6">
                        <span class="font-serif text-3xl font-semibold text-sky-500/70">{{ $item['n'] }}</span>
                        <h3 class="mt-3 font-serif text-lg font-semibold text-navy-900 dark:text-white">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-white/50">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 7. Realtor Program (RMS): a contained dark accent panel, not a full-bleed dark section --}}
    <section class="bg-ivory py-16 dark:bg-navy-950 md:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="rounded-2xl bg-navy-950 px-6 py-14 sm:px-12 sm:py-16">
                <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-2">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-400">Realtor Management System</span>
                        <h2 class="mt-4 font-serif text-3xl font-semibold leading-tight tracking-tight text-white sm:text-4xl">
                            Earn With <span class="text-gradient-invert">Oku Lands</span>
                        </h2>
                        <p class="mt-5 max-w-md text-sm leading-relaxed text-white/60 sm:text-base">
                            Become an Oku Lands realtor and earn commission on every sale you refer, plus a share
                            from anyone you bring into the network.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ route('register') }}" class="rounded-md bg-sky-500 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-sky-400">
                                Become a Realtor
                            </a>
                            <a href="{{ route('login') }}" class="rounded-md border border-white/25 px-7 py-3.5 text-sm font-semibold text-white transition hover:border-white/50">
                                Realtor Login
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($rmsBenefits as $benefit)
                            <div class="rounded-lg border border-white/10 p-6">
                                <h3 class="font-serif text-base font-semibold text-white">{{ $benefit['title'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-white/50">{{ $benefit['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 8. Testimonials --}}
    @if ($testimonials->isNotEmpty())
        <section class="bg-ivory py-16 dark:bg-navy-950 md:py-24">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div data-reveal class="mx-auto max-w-2xl text-center">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">Client Stories</span>
                    <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-navy-900 dark:text-white sm:text-4xl">
                        Your Satisfaction Is Our Priority
                    </h2>
                </div>
                <div class="mt-12">
                    <x-testimonial-carousel :testimonials="$testimonials" />
                </div>
            </div>
        </section>
    @endif

    {{-- 9. FAQ + Contact (combined) --}}
    <section class="bg-white py-16 dark:bg-navy-900 md:py-24">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="grid grid-cols-1 gap-16 lg:grid-cols-2">
                <div data-reveal>
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">FAQ</span>
                    <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-navy-900 dark:text-white">
                        Frequently Asked Questions
                    </h2>
                    <div class="mt-8">
                        <x-faq-accordion :items="$faqs" />
                    </div>
                </div>

                <div data-reveal data-reveal-delay="120">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">Get In Touch</span>
                    <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-navy-900 dark:text-white">
                        Looking for the Perfect Property?
                    </h2>

                    @if (session('status'))
                        <div class="mt-6 rounded-md border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST" class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        @csrf
                        <input type="hidden" name="type" value="general">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Name</label>
                            <input type="text" name="name" required class="w-full rounded-md border-navy-900/15 bg-white text-sm focus:border-sky-400 focus:ring-sky-400 dark:border-white/15 dark:bg-navy-950 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Phone</label>
                            <input type="text" name="phone" class="w-full rounded-md border-navy-900/15 bg-white text-sm focus:border-sky-400 focus:ring-sky-400 dark:border-white/15 dark:bg-navy-950 dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Email</label>
                            <input type="email" name="email" class="w-full rounded-md border-navy-900/15 bg-white text-sm focus:border-sky-400 focus:ring-sky-400 dark:border-white/15 dark:bg-navy-950 dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Message</label>
                            <textarea name="message" rows="3" class="w-full rounded-md border-navy-900/15 bg-white text-sm focus:border-sky-400 focus:ring-sky-400 dark:border-white/15 dark:bg-navy-950 dark:text-white"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="w-full rounded-md bg-sky-500 py-3.5 text-sm font-semibold text-white transition hover:bg-sky-400">
                                Send Message
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 flex flex-wrap items-center gap-6 border-t border-navy-900/10 pt-6 text-sm dark:border-white/10">
                        @if ($settings->whatsapp ?? false)
                            <a href="https://wa.me/{{ $settings->whatsapp }}" target="_blank" rel="noopener" class="flex items-center gap-2 font-semibold text-navy-900 dark:text-white">
                                <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.1-1.7-.9-2-1-.3-.1-.5-.1-.7.1-.2.3-.8 1-.9 1.1-.2.2-.3.2-.6.1-.3-.1-1.2-.5-2.4-1.5-.9-.8-1.5-1.8-1.6-2.1-.2-.3 0-.5.1-.6.1-.1.3-.3.4-.5.1-.1.2-.3.3-.5.1-.2 0-.4 0-.5C10 9 9.5 7.8 9.3 7.3c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.3.3-1 1-1 2.4s1 2.9 1.2 3.1c.1.2 2 3 4.8 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.7-.7 1.9-1.3.2-.7.2-1.2.2-1.3-.1-.2-.3-.2-.6-.4Z"/><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.1-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Z"/></svg>
                                {{ $settings->phone }}
                            </a>
                        @endif
                        @if ($settings->address ?? false)
                            <p class="flex items-center gap-2 text-slate-500 dark:text-white/50">
                                <svg class="h-4 w-4 shrink-0 text-flag-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $settings->address }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
