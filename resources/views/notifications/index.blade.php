@php $layout = $isAdmin ? 'layouts.admin' : 'layouts.realtor'; @endphp
<x-dynamic-component :component="$layout" title="Alerts">
    <x-app.page-header :title="$isAdmin ? 'Notifications' : 'Alerts'" kicker="Activity" subtitle="New leads, commissions and team activity, all in one place." />

    <div class="card-flush">
        @forelse ($notifications as $n)
            @php $d = $n->data; $new = in_array($n->id, $unreadIds, true); @endphp
            <a href="{{ $d['url'] ?? '#' }}" class="flex items-start gap-3.5 border-t border-ink/10 px-4 py-4 transition first:border-t-0 hover:bg-soft/60 sm:px-6 {{ $new ? 'bg-brand/[0.05]' : '' }}">
                <span class="stat-icon !h-11 !w-11 shrink-0"><x-icon :name="$d['icon'] ?? 'bell'" class="h-5 w-5" /></span>
                <div class="min-w-0 flex-1">
                    <p class="flex items-center gap-2 text-sm font-bold text-ink">{{ $d['title'] ?? 'Notification' }} @if ($new)<span class="badge badge-blue !py-0.5">New</span>@endif</p>
                    <p class="mt-0.5 text-sm text-mute">{{ $d['body'] ?? '' }}</p>
                    <p class="mt-1 text-xs text-mute/80">{{ $n->created_at->diffForHumans() }}</p>
                </div>
            </a>
        @empty
            <x-app.empty icon="bell" title="You’re all caught up" text="Alerts about leads, commissions and your team will appear here." />
        @endforelse
    </div>
    <div class="mt-6">{{ $notifications->links('pagination.premium') }}</div>
</x-dynamic-component>
