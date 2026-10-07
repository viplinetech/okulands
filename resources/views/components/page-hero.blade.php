{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Banner used at the top of every inner page. Photo comes from the admin (Settings → page banners).
--}}
@props(['eyebrow' => null, 'title', 'accent' => null, 'subtitle' => null, 'image', 'crumb' => null, 'compact' => false])

@push('head')
    <link rel="preload" as="image" href="{{ $image }}" fetchpriority="high">
@endpush

<section class="grain relative isolate overflow-hidden rounded-b-[2rem] bg-navy-950 text-white md:rounded-b-[3rem]">
    <div class="absolute inset-0 -z-10">
        <img src="{{ $image }}" alt="" class="h-full w-full object-cover" fetchpriority="high" decoding="async">
        <div class="absolute inset-0 bg-gradient-to-b from-navy-950/85 via-navy-950/60 to-navy-950/95"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-navy-950/80 via-navy-950/30 to-transparent"></div>
        <div class="aurora absolute -left-32 top-10 h-[26rem] w-[26rem] rounded-full bg-sky-500/25 blur-[120px]"></div>
    </div>

    <div class="mx-auto max-w-7xl px-5 pb-10 pt-28 sm:px-8 md:pb-16 md:pt-36">
        <nav aria-label="Breadcrumb" data-reveal class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-white/55">
            <a href="{{ route('home') }}" class="transition hover:text-white">Home</a>
            <span aria-hidden="true">/</span>
            <span class="text-sky-300" aria-current="page">{{ $crumb ?? $title }}</span>
        </nav>

        @if ($eyebrow)
            <p data-reveal data-delay="80" class="eyebrow mt-8 text-sky-300">{{ $eyebrow }}</p>
        @endif

        <h1 data-split data-delay="120" class="display mt-5 max-w-4xl {{ $compact ? 'text-[clamp(2.2rem,6vw,4.6rem)]' : 'text-[clamp(2.7rem,8.5vw,6.5rem)]' }}">
            {{ $title }}@if ($accent) <span class="italic text-shine-inv">{{ $accent }}</span>@endif
        </h1>

        @if ($subtitle)
            <p data-reveal data-delay="450" class="mt-6 max-w-xl text-base leading-relaxed text-white/70 sm:text-lg">{{ $subtitle }}</p>
        @endif

        {{ $slot }}
    </div>
</section>
