<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
@php
    $images = $property->imageUrls() ?: [$property->coverUrl()];
    $count = count($images);
    $shown = min($count, 5);
    $sold = $property->status === 'sold';
    $waText = "Hello Oku Lands, I'm interested in \"{$property->title}\" ({$property->location}). Please share more details.";
    $specs = array_filter([
        ['tag', 'Type', $property->type],
        ['ruler', 'Size', $property->size ? $property->size.' sqm' : null],
        ['bed', 'Bedrooms', $property->bedrooms],
        ['bath', 'Bathrooms', $property->bathrooms],
        ['map', 'Service', $property->sectorLabel()],
    ], fn ($s) => filled($s[2]));
@endphp
<x-layouts.public :title="$property->title" :description="\App\Support\RichText::excerpt($property->description ?: $property->title, 155)" :image="$images[0]" :hide-fab="true">

    <x-page-hero
        :eyebrow="$property->sectorLabel().' · '.ucfirst($property->status)"
        :title="$property->title"
        :subtitle="$property->location"
        :crumb="$property->title"
        :image="$images[0]"
        :compact="true"
    />

    <section class="bg-page py-8 md:py-12">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-5 sm:px-8 lg:grid-cols-12 lg:gap-14">

            <div class="lg:col-span-7 xl:col-span-8">
                {{-- Photo mosaic + lightbox --}}
                <div data-reveal class="grid h-[19rem] grid-cols-4 grid-rows-2 gap-2 sm:h-[30rem] sm:gap-3">
                    @foreach (array_slice($images, 0, $shown) as $i => $src)
                        @php
                            $span = match (true) {
                                $i === 0 && $count === 1 => 'col-span-4 row-span-2',
                                $i === 0 => 'col-span-2 row-span-2',
                                $count === 2 => 'col-span-2 row-span-2',
                                $count === 3 => 'col-span-2 row-span-1',
                                default => '',
                            };
                        @endphp
                        <a href="{{ $src }}" data-lb="prop" data-caption="{{ $property->title }} ({{ $i + 1 }}/{{ $count }})" class="group relative overflow-hidden rounded-2xl bg-soft sm:rounded-3xl {{ $span }}">
                            <img src="{{ $src }}" alt="{{ $property->title }}, photo {{ $i + 1 }}" data-fit class="h-full w-full object-cover transition duration-[1200ms] ease-out group-hover:scale-105" {{ $i === 0 ? 'fetchpriority=high' : 'loading=lazy' }}>
                            @if ($i === $shown - 1 && $count > $shown)
                                <span class="absolute inset-0 flex items-center justify-center bg-navy-950/60 font-serif text-3xl text-white">+{{ $count - $shown }}</span>
                            @endif
                        </a>
                    @endforeach
                    @foreach (array_slice($images, $shown) as $j => $src)
                        <a href="{{ $src }}" data-lb="prop" data-caption="{{ $property->title }} ({{ $shown + $j + 1 }}/{{ $count }})" class="hidden" tabindex="-1" aria-hidden="true"></a>
                    @endforeach
                </div>
                @if ($count > 1)
                    <p class="mt-3 text-xs text-mute">Tap a photo to view all {{ $count }} images.</p>
                @endif

                {{-- Price + summary --}}
                <div data-reveal class="mt-10 flex flex-wrap items-end justify-between gap-4 border-b border-ink/10 pb-8">
                    <div>
                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-mute">{{ $sold ? 'Sold at' : ($property->isMultiUnit() ? 'Price per unit' : 'Price') }}</p>
                        <p class="display mt-2 text-5xl text-ink sm:text-6xl">&#8358;{{ number_format($property->price) }}</p>
                        @if (! $sold && $property->isMultiUnit())
                            <p class="mt-2 text-sm font-semibold text-brand">{{ $property->unitsRemaining() }} of {{ $property->units_total }} units left</p>
                        @endif
                    </div>
                    <p class="flex items-center gap-2 text-sm text-mute"><x-icon name="pin" class="h-4 w-4 text-flag-500" />{{ $property->location }}</p>
                </div>

                {{-- Specs --}}
                @if ($specs)
                    <dl class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                        @foreach ($specs as $k => [$icon, $label, $value])
                            <div data-reveal data-delay="{{ $k * 60 }}" class="surface flex items-center gap-4 p-4 sm:p-5">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon :name="$icon" class="h-5 w-5" /></span>
                                <div class="min-w-0">
                                    <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-mute">{{ $label }}</dt>
                                    <dd class="mt-0.5 truncate font-semibold text-ink">{{ $value }}</dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                @endif

                {{-- Description --}}
                @if (filled($property->description))
                    <div data-reveal class="mt-8">
                        <h2 class="display text-4xl text-ink">About this property</h2>
                        <x-prose :html="$property->description" class="mt-5 text-[1.02rem] leading-relaxed text-mute" />
                    </div>
                @endif

                {{-- Trust points --}}
                <div data-reveal class="mt-8 grid gap-3 sm:grid-cols-3">
                    @foreach ([['shield', 'Verified documentation'], ['compass', 'Guided site inspection'], ['wallet', 'Ask about payment plans']] as [$icon, $label])
                        <div class="flex items-center gap-3 rounded-2xl border border-ink/10 px-4 py-3.5 text-sm font-semibold text-ink">
                            <x-icon :name="$icon" class="h-5 w-5 shrink-0 text-brand" />{{ $label }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Enquiry card --}}
            <aside id="book" class="scroll-mt-28 lg:col-span-5 xl:col-span-4">
                <div class="surface shadow-soft p-6 sm:p-8 lg:sticky lg:top-28">
                    <p class="eyebrow text-brand">{{ $sold ? 'Similar properties' : 'Book an inspection' }}</p>
                    <h2 class="display mt-4 text-3xl text-ink sm:text-4xl">{{ $sold ? 'This one is taken.' : 'Interested? Let’s talk.' }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-mute">{{ $sold ? 'Tell us what you liked and we will find you a comparable, verified property.' : 'Send your details and our team will confirm an inspection date.' }}</p>

                    <div class="mt-7">
                        <x-enquiry-form :property-id="$property->id" :compact="true" button="{{ $sold ? 'Find me similar' : 'Request inspection' }}" placeholder="Preferred date, questions, budget…" />
                    </div>

                    @if ($settings->whatsapp)
                        <div class="my-6 flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.2em] text-mute"><span class="h-px flex-1 bg-ink/10"></span>or<span class="h-px flex-1 bg-ink/10"></span></div>
                        <a href="{{ $settings->whatsappUrl($waText) }}" target="_blank" rel="noopener" class="btn btn-outline w-full">
                            <x-icon name="whatsapp" class="h-5 w-5 text-[#25D366]" /> Chat on WhatsApp
                        </a>
                    @endif
                </div>
            </aside>
        </div>
    </section>

    {{-- Similar properties --}}
    @if ($related->isNotEmpty())
        <section class="bg-soft py-16 md:py-28">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div class="flex items-end justify-between gap-6">
                    <h2 data-split class="display text-4xl sm:text-6xl">You may also <span class="italic text-glow">like.</span></h2>
                    <a href="{{ route('properties.index') }}" class="group hidden items-center gap-3 text-sm font-semibold text-ink sm:inline-flex">All properties <span class="circle-link"><x-icon name="arrow" class="h-4 w-4" /></span></a>
                </div>
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $i => $r)
                        <x-property-card :property="$r" data-reveal data-delay="{{ $i * 90 }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Spacer so the mobile action bar never covers the footer --}}
    <div class="h-20 lg:hidden" aria-hidden="true"></div>

    {{-- Mobile action bar --}}
    <div class="safe-bottom fixed inset-x-0 bottom-0 z-40 flex items-center gap-3 border-t border-ink/10 bg-card/95 px-4 pt-3 backdrop-blur-xl lg:hidden">
        <div class="min-w-0 flex-1">
            <p class="text-[0.6rem] font-semibold uppercase tracking-[0.2em] text-mute">{{ $sold ? 'Sold' : 'Price' }}</p>
            <p class="truncate font-serif text-2xl leading-none text-ink">&#8358;{{ number_format($property->price) }}</p>
        </div>
        @if ($settings->whatsapp)
            <a href="{{ $settings->whatsappUrl($waText) }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-ink/15 text-[#25D366]"><x-icon name="whatsapp" class="h-6 w-6" /></a>
        @endif
        <a href="#enquire" class="btn btn-primary !px-6 !py-3.5">{{ $sold ? 'Ask about similar' : 'Enquire' }}</a>
    </div>

</x-layouts.public>
