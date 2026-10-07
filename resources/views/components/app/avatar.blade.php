{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Round profile picture, falling back to initials.
--}}
@props(['user', 'size' => 'h-10 w-10 text-sm'])
<span {{ $attributes->merge(['class' => 'avatar '.$size]) }}>
    @if ($user->avatarUrl())
        <img src="{{ $user->avatarUrl() }}" alt="" class="h-full w-full object-cover" loading="lazy">
    @else
        {{ $user->initials() }}
    @endif
</span>
