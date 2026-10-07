<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
@php
    $categories = collect($items)->pluck('category')->filter()->unique()->values();
    $ratios = ['4 / 5', '1 / 1', '4 / 3', '3 / 4', '5 / 4', '4 / 5'];
@endphp
<x-layouts.public title="Gallery" description="A look at Oku Lands & Properties projects: developments, construction sites and farmland." :image="$settings->bannerUrl('gallery')">

    <x-page-hero
        :eyebrow="c('gallery.hero.eyebrow')"
        crumb="Gallery"
        :title="c('gallery.hero.title')"
        :accent="c('gallery.hero.accent')"
        :subtitle="c('gallery.hero.subtitle')"
        :image="$settings->bannerUrl('gallery')"
    />

    <section class="bg-page py-10 md:py-16">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">

            @if (count($items))
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="group" aria-label="Filter by category">
                        <button type="button" data-filter="all" aria-pressed="true" class="shrink-0 rounded-full border border-ink bg-ink px-5 py-2.5 text-[0.82rem] font-semibold text-page transition">All</button>
                        @foreach ($categories as $cat)
                            <button type="button" data-filter="{{ $cat }}" aria-pressed="false" class="shrink-0 rounded-full border border-ink/15 px-5 py-2.5 text-[0.82rem] font-semibold text-ink transition hover:border-ink/40">{{ $cat }}</button>
                        @endforeach
                    </div>
                    <p class="text-sm text-mute"><span data-gallery-count class="font-semibold text-ink">{{ count($items) }}</span> photos</p>
                </div>

                <div data-gallery class="masonry mt-8 md:mt-10">
                    @foreach ($items as $i => $item)
                        <a href="{{ $item['image'] }}" data-lb="gallery" data-cat="{{ $item['category'] }}" data-caption="{{ trim(($item['title'] ?? '').(! empty($item['caption']) ? ' · '.$item['caption'] : '')) }}"
                           data-reveal data-delay="{{ ($i % 3) * 80 }}"
                           class="g-item group relative block overflow-hidden rounded-3xl bg-soft">
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? $item['category'] }}" loading="lazy"
                                 data-fit class="w-full object-cover transition duration-[1400ms] ease-out group-hover:scale-105" style="aspect-ratio: {{ $ratios[$i % count($ratios)] }}">
                            <div class="absolute inset-0 bg-gradient-to-t from-navy-950/75 via-transparent to-transparent opacity-0 transition duration-500 group-hover:opacity-100 max-sm:opacity-100"></div>
                            <div class="absolute inset-x-4 bottom-4 flex items-end justify-between gap-3 text-white opacity-0 transition duration-500 group-hover:opacity-100 max-sm:opacity-100">
                                <div class="min-w-0">
                                    <p class="text-[0.62rem] font-bold uppercase tracking-[0.18em] text-sky-300">{{ $item['category'] }}</p>
                                    @if (! empty($item['title']))
                                        <p class="truncate font-serif text-xl leading-tight">{{ $item['title'] }}</p>
                                    @endif
                                </div>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/40 bg-white/10 backdrop-blur"><x-icon name="plus" class="h-4 w-4" /></span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="surface mx-auto max-w-xl px-6 py-12 text-center">
                    <h2 class="display text-4xl">{{ c('gallery.empty_title') }}</h2>
                    <p class="mt-4 text-mute">{{ c('gallery.empty_text') }}</p>
                    <a href="{{ route('properties.index') }}" class="btn btn-primary mt-7">View properties</a>
                </div>
            @endif
        </div>
    </section>

    <x-cta-band :title="c('gallery.cta.title')" :text="c('gallery.cta.text')" :button1="c('gallery.cta.button1')" :href1="route('contact', ['interest' => 'inspection'])" :button2="c('gallery.cta.button2')" :href2="route('contact')" />

</x-layouts.public>
