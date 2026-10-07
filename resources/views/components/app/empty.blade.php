{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Friendly empty state with an optional call to action in the slot.
--}}
@props(['icon' => 'inbox', 'title', 'text' => null])
<div {{ $attributes->merge(['class' => 'px-6 py-12 text-center']) }}>
    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon :name="$icon" class="h-6 w-6" /></span>
    <h3 class="mt-5 font-serif text-2xl text-ink">{{ $title }}</h3>
    @if ($text)<p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-mute">{{ $text }}</p>@endif
    @if (trim((string) $slot) !== '')<div class="mt-6 flex flex-wrap justify-center gap-2">{{ $slot }}</div>@endif
</div>
