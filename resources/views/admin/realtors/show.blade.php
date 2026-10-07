<x-layouts.admin :title="$realtor->name">
    <x-app.page-header :title="$realtor->name" kicker="Realtor" :subtitle="$realtor->email">
        <a href="{{ route('admin.realtors.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow" class="h-4 w-4 rotate-180" /> Back</a>
    </x-app.page-header>

    @if (session('temp_password'))
        <section class="card mb-4 border-amber-500/40 !bg-amber-500/5">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-600"><x-icon name="lock" class="h-5 w-5" /></span>
                <div><h2 class="card-title">Temporary password</h2><p class="text-xs text-mute">Shown once. Share it securely (not by public message) and ask them to change it.</p></div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <code class="rounded-xl bg-card px-4 py-3 font-mono text-lg font-bold tracking-wider text-ink">{{ session('temp_password') }}</code>
                <button type="button" data-copy="{{ session('temp_password') }}" class="btn btn-outline btn-sm"><x-icon name="copy" class="h-4 w-4" /><span data-label>Copy</span></button>
            </div>
        </section>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        {{-- Profile card --}}
        <section class="card lg:col-span-1">
            <div class="flex items-center gap-4">
                <x-app.avatar :user="$realtor" size="h-16 w-16 text-xl" />
                <div class="min-w-0"><p class="truncate font-serif text-2xl text-ink">{{ $realtor->name }}</p><x-app.badge :status="$realtor->status" /></div>
            </div>
            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-mute">Phone</dt><dd class="font-bold text-ink">{{ $realtor->phone ?: '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-mute">Referral code</dt><dd class="font-mono font-bold text-ink">{{ $realtor->referral_code }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-mute">Sponsor</dt><dd class="font-bold text-ink">@if ($realtor->referrer)<a href="{{ route('admin.realtors.show', $realtor->referrer) }}" class="text-brand hover:underline">{{ $realtor->referrer->name }}</a>@else — @endif</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-mute">Joined</dt><dd class="font-bold text-ink">{{ $realtor->created_at->format('M j, Y') }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-mute">Last sign-in</dt><dd class="font-bold text-ink">{{ $realtor->last_login_at?->diffForHumans() ?? 'Never' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-mute">Two-factor</dt><dd><x-app.badge :status="$realtor->hasTwoFactorEnabled() ? 'on' : 'off'">{{ $realtor->hasTwoFactorEnabled() ? 'On' : 'Off' }}</x-app.badge></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-mute">Bank</dt><dd class="text-right font-bold text-ink">{{ $realtor->bank_name ? $realtor->bank_name.' · '.$realtor->maskedAccountNumber() : 'Not added' }}</dd></div>
            </dl>

            <div class="mt-6 space-y-2 border-t border-ink/10 pt-5">
                <form method="POST" action="{{ route('admin.realtors.status', $realtor) }}" data-confirm="{{ $realtor->status === 'active' ? 'Suspend this realtor? They are signed out immediately and cannot sign in.' : 'Reactivate this realtor?' }}" data-confirm-yes="{{ $realtor->status === 'active' ? 'Suspend' : 'Reactivate' }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $realtor->status === 'active' ? 'suspended' : 'active' }}">
                    @if ($realtor->status === 'active')<input name="reason" placeholder="Reason (optional)" class="field mb-2 !py-2.5 text-sm">@endif
                    <button type="submit" class="btn {{ $realtor->status === 'active' ? 'btn-danger' : 'btn-primary' }} btn-sm w-full">{{ $realtor->status === 'active' ? 'Suspend account' : 'Reactivate account' }}</button>
                </form>
                @if ($realtor->hasTwoFactorEnabled())
                    <form method="POST" action="{{ route('admin.realtors.two-factor', $realtor) }}" data-confirm="Reset two-factor for this realtor? Do this only after confirming their identity (for example, they lost their phone)." data-confirm-yes="Reset">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm w-full">Reset two-factor</button>
                    </form>
                @endif
            </div>
        </section>

        <div class="space-y-4 lg:col-span-2">
            <section class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <x-app.stat icon="link" label="Clicks" :value="number_format($summary['clicks'])" />
                <x-app.stat icon="inbox" label="Leads" :value="number_format($summary['leads'])" />
                <x-app.stat icon="tag" label="Sales" :value="number_format($summary['sales'])" />
                <x-app.stat icon="users" label="Team" :value="number_format($summary['team'])" />
                <x-app.stat icon="wallet" label="Earned" :value="'₦'.number_format($summary['earned'])" class="col-span-2" />
                <x-app.stat icon="clock" label="Pending" :value="'₦'.number_format($summary['pending'])" />
                <x-app.stat icon="check-circle" label="Paid" :value="'₦'.number_format($summary['paid'])" />
            </section>

            <form method="POST" action="{{ route('admin.realtors.update', $realtor) }}" class="card">
                @csrf @method('PUT')
                <h2 class="card-title">Edit details</h2>
                <div class="form-grid mt-5">
                    <div><label for="name" class="field-label">Name</label><input id="name" name="name" value="{{ old('name', $realtor->name) }}" required class="field">@error('name')<p class="err">{{ $message }}</p>@enderror</div>
                    <div><label for="phone" class="field-label">Phone</label><input id="phone" name="phone" value="{{ old('phone', $realtor->phone) }}" class="field">@error('phone')<p class="err">{{ $message }}</p>@enderror</div>
                    <div>
                        <label for="override" class="field-label">Personal commission rate (%)</label>
                        <input id="override" name="commission_rate_override" inputmode="decimal" value="{{ old('commission_rate_override', $realtor->commission_rate_override) }}" placeholder="Default" class="field">
                        <p class="hint">Leave empty to use the standard tier-1 rate.</p>
                        @error('commission_rate_override')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="referred_by" class="field-label">Sponsor</label>
                        <select id="referred_by" name="referred_by" class="field"><option value="">No sponsor</option>@foreach ($sponsors as $s)<option value="{{ $s->id }}" @selected(old('referred_by', $realtor->referred_by) == $s->id)>{{ $s->name }}</option>@endforeach</select>
                        @error('referred_by')<p class="err">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-5 flex justify-end"><button type="submit" class="btn btn-primary btn-sm">Save details</button></div>
            </form>
        </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="card-flush">
            <div class="px-4 pt-4 sm:px-6 sm:pt-6"><h2 class="card-title">Recent commissions</h2></div>
            @forelse ($commissions as $c)
                <div class="flex items-center gap-3 border-t border-ink/10 px-4 py-3.5 first:mt-3 sm:px-6">
                    <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold text-ink">₦{{ number_format($c->amount) }} · tier {{ $c->tier }}</p><p class="truncate text-xs text-mute">{{ $c->sale?->property?->title }}</p></div>
                    <x-app.badge :status="$c->status" />
                </div>
            @empty
                <x-app.empty icon="wallet" title="No commissions yet" />
            @endforelse
        </section>
        <section class="card-flush">
            <div class="px-4 pt-4 sm:px-6 sm:pt-6"><h2 class="card-title">Team</h2></div>
            @forelse ($team as $m)
                <a href="{{ route('admin.realtors.show', $m) }}" class="flex items-center gap-3 border-t border-ink/10 px-4 py-3.5 transition first:mt-3 hover:bg-soft/60 sm:px-6">
                    <x-app.avatar :user="$m" />
                    <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold text-ink">{{ $m->name }}</p><p class="truncate text-xs text-mute">{{ $m->downlines_count }} recruits · joined {{ $m->created_at->format('M j, Y') }}</p></div>
                    <x-app.badge :status="$m->status" />
                </a>
            @empty
                <x-app.empty icon="users" title="No team members" />
            @endforelse
        </section>
    </div>

    <section class="card-flush mt-4">
        <div class="px-4 pt-4 sm:px-6 sm:pt-6"><h2 class="card-title">Recent activity</h2></div>
        @forelse ($activity as $log)
            <div class="flex items-start gap-3 border-t border-ink/10 px-4 py-3 first:mt-3 sm:px-6">
                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-brand"></span>
                <div class="min-w-0 flex-1"><p class="text-sm text-ink">{{ $log->description ?: $log->action }}</p><p class="text-xs text-mute">{{ $log->created_at->diffForHumans() }} · {{ $log->ip_address }}</p></div>
            </div>
        @empty
            <x-app.empty icon="activity" title="No activity recorded" />
        @endforelse
    </section>
</x-layouts.admin>
