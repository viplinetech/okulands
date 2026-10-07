<x-layouts.admin title="Withdrawals">
    <x-app.page-header title="Withdrawals" kicker="Sales" subtitle="Realtor requests to withdraw their earned commission balance." />

    <section class="grid grid-cols-2 gap-3 sm:gap-4">
        <x-app.stat icon="clock" label="Awaiting payout" :value="'₦'.number_format($totals['pending'])" :hero="true" />
        <x-app.stat icon="check-circle" label="Paid out" :value="'₦'.number_format($totals['paid'])" />
    </section>

    <div class="tabs mb-4 mt-6">
        <a href="{{ route('admin.withdrawals.index') }}" class="tab {{ ! $status ? 'is-active' : '' }}">All</a>
        <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}" class="tab {{ $status === 'pending' ? 'is-active' : '' }}">Pending</a>
        <a href="{{ route('admin.withdrawals.index', ['status' => 'paid']) }}" class="tab {{ $status === 'paid' ? 'is-active' : '' }}">Paid</a>
        <a href="{{ route('admin.withdrawals.index', ['status' => 'rejected']) }}" class="tab {{ $status === 'rejected' ? 'is-active' : '' }}">Declined</a>
    </div>

    <div class="card-flush">
        @if ($withdrawals->count())
            <table class="tbl">
                <thead><tr><th>Realtor</th><th>Amount</th><th>Payout account</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                @foreach ($withdrawals as $w)
                    <tr>
                        <td class="cell-main">{{ $w->user?->name }}</td>
                        <td data-label="Amount" class="font-bold text-ink">₦{{ number_format($w->amount) }}</td>
                        <td data-label="Payout account" class="text-xs">
                            @if ($w->bank_name)<span class="font-bold text-ink">{{ $w->bank_name }}</span><br>{{ $w->account_name }} · {{ $w->maskedAccountNumber() }}@else<span class="text-flag-500">No bank details</span>@endif
                        </td>
                        <td data-label="Status">
                            <x-app.badge :status="$w->status" />
                            @if ($w->status === 'rejected' && $w->notes)<p class="mt-1 max-w-[16rem] text-xs text-mute">{{ $w->notes }}</p>@endif
                        </td>
                        <td class="cell-actions">
                            @if ($w->status === 'pending')
                                <div class="flex justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.withdrawals.pay', $w) }}" class="flex gap-2">@csrf
                                        <input name="reference" placeholder="Ref" class="field !w-24 !rounded-full !py-2 !text-sm" aria-label="Payout reference">
                                        <button type="submit" class="btn btn-primary btn-sm">Pay</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.withdrawals.reject', $w) }}" data-confirm="Decline this withdrawal request? The realtor will be notified and the amount stays in their balance." data-confirm-yes="Decline">@csrf
                                        <button type="submit" class="btn btn-outline btn-sm">Decline</button>
                                    </form>
                                </div>
                            @else
                                <span class="block text-right text-xs text-mute">{{ $w->decided_at?->format('M j, Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="wallet" title="No withdrawal requests" text="They appear here when a realtor requests a payout of their earned balance." />
        @endif
    </div>
    <div class="mt-6">{{ $withdrawals->links('pagination.premium') }}</div>
</x-layouts.admin>
