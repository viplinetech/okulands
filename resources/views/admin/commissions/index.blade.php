<x-layouts.admin title="Commissions">
    <x-app.page-header title="Commissions" kicker="Sales" subtitle="What is owed, what is paid, and the rates that drive it.">
        <a href="{{ route('admin.reports.export', ['type' => 'commissions']) }}" class="btn btn-outline btn-sm"><x-icon name="download" class="h-4 w-4" /> Export payout sheet</a>
    </x-app.page-header>

    <section class="grid grid-cols-2 gap-3 sm:gap-4">
        <x-app.stat icon="clock" label="Awaiting payout" :value="'₦'.number_format($totals['pending'])" :hero="true" />
        <x-app.stat icon="check-circle" label="Paid out" :value="'₦'.number_format($totals['paid'])" />
    </section>

    {{-- Rates --}}
    @include('admin.commissions._rates-form')

    <div class="tabs mb-4 mt-6">
        <a href="{{ route('admin.commissions.index') }}" class="tab {{ ! $status ? 'is-active' : '' }}">All</a>
        <a href="{{ route('admin.commissions.index', ['status' => 'pending']) }}" class="tab {{ $status === 'pending' ? 'is-active' : '' }}">Pending</a>
        <a href="{{ route('admin.commissions.index', ['status' => 'paid']) }}" class="tab {{ $status === 'paid' ? 'is-active' : '' }}">Paid</a>
    </div>

    <form method="GET" class="card mb-4 grid gap-3 !p-3 sm:!p-4 md:grid-cols-[1fr_auto]">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <label class="relative block"><span class="sr-only">Search realtor</span><x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-mute" /><input type="search" name="q" value="{{ $term }}" placeholder="Search by realtor name…" class="field !rounded-full !py-3 !pl-11"></label>
        <button type="submit" class="btn btn-primary btn-sm !py-3">Search</button>
    </form>

    <div class="card-flush">
        @if ($commissions->count())
            <table class="tbl">
                <thead><tr><th>Realtor</th><th>Property</th><th>Tier</th><th>Amount</th><th>Payout account</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                @foreach ($commissions as $c)
                    <tr>
                        <td class="cell-main">{{ $c->user?->name }}</td>
                        <td data-label="Property" class="max-w-[12rem]"><span class="line-clamp-2">{{ $c->sale?->property?->title }}</span></td>
                        <td data-label="Tier">{{ $c->tier }} · {{ rtrim(rtrim(number_format($c->rate, 2), '0'), '.') }}%</td>
                        <td data-label="Amount" class="font-bold text-ink">₦{{ number_format($c->amount) }}</td>
                        <td data-label="Payout account" class="text-xs">
                            @if ($c->user?->bank_name)<span class="font-bold text-ink">{{ $c->user->bank_name }}</span><br>{{ $c->user->account_name }} · {{ $c->user->account_number }}@else<span class="text-flag-500">No bank details</span>@endif
                        </td>
                        <td data-label="Status"><x-app.badge :status="$c->status" /></td>
                        <td class="cell-actions">
                            @if ($c->status === 'pending')
                                <form method="POST" action="{{ route('admin.commissions.pay', $c) }}" class="flex justify-end gap-2">@csrf
                                    <input name="payout_reference" placeholder="Ref" class="field !w-24 !rounded-full !py-2 !text-sm" aria-label="Payout reference">
                                    <button type="submit" class="btn btn-primary btn-sm">Pay</button>
                                </form>
                            @else
                                <span class="block text-right text-xs text-mute">{{ $c->paid_at?->format('M j, Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="wallet" title="No commissions here" text="They are created automatically when a sale is approved." />
        @endif
    </div>
    <div class="mt-6">{{ $commissions->links('pagination.premium') }}</div>
</x-layouts.admin>
