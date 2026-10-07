{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Small bar chart, drawn in pure CSS (no chart library). data = [['label' => 'Jan', 'value' => 1200], …]
--}}
@props(['data', 'money' => false, 'height' => 'h-36'])
@php
    $max = max(1, collect($data)->max('value'));
    $last = count($data) - 1;
@endphp
<div {{ $attributes->merge(['class' => 'flex items-end gap-2 sm:gap-3 '.$height]) }} role="img" aria-label="Bar chart">
    @foreach ($data as $i => $bar)
        @php $pct = $bar['value'] > 0 ? max(6, round($bar['value'] / $max * 100)) : 3; @endphp
        <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2" title="{{ $bar['label'] }}: {{ $money ? '₦'.number_format($bar['value']) : number_format($bar['value']) }}">
            <span class="text-[0.62rem] font-bold text-mute">{{ $bar['value'] > 0 ? ($money ? '₦'.(($bar['value'] >= 1000000) ? round($bar['value'] / 1000000, 1).'M' : (($bar['value'] >= 1000) ? round($bar['value'] / 1000).'k' : round($bar['value']))) : number_format($bar['value'])) : '' }}</span>
            <div class="w-full rounded-t-xl {{ $i === $last ? 'bg-brand' : 'bg-brand/30' }} origin-bottom" style="height: {{ $pct }}%; animation: bar-grow .9s var(--ease-out) both; animation-delay: {{ $i * 70 }}ms"></div>
            <span class="text-[0.66rem] font-semibold uppercase tracking-wide text-mute">{{ $bar['label'] }}</span>
        </div>
    @endforeach
</div>
