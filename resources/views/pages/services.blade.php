<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
@php
    $steps = cp('services.process.steps');
@endphp
<x-layouts.public title="Services" description="Real estate, construction and agriculture services from Oku Lands & Properties, with verified titles and flexible payment plans." :image="$settings->bannerUrl('services')">

    <x-page-hero
        :eyebrow="c('services.hero.eyebrow')"
        crumb="Services"
        :title="c('services.hero.title')"
        :accent="c('services.hero.accent')"
        :subtitle="c('services.hero.subtitle')"
        :image="$settings->bannerUrl('services')"
    />

    {{-- Quick jump --}}
    @if ($services->isNotEmpty())
        <div class="border-b border-ink/10 bg-card">
            {{-- Every service is visible: the links wrap neatly. On phones the first three show, with "More" for the rest. --}}
            <div class="relative mx-auto max-w-7xl px-3 py-3 sm:px-8 sm:py-4" data-quick-jump>
                {{-- One line on every screen. On phones the links that do not fit move into "More…". --}}
                <div class="flex flex-nowrap items-center gap-1.5 sm:gap-2 overflow-hidden" data-qj-row>
                    @foreach ($services as $s)
                        <a href="#{{ $s->slug }}" data-qj-pill class="shrink-0 whitespace-nowrap rounded-full border border-ink/15 px-3 py-2 sm:px-4 text-[0.78rem] sm:text-[0.8rem] font-semibold text-ink transition hover:border-brand hover:text-brand">{{ $s->title }}</a>
                    @endforeach
                    <button type="button" class="qj-more shrink-0 whitespace-nowrap rounded-full border border-brand/40 px-3 py-2 sm:px-4 text-[0.78rem] sm:text-[0.8rem] font-semibold text-brand lg:hidden" aria-expanded="false" aria-haspopup="true" data-qj-toggle hidden>More&hellip;</button>
                </div>

                {{-- Phones: the links that did not fit, in a dropdown panel. --}}
                <div class="absolute left-3 right-3 sm:left-5 sm:right-5 top-full z-30 mt-2 hidden overflow-hidden rounded-3xl border border-ink/10 bg-card p-2 shadow-soft lg:hidden" data-qj-panel role="menu">
                    @foreach ($services as $s)
                        <a href="#{{ $s->slug }}" role="menuitem" data-qj-link class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-semibold text-ink transition hover:bg-soft hover:text-brand">
                            {{ $s->title }}
                            <x-icon name="arrow" class="h-4 w-4 text-mute" />
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Service rows --}}
    <section class="bg-page py-8 md:py-16">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            @forelse ($services as $i => $service)
                @php $flip = $i % 2 === 1; @endphp
                <article id="{{ $service->slug }}" class="grid scroll-mt-28 grid-cols-1 items-center gap-8 border-b border-ink/10 py-14 last:border-0 md:py-24 lg:grid-cols-12 lg:gap-12">
                    <div class="lg:col-span-6 {{ $flip ? 'lg:order-2' : '' }}">
                        {{-- Fixed frame, filled edge to edge by the photo, centred. Photos are stored at full resolution, so they stay sharp. --}}
                        <div data-unveil class="relative aspect-[4/3] overflow-hidden rounded-[2rem] bg-soft">
                            <img src="{{ $service->imageUrl() }}" alt="{{ $service->title }}" class="absolute inset-0 h-full w-full object-cover object-center" loading="lazy" decoding="async">
                        </div>
                    </div>

                    <div class="lg:col-span-6 {{ $flip ? 'lg:order-1' : '' }} {{ $flip ? '' : 'lg:pl-6' }}">
                        <div data-reveal class="flex items-center gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon :name="$service->icon ?: 'building'" class="h-6 w-6" /></span>
                            <span class="text-xs font-bold tracking-[0.2em] text-mute">{{ sprintf('%02d', $i + 1) }}</span>
                        </div>
                        <h2 data-split class="display mt-6 text-4xl text-ink sm:text-6xl">{{ $service->title }}</h2>
                        <p data-reveal data-delay="100" class="mt-5 text-lg leading-relaxed text-ink/80">{{ $service->summary }}</p>

                        @if ($service->description)
                            <x-prose data-reveal data-delay="150" :html="$service->description" class="mt-5 leading-relaxed text-mute" />
                        @endif

                        @if (! empty($service->features))
                            <ul data-reveal data-delay="200" class="mt-7 grid gap-3 sm:grid-cols-2">
                                @foreach ($service->features as $feature)
                                    <li class="flex items-start gap-3 text-sm font-medium text-ink">
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon name="check" class="h-3 w-3" /></span>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <a href="{{ route('contact', ['interest' => 'service-'.$service->id]) }}" data-reveal data-delay="250" class="group mt-9 inline-flex items-center gap-3 text-sm font-semibold text-ink">
                            Enquire about {{ \Illuminate\Support\Str::lower($service->title) }}
                            <span class="circle-link"><x-icon name="arrow" class="h-4 w-4" /></span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="surface mx-auto my-16 max-w-xl px-6 py-14 text-center">
                    <h2 class="display text-4xl">{{ c('services.empty_title') }}</h2>
                    <p class="mt-4 text-mute">{{ c('services.empty_text') }}</p>
                    <a href="{{ route('contact') }}" class="btn btn-primary mt-7">Contact us</a>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Process --}}
    <section class="bg-soft py-12 md:py-20">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="max-w-2xl">
                <p data-reveal class="eyebrow text-brand">{{ c('services.process.eyebrow') }}</p>
                <h2 data-split class="display mt-5 text-5xl sm:text-6xl">{{ ch('services.process.title') }}</h2>
            </div>
            <ol class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5 md:mt-10">
                @foreach ($steps as $i => [$title, $text])
                    <li data-reveal data-delay="{{ $i * 100 }}" class="spot surface relative p-7 sm:p-8">
                        <span class="display text-5xl text-brand">{{ $i + 1 }}</span>
                        <h3 class="mt-10 font-serif text-3xl text-ink">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-mute">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <x-cta-band :title="c('services.cta.title')" :text="c('services.cta.text')" :button1="c('services.cta.button1')" :href1="route('contact')" :button2="c('services.cta.button2')" :href2="route('contact', ['interest' => 'inspection'])" />

</x-layouts.public>
