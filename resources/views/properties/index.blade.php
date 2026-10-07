<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
@php
    $typeOptions = \App\Models\Property::activeSectors();
    $statusOptions = ['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold'];
    $active = collect($filters)->filter()->except('sort');
    // One removable pill per active filter; each link is the current search minus that filter.
    $pills = $active->map(fn ($value, $key) => [
        'label' => match ($key) {
            'q' => '“'.$value.'”',
            'sector' => $typeOptions[$value] ?? $value,
            'budget' => $budgets[$value] ?? $value,
            'status' => $statusOptions[$value] ?? $value,
            default => $value,
        },
        'url' => route('properties.index', collect($filters)->filter()->except($key)->all()),
    ]);
@endphp
<x-layouts.public title="Properties" description="Browse verified land and property for sale across Nigeria: real estate, construction plots and farmland.">

    <x-page-hero
        :eyebrow="c('properties.hero.eyebrow')"
        crumb="Properties"
        :title="c('properties.hero.title')"
        :accent="c('properties.hero.accent')"
        :subtitle="c('properties.hero.subtitle')"
        :image="$settings->bannerUrl('properties')"
    />

    <section class="bg-page pb-12 md:pb-20">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">

            {{-- Search & filters --}}
            <form method="GET" action="{{ route('properties.index') }}" data-autosubmit role="search" aria-label="Filter properties"
                  class="surface shadow-soft relative z-10 -mt-8 p-3 sm:-mt-12 sm:p-4">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-[1.5fr_1fr_1fr_1fr_auto] md:gap-0">
                    <label class="block rounded-2xl px-4 py-2.5 transition focus-within:bg-soft md:border-r md:border-ink/10 md:rounded-r-none">
                        <span class="field-label !mb-1">Location</span>
                        <span class="flex items-center gap-2.5">
                            <x-icon name="pin" class="h-4 w-4 shrink-0 text-brand" />
                            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" list="prop-locations" autocomplete="off" placeholder="City, area or keyword" class="w-full border-0 bg-transparent p-0 text-[0.95rem] font-medium text-ink placeholder:text-mute/60 focus:ring-0">
                        </span>
                    </label>
                    <label class="block rounded-2xl px-4 py-2.5 transition focus-within:bg-soft md:rounded-none md:border-r md:border-ink/10">
                        <span class="field-label !mb-1">Type</span>
                        <select name="sector" class="w-full border-0 bg-transparent p-0 pr-6 text-[0.95rem] font-medium text-ink focus:ring-0">
                            <option value="">All types</option>
                            @foreach ($typeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sector'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block rounded-2xl px-4 py-2.5 transition focus-within:bg-soft md:rounded-none md:border-r md:border-ink/10">
                        <span class="field-label !mb-1">Budget</span>
                        <select name="budget" class="w-full border-0 bg-transparent p-0 pr-6 text-[0.95rem] font-medium text-ink focus:ring-0">
                            <option value="">Any budget</option>
                            @foreach ($budgets as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['budget'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block rounded-2xl px-4 py-2.5 transition focus-within:bg-soft md:rounded-l-none">
                        <span class="field-label !mb-1">Status</span>
                        <select name="status" class="w-full border-0 bg-transparent p-0 pr-6 text-[0.95rem] font-medium text-ink focus:ring-0">
                            <option value="">Any status</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="btn btn-primary mt-1 md:mx-2 md:mt-0 md:self-center md:px-8">
                        <x-icon name="search" class="h-4 w-4" /> Search
                    </button>
                </div>

                @if ($locations)
                    <datalist id="prop-locations">
                        @foreach ($locations as $loc)<option value="{{ $loc }}"></option>@endforeach
                    </datalist>
                @endif

                {{-- Active filters + sort --}}
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-ink/10 px-2 pt-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @forelse ($pills as $pill)
                            <a href="{{ $pill['url'] }}" class="group inline-flex items-center gap-1.5 rounded-full bg-brand/10 py-1.5 pl-3.5 pr-2.5 text-[0.78rem] font-semibold text-brand transition hover:bg-brand hover:text-brand-fg" aria-label="Remove filter {{ $pill['label'] }}">
                                {{ $pill['label'] }}
                                <x-icon name="plus" class="h-3.5 w-3.5 rotate-45" />
                            </a>
                        @empty
                            <span class="text-[0.8rem] text-mute">Search by location, or narrow by type, budget and status.</span>
                        @endforelse
                        @if ($pills->count() > 1)
                            <a href="{{ route('properties.index') }}" class="px-1 text-[0.78rem] font-semibold text-mute underline-offset-4 hover:text-ink hover:underline">Clear all</a>
                        @endif
                    </div>
                    <label class="flex items-center gap-2 text-[0.8rem] text-mute">
                        <span class="font-semibold">Sort by</span>
                        <select name="sort" class="rounded-full border border-ink/15 bg-card py-1.5 pl-3.5 pr-9 text-[0.8rem] font-semibold text-ink focus:border-brand focus:ring-brand/20">
                            @foreach (['latest' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low'] as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </form>

            {{-- Results --}}
            <div class="mt-10 flex flex-wrap items-center justify-between gap-3 md:mt-10">
                <p class="text-sm text-mute" aria-live="polite">
                    @if ($properties->total() > 0)
                        Showing <span class="font-semibold text-ink">{{ $properties->firstItem() }}&ndash;{{ $properties->lastItem() }}</span> of <span class="font-semibold text-ink">{{ $properties->total() }}</span> {{ \Illuminate\Support\Str::plural('property', $properties->total()) }}
                    @else
                        No properties found
                    @endif
                </p>
            </div>

            @if ($properties->isNotEmpty())
                <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($properties as $i => $property)
                        <x-property-card :property="$property" data-reveal data-delay="{{ ($i % 3) * 90 }}" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $properties->links('pagination.premium') }}
                </div>
            @else
                <div data-reveal class="surface mx-auto mt-10 max-w-2xl px-6 py-12 text-center sm:px-12">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon name="search" class="h-6 w-6" /></span>
                    @if ($total === 0)
                        <h2 class="display mt-6 text-4xl">{{ c('properties.none_title') }}</h2>
                        <p class="mx-auto mt-4 max-w-md text-mute">{{ c('properties.none_text') }}</p>
                        <a href="{{ route('contact', ['interest' => 'buying']) }}" class="btn btn-primary mt-8">Request the catalogue</a>
                    @else
                        <h2 class="display mt-6 text-4xl">{{ c('properties.nomatch_title') }}</h2>
                        <p class="mx-auto mt-4 max-w-md text-mute">{{ c('properties.nomatch_text') }}</p>
                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                            <a href="{{ route('properties.index') }}" class="btn btn-outline">Clear filters</a>
                            <a href="{{ route('contact', ['interest' => 'buying']) }}" class="btn btn-primary">Ask our team</a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <x-cta-band :title="c('properties.cta.title')" :text="c('properties.cta.text')" :button1="c('properties.cta.button1')" :href1="route('contact', ['interest' => 'buying'])" :button2="c('properties.cta.button2')" :href2="route('register')" />

</x-layouts.public>
