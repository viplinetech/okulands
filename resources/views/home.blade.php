<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
<x-layouts.public :title="'Home'" :transparent-hero="true">

    <x-hero :images="$heroImages" />

    {{-- Spacer to clear the floating search card straddling the hero --}}
    <div class="h-24 bg-white dark:bg-navy-950 sm:h-20"></div>

    {{-- Trust marquee --}}
    <section class="overflow-hidden border-y border-navy-100 bg-navy-50 py-6 dark:border-white/10 dark:bg-navy-900">
        <div class="flex whitespace-nowrap animate-marquee">
            @foreach (array_merge(
                ['Verified Titles', 'Secure Escrow', 'Flexible Payment Plans', 'Realtor Rewarded', '24/7 Support', 'Trusted Since Day One'],
                ['Verified Titles', 'Secure Escrow', 'Flexible Payment Plans', 'Realtor Rewarded', '24/7 Support', 'Trusted Since Day One']
            ) as $item)
                <span class="mx-6 inline-flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-navy-500 dark:text-white/50">
                    <svg class="h-4 w-4 text-gold-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7 7a1 1 0 0 1-1.4 0l-3-3a1 1 0 1 1 1.4-1.4L9 11.6l6.3-6.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                    {{ $item }}
                </span>
            @endforeach
        </div>
    </section>

    {{-- Three Pillars --}}
    <section class="bg-white py-24 dark:bg-navy-950">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="mx-auto max-w-2xl text-center">
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
                ] as $index => $pillar)
                    <div data-reveal="scale" data-reveal-delay="{{ $index * 120 }}" class="group rounded-3xl border border-navy-100 bg-white p-8 shadow-premium transition duration-500 hover:-translate-y-2 hover:shadow-premium-lg dark:border-white/10 dark:bg-navy-900">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-navy-800 to-navy-950 text-gold-400 transition duration-500 group-hover:scale-110 group-hover:rotate-3 dark:from-gold-500 dark:to-gold-600 dark:text-navy-950">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="{{ $pillar['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h3 class="mt-6 font-serif text-xl font-bold text-navy-900 dark:text-white">{{ $pillar['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-navy-600 dark:text-white/60">{{ $pillar['desc'] }}</p>
                        <span class="mt-5 inline-flex items-center gap-1 text-sm font-bold text-gold-600 opacity-0 transition group-hover:opacity-100 dark:text-gold-400">
                            Learn more
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Stats band with count-up animation --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-navy-900 to-navy-950 py-20">
        <div class="absolute inset-0 opacity-[0.06] [background-image:linear-gradient(rgba(255,255,255,.4)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.4)_1px,transparent_1px)] [background-size:48px_48px]"></div>
        <div class="relative mx-auto grid max-w-6xl grid-cols-2 gap-8 px-5 sm:px-8 md:grid-cols-4">
            @foreach ([
                ['n' => 500, 'suffix' => '+', 'label' => 'Properties Sold'],
                ['n' => 1200, 'suffix' => '+', 'label' => 'Happy Clients'],
                ['n' => 150, 'suffix' => '+', 'label' => 'Active Realtors'],
                ['n' => 10, 'suffix' => '+', 'label' => 'Years of Trust'],
            ] as $index => $stat)
                <div data-reveal data-reveal-delay="{{ $index * 100 }}" class="text-center">
                    <div class="font-serif text-4xl font-extrabold text-gold-400 sm:text-5xl">
                        <span data-counter="{{ $stat['n'] }}" data-counter-suffix="{{ $stat['suffix'] }}">0</span>
                    </div>
                    <div class="mt-2 text-xs font-semibold uppercase tracking-[0.15em] text-white/60">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Featured Properties --}}
    <section class="bg-navy-50 py-24 dark:bg-navy-900">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Featured Listings</span>
                    <h2 class="mt-3 font-serif text-3xl font-extrabold text-navy-900 dark:text-white sm:text-4xl">
                        Properties Available Now
                    </h2>
                </div>
                <a href="{{ url('/properties') }}" class="group inline-flex items-center gap-2 rounded-full border border-navy-200 px-6 py-3 text-sm font-semibold text-navy-800 transition hover:border-gold-400 hover:text-gold-600 dark:border-white/20 dark:text-white">
                    View All Properties
                    <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($featuredProperties as $index => $property)
                    <a href="{{ url('/properties/'.$property->slug) }}" data-reveal data-reveal-delay="{{ $index * 100 }}" class="group overflow-hidden rounded-3xl border border-navy-100 bg-white shadow-premium transition duration-500 hover:-translate-y-2 hover:shadow-premium-lg dark:border-white/10 dark:bg-navy-950">
                        <div class="relative aspect-[4/3] overflow-hidden bg-navy-100 dark:bg-navy-800">
                            @if (!empty($property->images[0] ?? null))
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($property->images[0]) }}" alt="{{ $property->title }}" class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-110">
                            @endif
                            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-bold uppercase tracking-wider text-navy-800 backdrop-blur-sm">
                                {{ str_replace('_', ' ', $property->sector) }}
                            </span>
                        </div>
                        <div class="p-6">
                            <h3 class="font-serif text-lg font-bold text-navy-900 transition group-hover:text-gold-600 dark:text-white dark:group-hover:text-gold-400">{{ $property->title }}</h3>
                            <p class="mt-1 flex items-center gap-1 text-sm text-navy-500 dark:text-white/50">
                                <svg class="h-4 w-4 text-gold-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $property->location }}
                            </p>
                            <p class="mt-4 text-lg font-extrabold text-navy-900 dark:text-white">&#8358;{{ number_format($property->price) }}</p>
                        </div>
                    </a>
                @empty
                    <div data-reveal class="col-span-full rounded-3xl border border-dashed border-navy-200 bg-white p-14 text-center text-navy-400 dark:border-white/10 dark:bg-navy-950 dark:text-white/40">
                        Featured properties will appear here once listings are added from the admin dashboard.
                    </div>
                @endforelse
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
                        What Our Clients Say
                    </h2>
                </div>
                <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-3">
                    @foreach ($testimonials->take(3) as $index => $t)
                        <div data-reveal data-reveal-delay="{{ $index * 120 }}" class="rounded-3xl border border-navy-100 bg-navy-50 p-8 shadow-premium dark:border-white/10 dark:bg-navy-900">
                            <div class="flex gap-1 text-gold-500">
                                @for ($i = 0; $i < $t->rating; $i++)
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.6l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8Z"/></svg>
                                @endfor
                            </div>
                            <p class="mt-4 text-sm leading-relaxed text-navy-700 dark:text-white/70">&ldquo;{{ $t->content }}&rdquo;</p>
                            <p class="mt-6 font-bold text-navy-900 dark:text-white">{{ $t->client_name }}</p>
                            @if ($t->client_role)
                                <p class="text-xs text-navy-500 dark:text-white/50">{{ $t->client_role }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA Strip --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-navy-900 to-navy-950 py-20">
        <div class="absolute -left-20 top-1/2 h-72 w-72 -translate-y-1/2 rounded-full bg-gold-500/15 blur-[100px]"></div>
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
