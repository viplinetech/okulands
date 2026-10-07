<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
<x-layouts.public title="About Us" :description="$settings->aboutHeadline()" :image="$settings->bannerUrl('about')">

    <x-page-hero
        :eyebrow="c('about.hero.eyebrow')"
        crumb="About"
        :title="c('about.hero.title')"
        :accent="c('about.hero.accent')"
        :subtitle="c('about.hero.subtitle')"
        :image="$settings->bannerUrl('about')"
    />

    {{-- Story: one panel. The text opens it; the landscape photo hangs in the lower part of the same panel. --}}
    <section class="bg-page px-3 py-10 sm:px-8 md:py-16">
        <div class="mx-auto max-w-7xl overflow-hidden rounded-[2rem] border border-ink/10 bg-card shadow-soft md:rounded-[2.5rem]">
            <div class="grid grid-cols-1 gap-8 p-6 sm:p-10 md:gap-12 md:p-14 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-5">
                    <p data-reveal class="eyebrow text-brand">{{ c('about.story.eyebrow') }}</p>
                    <h2 data-split class="display mt-4 text-4xl sm:text-5xl lg:text-6xl">{{ $settings->aboutHeadline() }}</h2>
                </div>
                <div class="lg:col-span-7">
                    <x-prose data-reveal data-delay="150" :html="\App\Support\StaticText::aboutBody()" class="text-[1rem] leading-relaxed text-mute sm:text-[1.02rem]" />
                    <div data-reveal data-delay="250" class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('services') }}" class="btn btn-primary">{{ c('about.story.button1') }} <x-icon name="arrow" class="arrow h-4 w-4" /></a>
                        <a href="{{ route('contact') }}" class="btn btn-outline">{{ c('about.story.button2') }}</a>
                    </div>
                </div>
            </div>

            {{-- The landscape photo, hung in the panel: inset from the edges, same rounded corners. --}}
            <div>
                <div data-unveil class="relative aspect-[4/3] overflow-hidden bg-soft sm:aspect-[16/9] lg:aspect-[21/9]">
                    <img src="{{ $settings->aboutImageUrl() }}" alt="Oku Lands & Properties" class="absolute inset-0 h-full w-full object-cover object-center" width="1600" height="700" loading="lazy" decoding="async">
                    @if ($settings->rc_number)
                        <div class="absolute bottom-3 left-3 rounded-full bg-white/90 px-3.5 py-1.5 text-[0.65rem] font-bold uppercase tracking-[0.16em] text-navy-900 backdrop-blur sm:bottom-5 sm:left-5 sm:px-4 sm:py-2 sm:text-xs">{{ $settings->rc_number }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Message from the CEO (full). Hidden until the admin adds name + message. --}}
    @if ($ceo = $settings->ceo())
        <section class="bg-card py-12 md:py-20">
            <div class="mx-auto grid max-w-6xl grid-cols-1 gap-10 px-5 sm:px-8 md:grid-cols-12 md:gap-16">
                <div class="md:col-span-4">
                    <div class="md:sticky md:top-28">
                        <div data-unveil class="relative mx-auto aspect-[4/5] max-w-[18rem] overflow-hidden rounded-[2rem] bg-soft shadow-soft md:max-w-none">
                            @if ($ceo['photo'])
                                <img src="{{ $ceo['photo'] }}" alt="{{ $ceo['name'] }}, {{ $ceo['title'] }}" data-fit class="h-full w-full object-cover" loading="lazy" width="480" height="600">
                            @else
                                <div class="flex h-full items-center justify-center text-mute"><x-icon name="user" class="h-16 w-16" /></div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="md:col-span-8">
                    <p data-reveal class="eyebrow text-brand">{{ c('about.ceo.eyebrow') }}</p>
                    <x-prose data-reveal data-delay="100" :html="$ceo['message']" class="prose-lead mt-6 text-[1.05rem] leading-[1.85] text-ink/85" />
                    <p data-reveal class="mt-8 font-serif text-4xl italic text-glow">{{ $ceo['name'] }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-mute">{{ $ceo['title'] }}, {{ $settings->site_name }}</p>
                </div>
            </div>
        </section>
    @endif

    {{-- Mission & vision --}}
    <section class="bg-soft py-12 md:py-20">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-5 px-5 sm:px-8 md:grid-cols-2 md:gap-6">
            <div data-reveal class="grain hairline relative overflow-hidden rounded-[2rem] bg-navy-950 p-8 text-white sm:p-12">
                <div class="aurora absolute -right-20 -top-20 h-72 w-72 rounded-full bg-sky-500/25 blur-[90px]"></div>
                <div class="relative">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-400/15 text-sky-300"><x-icon name="compass" class="h-6 w-6" /></span>
                    <p class="eyebrow mt-10 text-sky-300">{{ c('about.mission.label') }}</p>
                    <p class="display mt-5 text-3xl leading-[1.1] sm:text-4xl">{{ $settings->missionText() }}</p>
                </div>
            </div>
            <div data-reveal data-delay="120" class="surface relative overflow-hidden p-8 sm:p-12">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon name="key" class="h-6 w-6" /></span>
                <p class="eyebrow mt-10 text-brand">{{ c('about.vision.label') }}</p>
                <p class="display mt-5 text-3xl leading-[1.1] text-ink sm:text-4xl">{{ $settings->visionText() }}</p>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="bg-page py-12 md:py-20">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="max-w-2xl">
                <p data-reveal class="eyebrow text-brand">{{ c('about.values.eyebrow') }}</p>
                <h2 data-split class="display mt-5 text-5xl sm:text-6xl">{{ ch('about.values.title') }}</h2>
            </div>
            <div class="mt-8 grid grid-cols-1 gap-x-8 gap-y-2 sm:grid-cols-2 lg:grid-cols-3 md:mt-10">
                @foreach ($settings->valueItems() as $i => $value)
                    <div data-reveal data-delay="{{ ($i % 3) * 90 }}" class="group border-t border-ink/15 py-8">
                        <span class="text-xs font-bold tracking-[0.2em] text-brand">{{ sprintf('%02d', $i + 1) }}</span>
                        <h3 class="mt-4 font-serif text-3xl text-ink">{{ $value['title'] }}</h3>
                        <p class="mt-2 max-w-xs leading-relaxed text-mute">{{ $value['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Numbers --}}
    <section class="grain relative overflow-hidden bg-navy-950 py-12 text-white md:py-16">
        <div class="aurora absolute left-1/4 top-0 h-80 w-80 rounded-full bg-sky-500/20 blur-[110px]"></div>
        <div class="relative mx-auto grid max-w-7xl grid-cols-2 gap-x-6 gap-y-10 px-5 sm:px-8 lg:grid-cols-4">
            @foreach ($settings->statItems() as $i => $stat)
                <div data-reveal data-delay="{{ $i * 100 }}" class="border-t border-white/20 pt-5">
                    <p class="display text-5xl sm:text-7xl">
                        <span data-counter="{{ $stat['value'] }}" data-suffix="{{ $stat['suffix'] ?? '' }}">{{ number_format($stat['value']) }}{{ $stat['suffix'] ?? '' }}</span>
                    </p>
                    <p class="mt-3 text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-white/55">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Team (only when the admin has added members) --}}
    @if ($team = $settings->teamItems())
        <section class="bg-page py-12 md:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <p data-reveal class="eyebrow text-brand">{{ c('about.team.eyebrow') }}</p>
                <h2 data-split class="display mt-5 text-5xl sm:text-6xl">{{ ch('about.team.title') }}</h2>
                <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:mt-10 lg:grid-cols-4 lg:gap-x-6">
                    @foreach ($team as $i => $member)
                        <div data-reveal data-delay="{{ ($i % 4) * 90 }}">
                            <div class="aspect-[4/5] overflow-hidden rounded-3xl bg-soft">
                                @if ($member['photo_url'])
                                    <img src="{{ $member['photo_url'] }}" alt="{{ $member['name'] }}" data-fit class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <div class="flex h-full items-center justify-center text-mute"><x-icon name="user" class="h-14 w-14" /></div>
                                @endif
                            </div>
                            <h3 class="mt-4 font-serif text-2xl text-ink">{{ $member['name'] }}</h3>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand">{{ $member['role'] ?? '' }}</p>
                            @if (! empty($member['bio']))
                                <p class="mt-2 text-sm leading-relaxed text-mute">{{ $member['bio'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
        <section class="bg-soft py-12 md:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <p data-reveal class="eyebrow mx-auto flex w-max text-brand">{{ c('shared.stories') }}</p>
                <div data-reveal data-delay="150" class="mt-8"><x-testimonial-carousel :testimonials="$testimonials" /></div>
            </div>
        </section>
    @endif

    <x-cta-band />

</x-layouts.public>
