<x-layouts.admin title="Reports">
    <x-app.page-header title="Reports" kicker="Overview" subtitle="Performance for the period you choose. Download any table as an Excel file.">
        <form method="GET" class="flex items-center gap-2">
            <label class="sr-only" for="period">Period</label>
            <select id="period" name="period" onchange="this.form.submit()" class="field !rounded-full !py-2.5 !pr-9 text-sm">
                @foreach ($periods as $key => $label)<option value="{{ $key }}" @selected($period === (string) $key)>{{ $label }}</option>@endforeach
            </select>
        </form>
    </x-app.page-header>

    <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-app.stat icon="tag" label="Revenue" :value="'₦'.number_format($totals['revenue'])" :hint="$totals['sales'].' sales'" :hero="true" class="col-span-2" />
        <x-app.stat icon="inbox" label="Leads" :value="number_format($totals['leads'])" />
        <x-app.stat icon="wallet" label="Commission" :value="'₦'.number_format($totals['commission'])" :hint="'₦'.number_format($totals['paid']).' paid'" />
    </section>

    <section class="card mt-4">
        <h2 class="card-title">Revenue by month</h2>
        @if (count($months))
            <x-app.bars :data="$months" :money="true" class="mt-5" />
        @else
            <x-app.empty icon="chart" title="No sales in this period" />
        @endif
    </section>

    <section class="card-flush mt-4">
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 pt-4 sm:px-6 sm:pt-6">
            <h2 class="card-title">Realtor performance</h2>
            <a href="{{ route('admin.reports.export', ['type' => 'realtors']) }}" class="btn btn-outline btn-sm"><x-icon name="download" class="h-4 w-4" /> Excel</a>
        </div>
        @if ($performance->count())
            <table class="tbl mt-3">
                <thead><tr><th>Realtor</th><th>Clicks</th><th>Leads</th><th>Sales</th><th>Revenue</th><th>Commission</th></tr></thead>
                <tbody>
                @foreach ($performance as $r)
                    <tr>
                        <td class="cell-main"><a href="{{ route('admin.realtors.show', $r) }}" class="flex items-center gap-3 text-left"><x-app.avatar :user="$r" /><span class="truncate font-bold text-ink">{{ $r->name }}</span></a></td>
                        <td data-label="Clicks">{{ $r->clicks }}</td>
                        <td data-label="Leads">{{ $r->leads }}</td>
                        <td data-label="Sales">{{ $r->closed }}</td>
                        <td data-label="Revenue" class="font-bold text-ink">₦{{ number_format((float) $r->revenue) }}</td>
                        <td data-label="Commission">₦{{ number_format((float) $r->earned) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="users" title="No realtors yet" />
        @endif
    </section>

    <section class="card mt-4">
        <h2 class="card-title">Download data</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (['sales' => ['tag', 'Sales'], 'commissions' => ['wallet', 'Commissions & payout sheet'], 'leads' => ['inbox', 'Leads'], 'realtors' => ['users', 'Realtors']] as $type => [$icon, $label])
                <a href="{{ route('admin.reports.export', ['type' => $type, 'period' => $period]) }}" class="flex items-center gap-3 rounded-2xl border border-ink/10 bg-page p-4 text-sm font-bold text-ink transition hover:border-brand hover:text-brand">
                    <span class="stat-icon !h-10 !w-10"><x-icon :name="$icon" class="h-[1.15rem] w-[1.15rem]" /></span>
                    <span class="flex-1">{{ $label }}</span><x-icon name="download" class="h-4 w-4" />
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.admin>
