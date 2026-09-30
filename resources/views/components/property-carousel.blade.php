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
            <a
                href="{{ url('/properties/'.$property->slug) }}"
                data-card
                data-reveal
                data-reveal-delay="{{ $index * 100 }}"
                class="group w-[280px] shrink-0 snap-start overflow-hidden rounded-3xl border border-navy-100 bg-white shadow-premium transition duration-500 hover:-translate-y-2 hover:shadow-premium-lg dark:border-white/10 dark:bg-navy-950 sm:w-[320px]"
            >
                <div class="relative aspect-[4/3] overflow-hidden bg-navy-100 dark:bg-navy-800">
                    @if (!empty($property->images[0] ?? null))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($property->images[0]) }}" alt="{{ $property->title }}" class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-110">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-navy-100 text-navy-300 dark:bg-navy-800 dark:text-white/20">
                            <x-brand-mark class="h-16 w-16 opacity-50" />
                        </div>
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
        @endforeach
    </div>

    <div class="mt-6 flex items-center justify-center gap-3 sm:absolute sm:-top-20 sm:right-0 sm:mt-0">
        <button type="button" @click="scrollByCards(-1)" class="flex h-11 w-11 items-center justify-center rounded-full border border-navy-200 text-navy-700 transition hover:border-gold-400 hover:text-gold-600 dark:border-white/20 dark:text-white" aria-label="Previous">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <button type="button" @click="scrollByCards(1)" class="flex h-11 w-11 items-center justify-center rounded-full border border-navy-200 text-navy-700 transition hover:border-gold-400 hover:text-gold-600 dark:border-white/20 dark:text-white" aria-label="Next">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>
</div>
