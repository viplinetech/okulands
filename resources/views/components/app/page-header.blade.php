{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Title block at the top of every app page. Actions (buttons) go in the slot.
--}}
@props(['title', 'kicker' => null, 'subtitle' => null])
<div class="mb-6 flex flex-wrap items-end justify-between gap-4 sm:mb-8">
    <div class="min-w-0">
        @if ($kicker)<p class="kicker">{{ $kicker }}</p>@endif
        <h1 class="page-title mt-1.5">{{ $title }}</h1>
        @if ($subtitle)<p class="mt-2 max-w-xl text-sm leading-relaxed text-mute">{{ $subtitle }}</p>@endif
    </div>
    @if (trim((string) $slot) !== '')
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
