<x-layouts.admin title="Dashboard">
    <x-app.page-header :title="'Good '.(now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening')).', '.auth()->user()->firstName()" kicker="Dashboard" subtitle="Here is what needs you today." />

    {{-- 1. What needs attention --}}
    <section class="mb-6">
        @php $waiting = collect($todo)->filter(fn ($t) => $t['count'] > 0); @endphp
        @if ($waiting->isNotEmpty())
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($todo as $item)
                    @if ($item['count'] > 0)
                        <a href="{{ route($item['route'], $item['query']) }}" class="card group flex items-center gap-4 !p-5 transition hover:-translate-y-0.5 hover:shadow-soft">
                            <span class="stat-icon"><x-icon :name="$item['icon']" class="h-5 w-5" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-sans text-4xl font-extrabold leading-none tracking-tight text-ink [font-variant-numeric:tabular-nums]">{{ number_format($item['count']) }}</span>
                                <span class="mt-1 block text-sm font-semibold text-mute">{{ $item['label'] }}</span>
                            </span>
                            <x-icon name="arrow" class="h-4 w-4 shrink-0 text-mute transition group-hover:translate-x-1" />
                        </a>
                    @endif
                @endforeach
            </div>
        @else
            <div class="card flex items-center gap-4 !p-6">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600"><x-icon name="check-circle" class="h-6 w-6" /></span>
                <div>
                    <p class="font-serif text-2xl text-ink">You are all caught up.</p>
                    <p class="text-sm text-mute">No enquiries, sales, commissions or reviews are waiting for you.</p>
                </div>
            </div>
        @endif
    </section>

    {{-- 2. Quick actions --}}
    <section class="mb-6">
        <p class="eyebrow mb-3 text-mute">Quick actions</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.leads.index') }}" class="btn btn-outline btn-sm"><x-icon name="inbox" class="h-4 w-4" /> View enquiries</a>
            <a href="{{ route('admin.properties.create') }}" class="btn btn-outline btn-sm"><x-icon name="plus" class="h-4 w-4" /> Add a property</a>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-outline btn-sm"><x-icon name="plus" class="h-4 w-4" /> Write an article</a>
            <a href="{{ route('admin.sales.create') }}" class="btn btn-outline btn-sm"><x-icon name="tag" class="h-4 w-4" /> Record a sale</a>
        </div>
    </section>

    {{-- 3. This month, at a glance --}}
    <section class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-app.stat icon="tag" label="Sales this month" :value="'₦'.number_format($month['sales'])" />
        <x-app.stat icon="inbox" label="Enquiries this month" :value="number_format($month['enquiries'])" />
        <x-app.stat icon="users" label="Active realtors" :value="number_format($month['realtors'])" />
    </section>

    {{-- 4. Latest enquiries --}}
    <section class="card-flush">
        <div class="flex items-center justify-between gap-3 px-4 pt-4 sm:px-6 sm:pt-6">
            <h2 class="card-title">Latest enquiries</h2>
            <a href="{{ route('admin.leads.index') }}" class="text-sm font-bold text-brand">See all</a>
        </div>
        @forelse ($latestLeads as $lead)
            <a href="{{ route('admin.leads.show', $lead) }}" class="flex items-center gap-3 border-t border-ink/10 px-4 py-3.5 transition hover:bg-soft sm:px-6">
                <span class="avatar h-10 w-10 text-sm">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($lead->name, 0, 1)) }}</span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-bold text-ink">{{ $lead->name }}</span>
                    <span class="block truncate text-xs text-mute">{{ $lead->property?->title ?? ucfirst($lead->type) }} · {{ $lead->created_at->diffForHumans() }}</span>
                </span>
                <x-app.badge :status="$lead->status" />
            </a>
        @empty
            <x-app.empty icon="inbox" title="No enquiries yet" text="Enquiries from the website appear here." />
        @endforelse
    </section>
</x-layouts.admin>
