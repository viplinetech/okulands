{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Admin menu links, rendered in both the desktop sidebar and the mobile drawer.
--}}
@php $counts = $counts ?? []; @endphp
@foreach (\App\Support\Nav::admin() as $group => $links)
    <p class="side-group">{{ $group }}</p>
    @foreach ($links as $link)
        @php $active = request()->routeIs($link['match']); @endphp
        <a href="{{ route($link['route']) }}" class="side-link {{ $active ? 'is-active' : '' }}" @if ($active) aria-current="page" @endif>
            <x-icon :name="$link['icon']" class="h-[1.15rem] w-[1.15rem]" />
            {{ $link['label'] }}
            @if (! empty($counts[$link['label']]))<span class="side-badge">{{ $counts[$link['label']] }}</span>@endif
        </a>
    @endforeach
@endforeach
