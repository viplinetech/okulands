{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    KPI tile. `hero` gives the dark gradient treatment. Numbers count up when `count` is given.
--}}
@props(['icon' => 'trend', 'label', 'value', 'hint' => null, 'hero' => false, 'count' => null, 'prefix' => '', 'suffix' => ''])
<div {{ $attributes->merge(['class' => 'stat '.($hero ? 'stat-hero' : '')]) }}>
    <span class="stat-icon"><x-icon :name="$icon" class="h-5 w-5" /></span>
    <p class="stat-value">
        @if ($count !== null)
            {{ $prefix }}<span data-counter="{{ $count }}" data-suffix="{{ $suffix }}">{{ number_format($count) }}{{ $suffix }}</span>
        @else
            {{ $prefix }}{{ $value }}{{ $suffix }}
        @endif
    </p>
    <p class="stat-label">{{ $label }}</p>
    @if ($hint)<p class="mt-1.5 text-xs {{ $hero ? 'text-white/55' : 'text-mute' }}">{{ $hint }}</p>@endif
</div>
