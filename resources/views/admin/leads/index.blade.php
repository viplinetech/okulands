<x-layouts.admin title="Leads">
    <x-app.page-header title="Leads" kicker="Sales" subtitle="Every enquiry from the website, with the realtor it came through.">
        <a href="{{ route('admin.reports.export', ['type' => 'leads']) }}" class="btn btn-outline btn-sm"><x-icon name="download" class="h-4 w-4" /> Export</a>
    </x-app.page-header>

    <div class="tabs mb-4">
        <a href="{{ route('admin.leads.index') }}" class="tab {{ ! $status ? 'is-active' : '' }}">All <span class="opacity-60">{{ $counts->sum() }}</span></a>
        @foreach ($statuses as $s)
            <a href="{{ route('admin.leads.index', ['status' => $s]) }}" class="tab {{ $status === $s ? 'is-active' : '' }}">{{ ucfirst($s) }} <span class="opacity-60">{{ $counts[$s] ?? 0 }}</span></a>
        @endforeach
    </div>

    <form method="GET" class="card mb-4 grid gap-3 !p-3 sm:!p-4 md:grid-cols-[1fr_auto_auto]">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <label class="relative block">
            <span class="sr-only">Search</span>
            <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-mute" />
            <input type="search" name="q" value="{{ $term }}" placeholder="Search name, email or phone…" class="field !rounded-full !py-3 !pl-11">
        </label>
        <select name="source" class="field !rounded-full !py-3 md:w-44" aria-label="Source">
            <option value="">All sources</option>
            <option value="referred" @selected(request('source') === 'referred')>Via a realtor</option>
            <option value="direct" @selected(request('source') === 'direct')>Direct</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm !py-3">Filter</button>
    </form>

    <div class="card-flush">
        @if ($leads->count())
            <table class="tbl">
                <thead><tr><th>Lead</th><th>Interested in</th><th>Referred by</th><th>Status</th><th>Received</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                @foreach ($leads as $lead)
                    <tr>
                        <td class="cell-main">
                            <a href="{{ route('admin.leads.show', $lead) }}" class="flex items-center gap-3 text-left">
                                <span class="avatar h-10 w-10 text-sm">{{ Str::upper(Str::substr($lead->name, 0, 1)) }}</span>
                                <div class="min-w-0"><p class="truncate font-bold text-ink">{{ $lead->name }}</p><p class="truncate text-xs font-normal text-mute">{{ $lead->phone ?: $lead->email }}</p></div>
                            </a>
                        </td>
                        <td data-label="Interested in" class="max-w-[14rem]"><span class="line-clamp-2">{{ $lead->property?->title ?? ucfirst($lead->type) }}</span></td>
                        <td data-label="Referred by">{{ $lead->referrer?->name ?? 'Direct' }}</td>
                        <td data-label="Status"><x-app.badge :status="$lead->status" /></td>
                        <td data-label="Received" class="whitespace-nowrap text-mute">{{ $lead->created_at->format('M j, g:ia') }}</td>
                        <td class="cell-actions">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.leads.show', $lead) }}" class="icon-action" aria-label="Open" title="Open"><x-icon name="eye" class="h-4 w-4" /></a>
                                <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" data-confirm="Delete this lead permanently?">@csrf @method('DELETE')
                                    <button type="submit" class="icon-action icon-action-danger" aria-label="Delete"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="inbox" title="No leads found" text="Enquiries from the website contact form and property pages will appear here." />
        @endif
    </div>
    <div class="mt-6">{{ $leads->links('pagination.premium') }}</div>
</x-layouts.admin>
