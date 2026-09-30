{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
--}}
@props(['items' => []])

<div class="mx-auto max-w-3xl divide-y divide-navy-100 rounded-3xl border border-navy-100 bg-white shadow-premium dark:divide-white/10 dark:border-white/10 dark:bg-navy-900">
    @foreach ($items as $index => $item)
        <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="p-6">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-4 text-left">
                <span class="font-serif text-base font-bold text-navy-900 dark:text-white sm:text-lg">{{ $item['q'] }}</span>
                <svg class="h-5 w-5 shrink-0 text-gold-500 transition-transform duration-300" :class="open ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            </button>
            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                <p class="mt-4 text-sm leading-relaxed text-navy-600 dark:text-white/60">{{ $item['a'] }}</p>
            </div>
        </div>
    @endforeach
</div>
