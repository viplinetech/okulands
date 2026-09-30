{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
--}}
@props(['properties'])

<div x-data="{
    scrollByCards(dir) {
        const track = $refs.track;
        const card = track.querySelector('[data-card]');
        const gap = 32;
        const width = card ? card.offsetWidth + gap : 320;
        track.scrollBy({ left: dir * width, behavior: 'smooth' });
    }
}" class="relative">

    <div x-ref="track" class="flex snap-x snap-mandatory gap-8 overflow-x-auto scroll-smooth pb-4 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @foreach ($properties as $index => $property)
            <div
                data-card
                data-reveal
                data-reveal-delay="{{ $index * 80 }}"
                class="group w-[280px] shrink-0 snap-start overflow-hidden rounded-lg border border-navy-900/10 bg-white transition duration-500 hover:shadow-premium dark:border-white/10 dark:bg-navy-900 sm:w-[320px]"
            >
                <a href="{{ url('/properties/'.$property->slug) }}" class="block">
                    <div class="relative aspect-[4/3] overflow-hidden bg-navy-100 dark:bg-navy-800">
                        <img
                            src="{{ !empty($property->images[0] ?? null) ? \Illuminate\Support\Facades\Storage::url($property->images[0]) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80' }}"
                            alt="{{ $property->title }}"
                            class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                            loading="lazy" width="320" height="240"
                        >
                        <span class="absolute left-3 top-3 rounded-sm bg-navy-950/85 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wider text-white">
                            {{ ucwords(str_replace('_', ' ', $property->sector)) }}
                        </span>
                        <span class="absolute right-3 top-3 rounded-sm bg-white/95 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wider text-navy-900">
                            {{ ucfirst($property->status) }}
                        </span>
                    </div>
                    <div class="p-5">
                        <h3 class="font-serif text-lg font-semibold text-navy-900 dark:text-white">{{ $property->title }}</h3>
                        <p class="mt-1 flex items-center gap-1 text-sm text-slate-500 dark:text-white/50">
                            <svg class="h-3.5 w-3.5 text-flag-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $property->location }}
                        </p>
                        @if ($property->size)
                            <p class="mt-1 text-xs text-slate-400 dark:text-white/40">{{ $property->size }} sqm</p>
                        @endif
                        <p class="mt-3 font-serif text-lg font-semibold text-navy-900 dark:text-white">&#8358;{{ number_format($property->price) }}</p>
                    </div>
                </a>
                <div class="border-t border-navy-900/10 p-4 dark:border-white/10">
                    <a href="{{ url('/contact') }}" class="block w-full rounded-md border border-navy-900/15 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-navy-800 transition hover:border-sky-400 hover:text-sky-600 dark:border-white/15 dark:text-white">
                        Book Inspection
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex items-center justify-center gap-3 sm:absolute sm:-top-20 sm:right-0 sm:mt-0">
        <button type="button" @click="scrollByCards(-1)" class="flex h-10 w-10 items-center justify-center rounded-full border border-navy-900/15 text-navy-700 transition hover:border-sky-400 hover:text-sky-600 dark:border-white/20 dark:text-white" aria-label="Previous">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <button type="button" @click="scrollByCards(1)" class="flex h-10 w-10 items-center justify-center rounded-full border border-navy-900/15 text-navy-700 transition hover:border-sky-400 hover:text-sky-600 dark:border-white/20 dark:text-white" aria-label="Next">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>
</div>
