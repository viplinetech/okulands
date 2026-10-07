{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Status pill. Colour is derived from the status word.
--}}
@props(['status'])
@php
    $tone = match (strtolower((string) $status)) {
        'active', 'available', 'approved', 'paid', 'converted', 'published', 'yes', 'on' => 'green',
        'pending', 'new', 'reserved', 'draft', 'hot' => 'amber',
        'suspended', 'sold', 'closed', 'cancelled', 'rejected', 'no', 'off' => 'red',
        'contacted', 'admin' => 'blue',
        default => 'slate',
    };
@endphp
<span {{ $attributes->merge(['class' => 'badge badge-'.$tone]) }}>{{ $slot->isEmpty() ? ucfirst((string) $status) : $slot }}</span>
