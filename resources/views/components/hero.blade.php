{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)

    Cinematic full-viewport hero. Images, headline and sub-headline all come
    from the admin (Settings); the defaults only show until they are set.
    Always dark (photo + navy scrim) in both themes so the type stays legible.
--}}
@props(['images' => [], 'headline', 'subheadline', 'locations' => []])

@php
    // Split the headline into a plain lead and an italic accent, at the first
    // punctuation mark, or halfway through the words if there is none.
    if (preg_match('/^(.+?[,.:;])\s+(.+)$/u', $headline, $m)) {
        [$lead, $accent] = [$m[1], $m[2]];
    } else {
        $words = preg_split('/\s+/', trim($headline));
        $cut = (int) ceil(count($words) / 2);
        [$lead, $accent] = [implode(' ', array_slice($words, 0, $cut)), implode(' ', array_slice($words, $cut))];
    }
@endphp

@push('head')
    @if (! empty($images[0]))
        <link rel="preload" as="image" href="{{ $images[0] }}" fetchpriority="high">
    @endif
@endpush

<section class="grain relative isolate flex min-h-[100svh] flex-col justify-end overflow-hidden bg-navy-950 text-white">
    <div class="absolute inset-0 -z-10">
        @foreach ($images as $i => $src)
            <img
                src="{{ $src }}"
                alt="{{ $i === 0 ? 'Premium land and property by Oku Lands & Properties, Nigeria' : '' }}"
                class="hero-slide absolute inset-0 h-full w-full object-cover {{ $i === 0 ? 'is-active' : '' }}"
                @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif
                decoding="async"
            >
        @endforeach
        <div class="absolute inset-0 bg-gradient-to-b from-navy-950/75 via-navy-950/45 to-navy-950"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-navy-950/90 via-navy-950/40 to-transparent"></div>

        <div class="aurora absolute -left-40 top-1/4 h-[36rem] w-[36rem] rounded-full bg-sky-500/25 blur-[130px]"></div>
        <div class="aurora absolute -right-32 bottom-10 h-[28rem] w-[28rem] rounded-full bg-navy-400/30 blur-[120px]" style="animation-delay:-8s"></div>
    </div>

    <div class="mx-auto w-full max-w-7xl px-5 pb-8 pt-32 sm:px-8 sm:pb-10 sm:pt-36">
        <p data-reveal data-delay="100" class="eyebrow whitespace-nowrap text-sky-300 !text-[clamp(0.5rem,2.6vw,0.62rem)] !tracking-[0.06em] sm:!text-[0.72rem] sm:!tracking-[0.25em] sm:!tracking-[0.25em]"><span class="sm:hidden">Bulk Lands &middot; Contractors &middot; Blocks</span><span class="hidden sm:inline">Bulk Lands &middot; General Contractors &middot; Block Industry</span></p>

        <h1 data-split data-delay="200" class="display mt-6 text-[clamp(3.1rem,10.5vw,9rem)]">
            {{ $lead }}<br>
            <span class="italic text-shine-inv">{{ $accent }}</span>
        </h1>

        <div class="mt-8 flex flex-col gap-8 sm:mt-10 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p data-reveal data-delay="600" class="max-w-md text-base leading-relaxed text-white/75 sm:text-lg">{{ $subheadline }}</p>
                <div data-reveal data-delay="750" class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('properties.index') }}" data-magnetic class="btn btn-sky">
                        Explore properties
                        <x-icon name="arrow" class="arrow h-4 w-4" />
                    </a>
                    <a href="{{ route('contact', ['interest' => 'inspection']) }}" data-magnetic class="btn btn-ghost">Book a free inspection</a>
                </div>
            </div>

        </div>

        {{-- Property search: frosted glass, labelled fields, location autocomplete --}}
        <form data-reveal data-delay="850" action="{{ route('properties.index') }}" method="GET" role="search" aria-label="Search properties"
              class="glass hairline mt-10 grid grid-cols-1 overflow-hidden rounded-3xl sm:grid-cols-2 lg:mt-12 lg:grid-cols-[1.4fr_1fr_1fr_auto] lg:rounded-full">
            <label class="group flex items-center gap-4 border-b border-white/10 px-5 py-3.5 transition focus-within:bg-white/10 sm:border-r sm:px-6 lg:border-b-0 lg:pl-8">
                <x-icon name="pin" class="h-5 w-5 shrink-0 text-sky-300" />
                <span class="min-w-0 flex-1">
                    <span class="block text-[0.62rem] font-semibold uppercase tracking-[0.22em] text-white/55">Location</span>
                    <input type="text" name="q" list="hero-locations" autocomplete="off" placeholder="City, area or keyword" class="mt-0.5 w-full border-0 bg-transparent p-0 text-[0.95rem] font-medium text-white placeholder:text-white/40 focus:ring-0">
                </span>
            </label>
            <label class="group flex items-center gap-4 border-b border-white/10 px-5 py-3.5 transition focus-within:bg-white/10 sm:px-6 lg:border-b-0 lg:border-r">
                <x-icon name="building" class="h-5 w-5 shrink-0 text-sky-300" />
                <span class="min-w-0 flex-1">
                    <span class="block text-[0.62rem] font-semibold uppercase tracking-[0.22em] text-white/55">Type</span>
                    <select name="sector" class="mt-0.5 w-full border-0 bg-transparent p-0 pr-6 text-[0.95rem] font-medium text-white focus:ring-0 [&>option]:text-navy-900">
                        <option value="">All types</option>
                        @foreach (\App\Models\Property::activeSectors() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </span>
            </label>
            <label class="group flex items-center gap-4 border-b border-white/10 px-5 py-3.5 transition focus-within:bg-white/10 sm:border-r sm:px-6 lg:border-b-0 lg:border-r">
                <x-icon name="wallet" class="h-5 w-5 shrink-0 text-sky-300" />
                <span class="min-w-0 flex-1">
                    <span class="block text-[0.62rem] font-semibold uppercase tracking-[0.22em] text-white/55">Budget</span>
                    <select name="budget" class="mt-0.5 w-full border-0 bg-transparent p-0 pr-6 text-[0.95rem] font-medium text-white focus:ring-0 [&>option]:text-navy-900">
                        <option value="">Any budget</option>
                        <option value="0-5000000">Under &#8358;5M</option>
                        <option value="5000000-20000000">&#8358;5M &ndash; &#8358;20M</option>
                        <option value="20000000-">Above &#8358;20M</option>
                    </select>
                </span>
            </label>
            <button type="submit" class="btn btn-sky m-2 sm:col-span-2 lg:col-span-1 lg:px-9">
                <x-icon name="search" class="h-4 w-4" />
                Search
            </button>
            @if ($locations)
                <datalist id="hero-locations">
                    @foreach ($locations as $loc)
                        <option value="{{ $loc }}"></option>
                    @endforeach
                </datalist>
            @endif
        </form>
    </div>
</section>
