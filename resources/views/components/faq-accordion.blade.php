{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
--}}
@props(['items' => []])

<div class="divide-y divide-navy-900/10 border-y border-navy-900/10 dark:divide-white/10 dark:border-white/10">
    @foreach ($items as $index => $item)
        <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="py-5">
            <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center justify-between gap-4 text-left">
                <span class="font-serif text-base font-semibold text-navy-900 dark:text-white">{{ $item['q'] }}</span>
                <span class="flex h-6 w-6 shrink-0 items-center justify-center text-sky-600 dark:text-sky-400">
                    <svg class="h-4 w-4 transition-transform duration-300" :class="open ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                </span>
            </button>
            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                <p class="mt-3 text-sm leading-relaxed text-slate-500 dark:text-white/60">{{ $item['a'] }}</p>
            </div>
        </div>
    @endforeach
</div>
