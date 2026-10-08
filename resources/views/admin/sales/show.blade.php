<x-layouts.admin title="Sale">
    <x-app.page-header :title="$sale->buyer_name" kicker="Sale" :subtitle="$sale->property?->title">
        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow" class="h-4 w-4 rotate-180" /> Back</a>
        @if ($sale->status === 'pending')
            <form method="POST" action="{{ route('admin.sales.approve', $sale) }}" data-confirm="Approve this sale? The property will be marked sold and commissions created for the realtor and their upline." data-confirm-yes="Approve sale">@csrf
                <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" class="h-4 w-4" /> Approve</button>
            </form>
        @endif
        @if (in_array($sale->status, ['pending', 'approved'], true))
            <form method="POST" action="{{ route('admin.sales.cancel', $sale) }}" data-confirm="Cancel this sale? Unpaid commissions are removed and the property returns to the market." data-confirm-yes="Cancel sale">@csrf
                <button type="submit" class="btn btn-outline btn-sm">Cancel sale</button>
            </form>
        @endif
    </x-app.page-header>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="stat stat-hero !p-5 sm:!p-7 lg:col-span-1">
            <p class="stat-label !mt-0">Sale amount</p>
            <p class="mt-2 font-sans text-5xl font-extrabold leading-none tracking-tight text-white [font-variant-numeric:tabular-nums]">₦{{ number_format($sale->amount) }}</p>
            <div class="mt-4"><x-app.badge :status="$sale->status" class="!bg-white/15 !text-white" /></div>
            @if ($sale->approved_at)<p class="mt-4 text-xs text-white/60">Approved {{ $sale->approved_at->format('M j, Y') }} by {{ $sale->approver?->name ?? 'admin' }}</p>@endif
        </section>

        <section class="card lg:col-span-2">
            <h2 class="card-title">Details</h2>
            <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="field-label !mb-1">Property</dt><dd class="font-bold text-ink">{{ $sale->property?->title }}</dd></div>
                @if ($sale->property?->isMultiUnit())
                    <div><dt class="field-label !mb-1">Quantity sold</dt><dd class="font-bold text-ink">{{ $sale->quantity }} unit(s) <span class="font-normal text-mute">· {{ $sale->property->unitsRemaining() }} of {{ $sale->property->units_total }} remaining overall</span></dd></div>
                @endif
                <div><dt class="field-label !mb-1">Realtor credited</dt><dd class="font-bold text-ink"><a href="{{ route('admin.realtors.show', $sale->realtor) }}" class="text-brand hover:underline">{{ $sale->realtor?->name }}</a></dd></div>
                <div><dt class="field-label !mb-1">Buyer</dt><dd class="font-bold text-ink">{{ $sale->buyer_name }}<br><span class="font-normal text-mute">{{ $sale->buyer_phone }} {{ $sale->buyer_email }}</span></dd></div>
                <div><dt class="field-label !mb-1">Reference</dt><dd class="font-bold text-ink">{{ $sale->reference ?: '—' }}</dd></div>
                @if ($sale->lead)<div><dt class="field-label !mb-1">From lead</dt><dd class="font-bold text-ink"><a href="{{ route('admin.leads.show', $sale->lead) }}" class="text-brand hover:underline">{{ $sale->lead->name }}</a></dd></div>@endif
                @if ($sale->notes)<div class="sm:col-span-2"><dt class="field-label !mb-1">Notes</dt><dd class="text-mute">{{ $sale->notes }}</dd></div>@endif
            </dl>
        </section>
    </div>

    <section class="card-flush mt-4">
        <div class="px-4 pt-4 sm:px-6 sm:pt-6"><h2 class="card-title">Commissions</h2></div>
        @if ($sale->commissions->count())
            <table class="tbl mt-3">
                <thead><tr><th>Realtor</th><th>Tier</th><th>Rate</th><th>Amount</th><th>Status</th><th class="text-right">Payout</th></tr></thead>
                <tbody>
                @foreach ($sale->commissions as $c)
                    <tr>
                        <td class="cell-main">{{ $c->user?->name }}</td>
                        <td data-label="Tier">{{ $c->tier === 1 ? 'Closed the sale' : 'Team · tier '.$c->tier }}</td>
                        <td data-label="Rate">{{ rtrim(rtrim(number_format($c->rate, 2), '0'), '.') }}%</td>
                        <td data-label="Amount" class="font-bold text-ink">₦{{ number_format($c->amount) }}</td>
                        <td data-label="Status"><x-app.badge :status="$c->status" /></td>
                        <td class="cell-actions">
                            @if ($c->status === 'pending')
                                <form method="POST" action="{{ route('admin.commissions.pay', $c) }}" class="flex justify-end gap-2">@csrf
                                    <input name="payout_reference" placeholder="Transfer ref (optional)" class="field !w-44 !rounded-full !py-2 !text-sm" aria-label="Payout reference">
                                    <button type="submit" class="btn btn-primary btn-sm">Mark paid</button>
                                </form>
                            @else
                                <span class="text-xs text-mute">{{ $c->paid_at?->format('M j, Y') }} {{ $c->payout_reference ? '· '.$c->payout_reference : '' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="wallet" title="No commissions yet" text="Commissions are created when this sale is approved." />
        @endif
    </section>
</x-layouts.admin>
