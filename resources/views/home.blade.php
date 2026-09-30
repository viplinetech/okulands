<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
<x-layouts.public :title="'Home'">

    <x-hero :images="$heroImages" />

    {{-- Three Pillars --}}
    <section class="bg-white py-24 dark:bg-navy-950">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">What We Do</span>
                <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                    Three Pillars of Growth
                </h2>
                <p class="mt-4 text-navy-600 dark:text-white/60">
                    A multi-sector company built to secure your land, build your future, and grow your wealth.
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach ([
                    ['icon' => 'M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5', 'title' => 'Real Estate', 'desc' => 'Verified residential and commercial land across prime Nigerian locations, secured and titled.'],
                    ['icon' => 'M3 21h18M6 21V9l6-4 6 4v12M10 21v-6h4v6', 'title' => 'Construction', 'desc' => 'Full-service building and infrastructure delivery, from foundation to finishing.'],
                    ['icon' => 'M12 2c3 3 4 6 4 9a4 4 0 1 1-8 0c0-3 1-6 4-9ZM12 22v-7', 'title' => 'Agriculture', 'desc' => 'Sustainable farmland investment and agribusiness ventures built for long-term returns.'],
                ] as $pillar)
                    <div class="group rounded-3xl border border-navy-100 bg-white p-8 shadow-premium transition hover:-translate-y-1 hover:shadow-premium-lg dark:border-white/10 dark:bg-navy-900">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-navy-800 to-navy-950 text-gold-400 transition group-hover:scale-105 dark:from-gold-500 dark:to-gold-600 dark:text-navy-950">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="{{ $pillar['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h3 class="mt-6 font-serif text-xl font-bold text-navy-900 dark:text-white">{{ $pillar['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-navy-600 dark:text-white/60">{{ $pillar['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Featured Properties --}}
    <section class="bg-navy-50 py-24 dark:bg-navy-900">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Featured Listings</span>
                    <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                        Properties Available Now
                    </h2>
                </div>
                <a href="{{ url('/properties') }}" class="rounded-full border border-navy-200 px-6 py-3 text-sm font-semibold text-navy-800 transition hover:border-gold-400 hover:text-gold-600 dark:border-white/20 dark:text-white">
                    View All Properties &rarr;
                </a>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($featuredProperties as $property)
                    <a href="{{ url('/properties/'.$property->slug) }}" class="group overflow-hidden rounded-3xl border border-navy-100 bg-white shadow-premium transition hover:-translate-y-1 hover:shadow-premium-lg dark:border-white/10 dark:bg-navy-950">
                        <div class="aspect-[4/3] overflow-hidden bg-navy-100 dark:bg-navy-800">
                            @if (!empty($property->images[0] ?? null))
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($property->images[0]) }}" alt="{{ $property->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="p-6">
                            <span class="text-xs font-bold uppercase tracking-wider text-gold-600 dark:text-gold-400">{{ str_replace('_', ' ', $property->sector) }}</span>
                            <h3 class="mt-2 font-serif text-lg font-bold text-navy-900 dark:text-white">{{ $property->title }}</h3>
                            <p class="mt-1 text-sm text-navy-500 dark:text-white/50">{{ $property->location }}</p>
                            <p class="mt-4 text-lg font-extrabold text-navy-900 dark:text-white">&#8358;{{ number_format($property->price) }}</p>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-navy-200 bg-white p-14 text-center text-navy-400 dark:border-white/10 dark:bg-navy-950 dark:text-white/40">
                        Featured properties will appear here once listings are added from the admin dashboard.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- CTA Strip --}}
    <section class="bg-gradient-to-br from-navy-900 to-navy-950 py-20">
        <div class="mx-auto max-w-4xl px-5 text-center sm:px-8">
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
