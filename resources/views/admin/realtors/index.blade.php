<x-layouts.admin title="Realtors">
    <x-app.page-header title="Realtors" kicker="People" subtitle="Everyone who sells for Oku Lands, with their results.">
        <a href="{{ route('admin.reports.export', ['type' => 'realtors']) }}" class="btn btn-outline btn-sm"><x-icon name="download" class="h-4 w-4" /> Export</a>
        <a href="{{ route('admin.realtors.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" /> Add realtor</a>
    </x-app.page-header>

    <div class="tabs mb-4">
        <a href="{{ route('admin.realtors.index') }}" class="tab {{ ! $status ? 'is-active' : '' }}">All <span class="opacity-60">{{ $counts->sum() }}</span></a>
        <a href="{{ route('admin.realtors.index', ['status' => 'active']) }}" class="tab {{ $status === 'active' ? 'is-active' : '' }}">Active <span class="opacity-60">{{ $counts['active'] ?? 0 }}</span></a>
        <a href="{{ route('admin.realtors.index', ['status' => 'suspended']) }}" class="tab {{ $status === 'suspended' ? 'is-active' : '' }}">Suspended <span class="opacity-60">{{ $counts['suspended'] ?? 0 }}</span></a>
    </div>

    <form method="GET" class="card mb-4 grid gap-3 !p-3 sm:!p-4 md:grid-cols-[1fr_auto]">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <label class="relative block"><span class="sr-only">Search</span><x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-mute" /><input type="search" name="q" value="{{ $term }}" placeholder="Search name, email, phone or referral code…" class="field !rounded-full !py-3 !pl-11"></label>
        <button type="submit" class="btn btn-primary btn-sm !py-3">Search</button>
    </form>

    <div class="card-flush">
        @if ($realtors->count())
            <table class="tbl">
                <thead><tr><th>Realtor</th><th>Leads</th><th>Sales</th><th>Team</th><th>Earned</th><th>Status</th><th>Joined</th></tr></thead>
                <tbody>
                @foreach ($realtors as $r)
                    <tr>
                        <td class="cell-main">
                            <a href="{{ route('admin.realtors.show', $r) }}" class="flex items-center gap-3 text-left">
                                <x-app.avatar :user="$r" />
                                <div class="min-w-0"><p class="truncate font-bold text-ink">{{ $r->name }}</p><p class="truncate text-xs font-normal text-mute">{{ $r->email }}</p></div>
                            </a>
                        </td>
                        <td data-label="Leads">{{ $r->leads_count }}</td>
                        <td data-label="Sales">{{ $r->closed_sales }}</td>
                        <td data-label="Team">{{ $r->downlines_count }}</td>
                        <td data-label="Earned" class="font-bold text-ink">₦{{ number_format((float) $r->earned) }}</td>
                        <td data-label="Status"><x-app.badge :status="$r->status" /></td>
                        <td data-label="Joined" class="whitespace-nowrap text-mute">{{ $r->created_at->format('M j, Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="users" title="No realtors found" text="Realtors who register on the website appear here. You can also add one yourself.">
                <a href="{{ route('admin.realtors.create') }}" class="btn btn-primary btn-sm">Add realtor</a>
            </x-app.empty>
        @endif
    </div>
    <div class="mt-6">{{ $realtors->links('pagination.premium') }}</div>
</x-layouts.admin>
