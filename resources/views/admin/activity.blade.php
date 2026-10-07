<x-layouts.admin title="Activity log">
    <x-app.page-header title="Activity log" kicker="System" subtitle="A permanent record of sign-ins and important actions. Entries cannot be edited or removed." />

    <div class="tabs mb-4">
        <a href="{{ route('admin.activity') }}" class="tab {{ ! $group ? 'is-active' : '' }}">Everything</a>
        @foreach ($groups as $key => $label)
            <a href="{{ route('admin.activity', ['group' => $key]) }}" class="tab {{ $group === $key ? 'is-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <form method="GET" class="card mb-4 grid gap-3 !p-3 sm:!p-4 md:grid-cols-[1fr_auto]">
        @if ($group)<input type="hidden" name="group" value="{{ $group }}">@endif
        <label class="relative block"><span class="sr-only">Search</span><x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-mute" /><input type="search" name="q" value="{{ $term }}" placeholder="Search description, action or IP address…" class="field !rounded-full !py-3 !pl-11"></label>
        <button type="submit" class="btn btn-primary btn-sm !py-3">Search</button>
    </form>

    <div class="card-flush">
        @if ($logs->count())
            <table class="tbl">
                <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Details</th><th>IP address</th></tr></thead>
                <tbody>
                @foreach ($logs as $log)
                    @php $bad = str_contains($log->action, 'failed') || str_contains($log->action, 'lockout') || str_contains($log->action, 'suspended'); @endphp
                    <tr>
                        <td class="cell-main whitespace-nowrap"><span class="text-left">{{ $log->created_at->format('M j, g:i:sa') }}</span></td>
                        <td data-label="Who">{{ $log->user?->name ?? 'System / visitor' }}</td>
                        <td data-label="Action"><span class="badge {{ $bad ? 'badge-red' : 'badge-slate' }}">{{ $log->action }}</span></td>
                        <td data-label="Details" class="max-w-[22rem] text-mute">{{ $log->description }}</td>
                        <td data-label="IP" class="font-mono text-xs text-mute">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="activity" title="Nothing recorded yet" text="Sign-ins and admin actions will be listed here." />
        @endif
    </div>
    <div class="mt-6">{{ $logs->links('pagination.premium') }}</div>
</x-layouts.admin>
