<x-layouts.realtor title="Team">
    <x-app.page-header title="Your team" kicker="Downline" subtitle="Realtors who joined through your link. You earn a share of their sales too.">
        <a href="{{ route('realtor.share') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" /> Invite a realtor</a>
    </x-app.page-header>

    <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-app.stat icon="users" label="Direct recruits" :value="number_format($members->total())" />
        <x-app.stat icon="trend" label="Team levels" :value="count($levels) ?: 0" :hint="collect($levels)->sum().' people in total'" />
        <x-app.stat icon="wallet" label="Earned from team" :value="'₦'.number_format($teamEarned)" class="col-span-2 lg:col-span-2" :hero="true" />
    </section>

    @if (count($levels) > 1)
        <section class="card mt-4">
            <h2 class="card-title">Depth of your network</h2>
            <div class="mt-4 space-y-3">
                @php $top = max($levels); @endphp
                @foreach ($levels as $level => $count)
                    <div class="flex items-center gap-3">
                        <span class="w-16 shrink-0 text-xs font-bold uppercase tracking-wide text-mute">Level {{ $level }}</span>
                        <div class="meter flex-1"><span style="width: {{ round($count / $top * 100) }}%"></span></div>
                        <span class="w-8 shrink-0 text-right text-sm font-bold text-ink">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($sponsor)
        <div class="mt-4 flex items-center gap-3 rounded-2xl border border-brand/25 bg-brand/10 px-4 py-3 text-sm text-ink">
            <x-icon name="users" class="h-5 w-5 shrink-0 text-brand" />
            <span>You joined through <strong>{{ $sponsor->name }}</strong>.</span>
        </div>
    @endif

    <div class="card-flush mt-4">
        @if ($members->count())
            <table class="tbl">
                <thead><tr><th>Member</th><th>Joined</th><th>Their line&rsquo;s sales</th><th>You earned from their line</th><th>Their recruits</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($members as $m)
                    <tr>
                        <td class="cell-main">
                            <div class="flex items-center gap-3 text-left">
                                <x-app.avatar :user="$m" />
                                <div class="min-w-0"><p class="truncate font-bold text-ink">{{ $m->name }}</p><p class="truncate text-xs font-normal text-mute">{{ $m->email }}</p></div>
                            </div>
                        </td>
                        <td data-label="Joined" class="whitespace-nowrap text-mute">{{ $m->created_at->format('M j, Y') }}</td>
                        <td data-label="Their line's sales" class="font-bold">{{ $teamSales[$m->id] ?? 0 }}</td>
                        <td data-label="You earned" class="font-bold text-brand">₦{{ number_format($earned[$m->id] ?? 0) }}</td>
                        <td data-label="Their recruits">{{ $m->downlines_count }}</td>
                        <td data-label="Status"><x-app.badge :status="$m->status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <x-app.empty icon="users" title="No one in your team yet" text="Send your invite link to people who could sell for Oku Lands. When they register through it, they join your downline automatically.">
                <a href="{{ route('realtor.share') }}" class="btn btn-primary btn-sm">Get invite link</a>
            </x-app.empty>
        @endif
    </div>
    <div class="mt-6">{{ $members->links('pagination.premium') }}</div>
</x-layouts.realtor>
