<x-layouts.realtor title="Earnings">
    <x-app.page-header title="Earnings" kicker="Commissions" subtitle="Every naira you have earned and exactly when it is paid." />

    @unless ($hasBank)
        <a href="{{ route('realtor.profile') }}#payout" class="mb-5 flex items-center gap-3 rounded-2xl border border-amber-500/30 bg-amber-500/10 px-4 py-3.5 text-sm font-semibold text-ink transition active:scale-[0.99]">
            <x-icon name="bank" class="h-5 w-5 shrink-0 text-amber-600" />
            <span class="flex-1">Add your bank details so we can pay you when commission is due.</span>
            <x-icon name="arrow" class="h-4 w-4 shrink-0" />
        </a>
    @endunless

    <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-app.stat icon="wallet" label="Total earned" :value="'₦'.number_format($summary['earned'])" :hero="true" class="col-span-2 lg:col-span-1" />
        <x-app.stat icon="clock" label="Pending" :value="'₦'.number_format($summary['pending'])" hint="Awaiting payout" />
        <x-app.stat icon="check-circle" label="Paid out" :value="'₦'.number_format($summary['paid'])" />
    </section>

    {{-- Withdraw --}}
    <section class="card mt-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="card-title">Available to withdraw</h2>
                <p class="mt-1 text-3xl font-bold text-ink">₦{{ number_format($available) }}</p>
            </div>
            @if ($hasBank && $available > 0)
                <form method="POST" action="{{ route('realtor.withdrawals.store') }}" class="flex items-end gap-2">
                    @csrf
                    <div>
                        <label for="amount" class="field-label">Amount (₦)</label>
                        <input id="amount" name="amount" inputmode="decimal" required max="{{ (int) $available }}" value="{{ old('amount', (int) $available) }}" class="field !w-40">
                    </div>
                    <button type="submit" class="btn btn-primary">Withdraw <x-icon name="arrow" class="h-4 w-4" /></button>
                </form>
            @elseif (! $hasBank)
                <a href="{{ route('realtor.profile') }}#payout" class="btn btn-outline btn-sm">Add bank details to withdraw</a>
            @endif
        </div>
        @error('amount')<p class="err mt-2">{{ $message }}</p>@enderror

        @if ($withdrawals->count())
            <div class="mt-5 space-y-2 border-t border-ink/10 pt-4">
                @foreach ($withdrawals as $w)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-mute">{{ $w->created_at->format('M j, Y') }}</span>
                        <span class="font-bold text-ink">₦{{ number_format($w->amount) }}</span>
                        <x-app.badge :status="$w->status" />
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="card mt-4">
        <h2 class="card-title">Monthly earnings</h2>
        <x-app.bars :data="$months" :money="true" class="mt-5" />
    </section>

    <div class="tabs mb-4 mt-6">
        <a href="{{ route('realtor.earnings') }}" class="tab {{ ! $status ? 'is-active' : '' }}">All</a>
        <a href="{{ route('realtor.earnings', ['status' => 'pending']) }}" class="tab {{ $status === 'pending' ? 'is-active' : '' }}">Pending</a>
        <a href="{{ route('realtor.earnings', ['status' => 'paid']) }}" class="tab {{ $status === 'paid' ? 'is-active' : '' }}">Paid</a>
    </div>

    <div class="card-flush">
        @if ($commissions->count())
            <table class="tbl">
                <thead><tr><th>Property</th><th>Type</th><th>Rate</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                @foreach ($commissions as $c)
                    <tr>
                        <td class="cell-main">{{ $c->sale?->property?->title ?? 'Sale #'.$c->sale_id }}</td>
                        <td data-label="Type">{{ $c->tier === 1 ? 'Your sale' : 'Team · tier '.$c->tier }}</td>
                        <td data-label="Rate">{{ rtrim(rtrim(number_format($c->rate, 2), '0'), '.') }}%</td>
                        <td data-label="Amount" class="font-bold text-ink">₦{{ number_format($c->amount) }}</td>
                        <td data-label="Status"><x-app.badge :status="$c->status" /></td>
                        <td data-label="Date" class="whitespace-nowrap text-mute">{{ ($c->paid_at ?? $c->created_at)->format('M j, Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="wallet" title="No commissions here yet" text="When a sale from your link is approved, your commission appears here." />
        @endif
    </div>
    <div class="mt-6">{{ $commissions->links('pagination.premium') }}</div>
</x-layouts.realtor>
