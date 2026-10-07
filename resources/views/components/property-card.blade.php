{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    One listing card, reused by the Properties grid, the home rail and "similar properties".
--}}
@props(['property'])

@php
    $statusStyles = [
        'available' => 'bg-white/90 text-navy-900',
        'reserved' => 'bg-amber-300/95 text-navy-950',
        'sold' => 'bg-navy-950/80 text-white',
    ];
@endphp

<article {{ $attributes->merge(['class' => 'group']) }}>
<a href="{{ route('properties.show', $property->slug) }}" draggable="false" class="block">
    <div class="relative aspect-[4/5] overflow-hidden rounded-[1.75rem] bg-soft sm:aspect-[5/6]">
        <img
            src="{{ $property->coverUrl() }}"
            alt="{{ $property->title }}"
            draggable="false"
            data-fit class="h-full w-full object-cover transition duration-[1400ms] ease-out group-hover:scale-[1.06] {{ $property->status === 'sold' ? 'grayscale-[60%]' : '' }}"
            loading="lazy" width="480" height="576"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-navy-950/85 via-navy-950/10 to-navy-950/20"></div>

        <div class="absolute inset-x-4 top-4 flex items-start justify-between gap-2">
            <span class="rounded-full bg-white/90 px-3.5 py-1.5 text-[0.65rem] font-bold uppercase tracking-[0.14em] text-navy-900 backdrop-blur">{{ $property->sectorLabel() }}</span>
            <span class="rounded-full px-3.5 py-1.5 text-[0.65rem] font-bold uppercase tracking-[0.14em] backdrop-blur {{ $statusStyles[$property->status] ?? $statusStyles['available'] }}">{{ ucfirst($property->status) }}</span>
        </div>

        <div class="absolute inset-x-5 bottom-5 text-white">
            <p class="font-serif text-3xl leading-none sm:text-[2rem]">&#8358;{{ number_format($property->price) }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-medium text-white/75">
                @if ($property->size)
                    <span class="inline-flex items-center gap-1.5"><x-icon name="ruler" class="h-3.5 w-3.5" />{{ $property->size }} sqm</span>
                @endif
                @if ($property->bedrooms)
                    <span class="inline-flex items-center gap-1.5"><x-icon name="bed" class="h-3.5 w-3.5" />{{ $property->bedrooms }} bed</span>
                @endif
                @if ($property->bathrooms)
                    <span class="inline-flex items-center gap-1.5"><x-icon name="bath" class="h-3.5 w-3.5" />{{ $property->bathrooms }} bath</span>
                @endif
            </div>
        </div>
    </div>

    <div class="min-w-0 px-1.5 pt-5">
        <h3 class="font-serif text-2xl leading-tight text-ink transition group-hover:text-brand">{{ $property->title }}</h3>
        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-mute">
            <x-icon name="pin" class="h-3.5 w-3.5 shrink-0 text-flag-500" />
            <span class="truncate">{{ $property->location }}</span>
        </p>
    </div>
</a>

<div class="px-1.5 pt-4">
    <a href="{{ route('properties.show', $property->slug) }}#book" draggable="false" class="btn {{ $property->status === 'sold' ? 'btn-outline' : 'btn-primary' }} btn-sm w-full">
        {{ $property->status === 'sold' ? c('property.card.button_sold') : c('property.card.button') }}
        <x-icon name="arrow" class="arrow h-4 w-4" />
    </a>
</div>
</article>
