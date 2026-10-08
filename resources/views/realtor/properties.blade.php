<x-layouts.realtor title="Listings">
    <x-app.page-header title="Listings" kicker="Share a property" subtitle="Every link below is tagged to you. Anyone who enquires about that property is credited to you." />

    <form method="GET" action="{{ route('realtor.properties') }}" class="card mb-5 grid gap-3 !p-3 sm:grid-cols-[1fr_auto_auto] sm:!p-4">
        <label class="relative block">
            <span class="sr-only">Search listings</span>
            <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-mute" />
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by title or location" class="field !rounded-full !py-3 !pl-11">
        </label>
        <select name="sector" class="field !rounded-full !py-3 sm:w-48" aria-label="Type">
            <option value="">All types</option>
            @foreach (\App\Models\Property::SECTORS as $key => $label)
                <option value="{{ $key }}" @selected(($filters['sector'] ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm !py-3">Search</button>
    </form>

    @if ($properties->count())
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($properties as $p)
                @php
                    $link = $user->referralLink('/properties/'.$p->slug);
                    $text = 'Check out '.$p->title.' ('.$p->location.') from Oku Lands: '.$link;
                @endphp
                <article class="card-flush flex flex-col">
                    <div class="relative aspect-[16/10] bg-soft">
                        <img src="{{ $p->coverUrl() }}" alt="" data-fit class="h-full w-full object-cover" loading="lazy">
                        <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-[0.62rem] font-bold uppercase tracking-[0.12em] text-navy-900">{{ $p->sectorLabel() }}</span>
                        @if ($p->featured)<span class="absolute right-3 top-3 rounded-full bg-brand px-3 py-1 text-[0.62rem] font-bold uppercase tracking-[0.12em] text-brand-fg">Featured</span>@endif
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <p class="font-serif text-xl leading-tight text-ink">{{ $p->title }}</p>
                        <p class="mt-1 flex items-center gap-1.5 text-xs text-mute"><x-icon name="pin" class="h-3.5 w-3.5 shrink-0 text-flag-500" /><span class="truncate">{{ $p->location }}</span></p>
                        <p class="mt-3 font-sans text-2xl font-extrabold tracking-tight text-ink [font-variant-numeric:tabular-nums]">₦{{ number_format($p->price) }}</p>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            <button type="button" data-copy="{{ $link }}" class="btn btn-outline btn-sm !px-2"><x-icon name="copy" class="h-4 w-4" /><span data-label>Copy</span></button>
                            <a href="https://wa.me/?text={{ rawurlencode($text) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm !px-2"><x-icon name="whatsapp" class="h-4 w-4 text-[#25D366]" />Share</a>
                            <a href="{{ route('properties.show', $p->slug) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm !px-2"><x-icon name="eye" class="h-4 w-4" />View</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $properties->links('pagination.premium') }}</div>
    @else
        <div class="card-flush"><x-app.empty icon="building" title="No listings found" text="Try a different search, or check back soon: new verified listings are added regularly." /></div>
    @endif
</x-layouts.realtor>
