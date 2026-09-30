{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)

    Full-viewport cinematic hero: one headline, one supporting line, the
    trust-stat strip on the dark background (replaces a separate stats
    section), and a floating search bar straddling the hero's bottom edge.
--}}
@props(['images' => []])

@php
    // Admin-uploaded hero images take priority; otherwise rotate through a
    // curated set of Nigeria-relevant real estate/construction/land photography
    // so the hero never feels static while waiting for real uploads.
    $rotatingImages = count($images) > 0 ? $images : [
        'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1920&q=80',
        'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80',
        'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1920&q=80',
        'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1920&q=80',
    ];
@endphp

<section
    x-data="{
        images: {{ Illuminate\Support\Js::from($rotatingImages) }},
        active: 0,
        init() {
            if (this.images.length > 1) {
                setInterval(() => { this.active = (this.active + 1) % this.images.length; }, 6000);
            }
        }
    }"
    class="relative flex min-h-[92vh] items-center overflow-hidden bg-navy-950"
>
    {{-- Background image(s) with navy overlay for legibility --}}
    <div class="absolute inset-0">
        <template x-for="(img, i) in images" :key="i">
            <img
                :src="img"
                alt="Oku Lands & Properties: real estate, construction and agriculture across Nigeria"
                class="absolute inset-0 h-full w-full object-cover transition-opacity duration-[2200ms] ease-in-out"
                :class="active === i ? 'opacity-100 animate-kenburns' : 'opacity-0'"
                loading="eager"
            >
        </template>
        <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/75 to-navy-950/40"></div>
        <div class="absolute inset-0 bg-navy-950/20"></div>

        {{-- Subtle animated glow, adds motion without clutter --}}
        <div class="absolute -left-32 top-0 h-[32rem] w-[32rem] rounded-full bg-sky-500/20 blur-[110px] animate-heroglow"></div>
        <div class="absolute -right-24 bottom-0 h-96 w-96 rounded-full bg-navy-400/20 blur-[110px] animate-heroglow" style="animation-delay:4s"></div>
    </div>

    <div class="relative z-10 mx-auto w-full max-w-7xl px-5 pb-28 pt-28 sm:px-8">
        <div class="max-w-2xl">
            <span data-reveal class="inline-block text-xs font-semibold uppercase tracking-[0.25em] text-sky-300">
                Real Estate &middot; Construction &middot; Agriculture
            </span>

            <h1 data-reveal data-reveal-delay="80" class="mt-5 font-serif text-4xl font-semibold leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-[3.4rem]">
                Land. Homes.
                <span class="block bg-gradient-to-r from-sky-300 via-white to-sky-300 bg-clip-text text-transparent animate-text-shine">Legacy.</span>
                Secured across Nigeria.
            </h1>

            <p data-reveal data-reveal-delay="160" class="mt-5 max-w-lg text-base leading-relaxed text-white/70">
                Verified land and property opportunities across Real Estate, Construction and Agriculture,
                backed by a company built on trust and a referral network that rewards every realtor.
            </p>

            <div data-reveal data-reveal-delay="240" class="mt-9 flex flex-wrap gap-4">
                <a href="{{ url('/properties') }}" class="rounded-md bg-sky-500 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-sky-400">
                    Browse Properties
                </a>
                <a href="{{ url('/contact') }}" class="rounded-md border border-white/30 px-7 py-3.5 text-sm font-semibold text-white transition hover:border-white/60">
                    Book a Free Inspection
                </a>
            </div>

            {{-- Trust stat strip --}}
            <div data-reveal data-reveal-delay="320" class="mt-14 grid max-w-lg grid-cols-2 divide-x divide-white/15 sm:grid-cols-4">
                @foreach ([
                    ['n' => 1101, 'suffix' => '+', 'label' => 'Happy Clients'],
                    ['n' => 459, 'suffix' => '+', 'label' => 'Properties Sold'],
                    ['n' => 137, 'suffix' => '+', 'label' => 'Active Realtors'],
                    ['n' => 3, 'suffix' => '', 'label' => 'Sectors, One Co.'],
                ] as $stat)
                    <div class="px-4 py-2 text-center first:pl-0 sm:text-left">
                        <div class="font-serif text-xl font-semibold text-white sm:text-2xl">
                            <span data-counter="{{ $stat['n'] }}" data-counter-suffix="{{ $stat['suffix'] }}">0</span>
                        </div>
                        <div class="mt-1 text-[0.65rem] font-medium uppercase tracking-wider text-white/45">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Floating search bar, straddling the hero's bottom edge --}}
    <div data-reveal data-reveal-delay="400" class="absolute inset-x-0 -bottom-9 z-20 px-5 sm:px-8">
        <div class="mx-auto max-w-5xl rounded-lg border border-navy-900/10 bg-white shadow-premium-lg dark:border-white/10 dark:bg-navy-900">
            <form action="{{ url('/properties') }}" method="GET" class="grid grid-cols-1 divide-y divide-navy-900/10 sm:grid-cols-4 sm:divide-x sm:divide-y-0 dark:divide-white/10">
                <div class="px-5 py-4">
                    <label class="block text-[0.65rem] font-semibold uppercase tracking-widest text-slate-400">Location</label>
                    <input type="text" name="q" placeholder="e.g. Amansea, Awka" class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-navy-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 dark:text-white">
                </div>
                <div class="px-5 py-4">
                    <label class="block text-[0.65rem] font-semibold uppercase tracking-widest text-slate-400">Type</label>
                    <select name="sector" class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-navy-900 focus:outline-none focus:ring-0 dark:text-white">
                        <option value="">Any Sector</option>
                        <option value="real_estate">Real Estate</option>
                        <option value="construction">Construction</option>
                        <option value="agriculture">Agriculture</option>
                    </select>
                </div>
                <div class="px-5 py-4">
                    <label class="block text-[0.65rem] font-semibold uppercase tracking-widest text-slate-400">Budget</label>
                    <select name="budget" class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-navy-900 focus:outline-none focus:ring-0 dark:text-white">
                        <option value="">Any Budget</option>
                        <option value="0-5000000">Under &#8358;5M</option>
                        <option value="5000000-20000000">&#8358;5M &ndash; &#8358;20M</option>
                        <option value="20000000-">Above &#8358;20M</option>
                    </select>
                </div>
                <button type="submit" class="flex items-center justify-center gap-2 bg-sky-500 px-5 py-4 text-sm font-semibold text-white transition hover:bg-sky-400 sm:rounded-r-lg">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                    Search
                </button>
            </form>
        </div>
    </div>
</section>
