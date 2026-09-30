{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Framed image with a diagonal accent corner and a floating stat badge,
    the pattern used across premium real-estate "About" sections.
--}}
@props(['image' => null, 'statValue' => '500+', 'statLabel' => 'Completed Projects'])

<div class="relative">
    <div class="relative overflow-hidden rounded-[2rem] border border-navy-100 bg-navy-100 shadow-premium-lg dark:border-white/10 dark:bg-navy-800">
        <div class="aspect-[4/5]">
            @if ($image)
                <img src="{{ $image }}" alt="Oku Lands & Properties" class="h-full w-full object-cover">
            @else
                <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(91,155,240,.2),transparent_50%),radial-gradient(circle_at_80%_80%,rgba(42,69,147,.25),transparent_50%),linear-gradient(160deg,#eef1f8_0%,#d6ddef_100%)] dark:bg-[linear-gradient(160deg,#101c44_0%,#15265a_100%)]">
                    <x-brand-mark class="h-24 w-24 opacity-70" />
                </div>
            @endif
        </div>
        {{-- Diagonal accent corner --}}
        <div class="absolute -left-10 -top-10 h-28 w-28 rotate-45 bg-gradient-to-br from-accent-400 to-accent-600"></div>
    </div>

    {{-- Floating stat badge --}}
    <div class="absolute -bottom-8 -right-6 flex items-center gap-4 rounded-2xl border border-navy-100 bg-white px-6 py-5 shadow-premium-lg dark:border-white/10 dark:bg-navy-900 sm:-right-10">
        <div class="font-serif text-3xl font-extrabold text-accent-600 dark:text-accent-400">{{ $statValue }}</div>
        <div class="max-w-[7rem] text-xs font-semibold uppercase leading-snug tracking-wide text-navy-600 dark:text-white/60">{{ $statLabel }}</div>
    </div>
</div>
