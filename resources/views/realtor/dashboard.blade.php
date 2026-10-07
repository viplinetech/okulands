<x-layouts.realtor title="Overview">
    @php
        $user = auth()->user();
        $done = collect($checklist)->where('done', true)->count();
        $todo = collect($checklist)->count();
    @endphp

    <x-app.page-header :title="'Welcome back, '.$user->firstName()" kicker="Realtor dashboard" subtitle="Here is how your referrals are performing." />

    {{-- Earnings hero + referral link --}}
    <section class="stat stat-hero !p-5 sm:!p-7">
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div>
                <p class="stat-label !mt-0">Total earned</p>
                <p class="mt-2 font-serif text-[2.6rem] leading-none text-white sm:text-6xl">₦<span data-counter="{{ (int) $summary['earned'] }}">{{ number_format($summary['earned']) }}</span></p>
                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-sm">
                    <p class="text-white/60">Pending <strong class="ml-1 text-amber-300">₦{{ number_format($summary['pending']) }}</strong></p>
                    <p class="text-white/60">Paid out <strong class="ml-1 text-emerald-300">₦{{ number_format($summary['paid']) }}</strong></p>
                </div>
            </div>
            <a href="{{ route('realtor.earnings') }}" class="btn btn-ghost btn-sm">Earnings <x-icon name="arrow" class="arrow h-4 w-4" /></a>
        </div>

        <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.06] p-3.5 backdrop-blur">
            <p class="text-[0.62rem] font-bold uppercase tracking-[0.2em] text-white/50">Your referral link</p>
            <p class="mt-1 truncate font-mono text-[0.8rem] text-white/90">{{ preg_replace('#^https?://#', '', $user->referralLink()) }}</p>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <button type="button" data-copy="{{ $user->referralLink() }}" class="btn btn-sky btn-sm"><x-icon name="copy" class="h-4 w-4" /><span data-label>Copy link</span></button>
                <button type="button" data-share="{{ $user->referralLink() }}" data-title="Oku Lands & Properties" data-text="Verified land and property across Nigeria." class="btn btn-ghost btn-sm"><x-icon name="send" class="h-4 w-4" /><span data-label>Share</span></button>
            </div>
        </div>
    </section>

    {{-- KPI tiles --}}
    <section class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-app.stat icon="link" label="Link clicks" :value="number_format($summary['clicks'])" :hint="$summary['joined'].' joined'" />
        <x-app.stat icon="inbox" label="Inspections booked" :value="number_format($summary['inspections'])" :hint="$summary['new_inspections'] ? $summary['new_inspections'].' new' : 'None waiting'" />
        <x-app.stat icon="tag" label="Sales closed" :value="number_format($summary['sales'])" />
        <x-app.stat icon="users" label="Your team" :value="number_format($summary['team'])" hint="Direct recruits" />
    </section>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        {{-- Chart --}}
        <section class="card lg:col-span-2">
            <div class="flex items-center justify-between gap-3">
                <h2 class="card-title">Earnings</h2>
                <span class="text-xs font-semibold text-mute">Last 6 months</span>
            </div>
            <x-app.bars :data="$months" :money="true" class="mt-5" />
        </section>

        {{-- Setup checklist --}}
        @if ($done < $todo)
            <section class="card">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="card-title">Get set up</h2>
                    <span class="badge badge-blue">{{ $done }}/{{ $todo }}</span>
                </div>
                <div class="meter mt-4"><span style="width: {{ round($done / max(1, $todo) * 100) }}%"></span></div>
                <ul class="mt-4 space-y-2">
                    @foreach ($checklist as $step)
                        <li>
                            <a href="{{ route($step['route']).(isset($step['anchor']) ? '#'.$step['anchor'] : '') }}" class="flex items-center gap-3 rounded-2xl px-2 py-2 text-sm transition hover:bg-soft {{ $step['done'] ? 'text-mute' : 'font-semibold text-ink' }}">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $step['done'] ? 'bg-emerald-500/15 text-emerald-600' : 'border border-ink/20 text-transparent' }}"><x-icon name="check" class="h-3.5 w-3.5" /></span>
                                <span class="{{ $step['done'] ? 'line-through decoration-ink/30' : '' }}">{{ $step['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @else
            <section class="card flex flex-col items-center justify-center text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600"><x-icon name="check-circle" class="h-7 w-7" /></span>
                <h2 class="card-title mt-4">You&rsquo;re all set</h2>
                <p class="mt-2 text-sm text-mute">Your profile, payouts and security are complete.</p>
            </section>
        @endif
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        {{-- Recent bookings (anonymous: the client's details stay with Oku Lands) --}}
        <section class="card-flush">
            <div class="flex items-center justify-between gap-3 px-4 pt-4 sm:px-6 sm:pt-6">
                <h2 class="card-title">Recent bookings</h2>
                <a href="{{ route('realtor.leads') }}" class="text-sm font-bold text-brand">View all</a>
            </div>
            @forelse ($recentLeads as $lead)
                <div class="flex items-center gap-3 border-t border-ink/10 px-4 py-3.5 first:mt-3 sm:px-6">
                    <span class="stat-icon !h-10 !w-10"><x-icon :name="$lead->type === 'inspection' ? 'key' : 'mail'" class="h-4 w-4" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-ink">{{ $lead->type === 'inspection' ? 'Inspection booked' : 'New enquiry' }}</p>
                        <p class="truncate text-xs text-mute">{{ $lead->property?->title ?? 'General' }} · {{ $lead->created_at->diffForHumans() }}</p>
                    </div>
                    <x-app.badge :status="$lead->status" />
                </div>
            @empty
                <x-app.empty icon="inbox" title="No bookings yet" text="When someone who came through your link books an inspection, it appears here instantly.">
                    <a href="{{ route('realtor.share') }}" class="btn btn-primary btn-sm">Share your link</a>
                </x-app.empty>
            @endforelse
        </section>

        {{-- Recent commissions --}}
        <section class="card-flush">
            <div class="flex items-center justify-between gap-3 px-4 pt-4 sm:px-6 sm:pt-6">
                <h2 class="card-title">Latest commissions</h2>
                <a href="{{ route('realtor.earnings') }}" class="text-sm font-bold text-brand">View all</a>
            </div>
            @forelse ($recentCommissions as $c)
                <div class="flex items-center gap-3 border-t border-ink/10 px-4 py-3.5 first:mt-3 sm:px-6">
                    <span class="stat-icon !h-10 !w-10"><x-icon name="wallet" class="h-[1.15rem] w-[1.15rem]" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-ink">₦{{ number_format($c->amount) }}</p>
                        <p class="truncate text-xs text-mute">{{ $c->sale?->property?->title ?? 'Sale' }} · Tier {{ $c->tier }}</p>
                    </div>
                    <x-app.badge :status="$c->status" />
                </div>
            @empty
                <x-app.empty icon="wallet" title="No commissions yet" text="Commission is added here as soon as a sale from your link is approved." />
            @endforelse
        </section>
    </div>
</x-layouts.realtor>
