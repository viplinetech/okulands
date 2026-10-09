<x-layouts.admin :title="$c['title']">
    <x-app.page-header :title="$c['title']" kicker="Website content" :subtitle="$c['subtitle'] ?? null">
        @if (! empty($c['generateAi']))
            <form method="POST" action="{{ route($c['generateAi']['route']) }}" data-confirm="OkuLands Smart AI will write, design a cover for, and publish a complete post immediately, with no review step. Continue?" data-confirm-yes="Generate &amp; publish">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm"><x-icon name="sparkle" class="h-4 w-4" /> {{ $c['generateAi']['label'] }}</button>
            </form>
        @endif
        <a href="{{ route($c['route'].'.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" /> Add {{ $c['singular'] }}</a>
    </x-app.page-header>

    @isset($c['before'])@include($c['before'])@endisset

    {{-- Search + filters --}}
    @if ($c['search'] || $c['filters'])
        <form method="GET" class="card mb-4 grid gap-3 !p-3 sm:!p-4 md:grid-cols-[1fr_auto_auto] {{ count($c['filters']) > 1 ? 'md:grid-cols-[1fr_auto_auto_auto]' : '' }}">
            @if ($c['search'])
                <label class="relative block">
                    <span class="sr-only">Search</span>
                    <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-mute" />
                    <input type="search" name="q" value="{{ $q }}" placeholder="Search {{ strtolower($c['title']) }}…" class="field !rounded-full !py-3 !pl-11">
                </label>
            @endif
            @foreach ($c['filters'] as $filter)
                <select name="{{ $filter['name'] }}" class="field !rounded-full !py-3 md:w-44" aria-label="{{ $filter['label'] }}">
                    <option value="">{{ $filter['label'] }}</option>
                    @foreach ($filter['options'] as $value => $label)
                        <option value="{{ $value }}" @selected((string) request($filter['name']) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            @endforeach
            <button type="submit" class="btn btn-primary btn-sm !py-3">Filter</button>
        </form>
    @endif

    <div class="card-flush">
        @if ($items->count())
            <table class="tbl">
                <thead>
                    <tr>
                        @foreach ($c['columns'] as $col)<th>{{ $col['label'] }}</th>@endforeach
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($items as $item)
                    <tr>
                        @foreach ($c['columns'] as $col)
                            @php
                                $value = isset($col['value']) ? $col['value']($item) : data_get($item, $col['key'] ?? '');
                                $type = $col['type'] ?? 'text';
                            @endphp
                            <td @if (! empty($col['main'])) class="cell-main" @else data-label="{{ $col['label'] }}" @endif>
                                @switch($type)
                                    @case('image')
                                        <span class="thumb !aspect-square block h-12 w-12 !rounded-xl">@if ($value)<img src="{{ $value }}" alt="" class="h-full w-full object-cover" loading="lazy">@endif</span>
                                        @break
                                    @case('badge')<x-app.badge :status="(string) $value" />@break
                                    @case('bool')<x-app.badge :status="$value ? 'yes' : 'no'">{{ $value ? 'Yes' : 'No' }}</x-app.badge>@break
                                    @case('money')<span class="font-bold text-ink">₦{{ number_format((float) $value) }}</span>@break
                                    @case('date')<span class="whitespace-nowrap text-mute">{{ $value ? \Illuminate\Support\Carbon::parse($value)->format('M j, Y') : '—' }}</span>@break
                                    @case('limit')<span class="line-clamp-2 text-mute">{{ \Illuminate\Support\Str::limit(strip_tags((string) $value), 90) }}</span>@break
                                    @default
                                        @if (! empty($col['main']))
                                            <div class="min-w-0 text-left">
                                                <p class="truncate font-bold text-ink">{{ $value }}</p>
                                                @if (! empty($col['sub']))<p class="truncate text-xs font-normal text-mute">{{ $col['sub']($item) }}</p>@endif
                                            </div>
                                        @else
                                            {{ $value }}
                                        @endif
                                @endswitch
                            </td>
                        @endforeach
                        <td class="cell-actions">
                            <div class="flex justify-end gap-2">
                                @if ($c['view'] && ($url = $c['view']($item)))
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="icon-action" aria-label="View on website" title="View on website"><x-icon name="external" class="h-4 w-4" /></a>
                                @endif
                                <a href="{{ route($c['route'].'.edit', $item) }}" class="icon-action" aria-label="Edit" title="Edit"><x-icon name="edit" class="h-4 w-4" /></a>
                                <form method="POST" action="{{ route($c['route'].'.destroy', $item) }}" data-confirm="Delete this {{ $c['singular'] }}? This cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-action icon-action-danger" aria-label="Delete" title="Delete"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty :icon="$c['icon'] ?? 'inbox'" :title="'No '.strtolower($c['title']).' yet'" :text="$q ? 'Nothing matches your search.' : 'Add your first '.$c['singular'].' to get started.'">
                <a href="{{ route($c['route'].'.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" /> Add {{ $c['singular'] }}</a>
            </x-app.empty>
        @endif
    </div>

    <div class="mt-6">{{ $items->links('pagination.premium') }}</div>
</x-layouts.admin>
