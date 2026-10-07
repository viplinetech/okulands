<x-layouts.admin title="Sales">
    <x-app.page-header title="Sales" kicker="Sales" subtitle="Record a sale, approve it, and commissions are calculated automatically.">
        <a href="{{ route('admin.reports.export', ['type' => 'sales']) }}" class="btn btn-outline btn-sm"><x-icon name="download" class="h-4 w-4" /> Export</a>
        <a href="{{ route('admin.sales.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" /> Record sale</a>
    </x-app.page-header>

    <div class="tabs mb-4">
        <a href="{{ route('admin.sales.index') }}" class="tab {{ ! $status ? 'is-active' : '' }}">All <span class="opacity-60">{{ $counts->sum() }}</span></a>
        @foreach ($statuses as $s)
            <a href="{{ route('admin.sales.index', ['status' => $s]) }}" class="tab {{ $status === $s ? 'is-active' : '' }}">{{ ucfirst($s) }} <span class="opacity-60">{{ $counts[$s] ?? 0 }}</span></a>
        @endforeach
    </div>

    <form method="GET" class="card mb-4 grid gap-3 !p-3 sm:!p-4 md:grid-cols-[1fr_auto]">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <label class="relative block">
            <span class="sr-only">Search</span>
            <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-mute" />
            <input type="search" name="q" value="{{ $term }}" placeholder="Search buyer or reference…" class="field !rounded-full !py-3 !pl-11">
        </label>
        <button type="submit" class="btn btn-primary btn-sm !py-3">Search</button>
    </form>

    <div class="card-flush">
        @if ($sales->count())
            <table class="tbl">
                <thead><tr><th>Buyer</th><th>Property</th><th>Realtor</th><th>Amount</th><th>Status</th><th>Date</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                @foreach ($sales as $sale)
                    <tr>
                        <td class="cell-main"><a href="{{ route('admin.sales.show', $sale) }}" class="block text-left"><p class="truncate font-bold text-ink">{{ $sale->buyer_name }}</p><p class="truncate text-xs font-normal text-mute">{{ $sale->reference ?: 'No reference' }}</p></a></td>
                        <td data-label="Property" class="max-w-[14rem]"><span class="line-clamp-2">{{ $sale->property?->title }}</span></td>
                        <td data-label="Realtor">{{ $sale->realtor?->name }}</td>
                        <td data-label="Amount" class="font-bold text-ink">₦{{ number_format($sale->amount) }}</td>
                        <td data-label="Status"><x-app.badge :status="$sale->status" /></td>
                        <td data-label="Date" class="whitespace-nowrap text-mute">{{ $sale->created_at->format('M j, Y') }}</td>
                        <td class="cell-actions"><div class="flex justify-end"><a href="{{ route('admin.sales.show', $sale) }}" class="icon-action" aria-label="Open"><x-icon name="eye" class="h-4 w-4" /></a></div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="tag" title="No sales yet" text="Record a sale when a buyer completes payment. Approving it creates commissions for the realtor and their upline.">
                <a href="{{ route('admin.sales.create') }}" class="btn btn-primary btn-sm">Record sale</a>
            </x-app.empty>
        @endif
    </div>
    <div class="mt-6">{{ $sales->links('pagination.premium') }}</div>
</x-layouts.admin>
