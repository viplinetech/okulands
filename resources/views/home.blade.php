<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    The home page previews each inner page (About, Services, Properties, Gallery, Realtors, Contact)
    and links through to it. All copy and imagery is admin-managed.
-->
<x-layouts.public :description="$settings->heroSubheadline()" :image="$heroImages[0] ?? null">

    {{-- 1. Hero --}}
    <x-hero :images="$heroImages" :headline="$settings->heroHeadline()" :subheadline="$settings->heroSubheadline()" :locations="$locations" />

    {{-- 3. About preview --}}
    <section class="bg-page py-12 md:py-20">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <p data-reveal class="eyebrow text-brand">{{ c('home.about.eyebrow') }}</p>

            <p data-scrub class="display mt-8 max-w-5xl text-[1.9rem] sm:text-6xl lg:text-7xl">{{ $settings->aboutHeadline() }}</p>

            {{-- Wide photo that crossfades between two images (both admin-managed), with the counters tucked underneath --}}
            <div class="mt-10 md:mt-12">
                <div data-unveil class="relative aspect-[4/3] overflow-hidden rounded-[1.75rem] bg-soft sm:aspect-[16/9] md:rounded-[2.25rem] lg:aspect-[21/9]">
                    <div data-crossfade class="absolute inset-0">
                        @foreach ($settings->aboutImageUrls() as $i => $src)
                            {{-- Each photo is shown whole over its own blurred copy, so nothing is cropped. --}}
                            <div class="xf-slide absolute inset-0 overflow-hidden {{ $i === 0 ? 'is-active' : '' }}">
                                <div class="fit-backdrop" style="background-image: url('{{ $src }}')"></div>
                                <img src="{{ $src }}" alt="{{ $i === 0 ? 'An Oku Lands development' : 'Oku Lands homes and land' }}" class="fit-img" width="1600" height="900" loading="lazy" decoding="async">
                            </div>
                        @endforeach
                    </div>
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-navy-950/40 via-transparent to-navy-950/30"></div>

                    <div class="absolute left-5 top-5 flex items-center gap-2.5 sm:left-8 sm:top-7" data-xf-dots aria-hidden="true">
                        <span class="xf-dot is-active"></span><span class="xf-dot"></span>
                    </div>
                    <a href="{{ route('about') }}" class="btn btn-ghost absolute right-4 top-4 !px-5 !py-2.5 text-[0.78rem] sm:right-7 sm:top-6">{{ c('home.about.button') }} <x-icon name="arrow" class="arrow h-3.5 w-3.5" /></a>
                </div>

                {{-- Counters: a floating card overlapping the photo --}}
                <div data-reveal class="relative z-10 mx-3 -mt-10 sm:mx-8 md:-mt-14 lg:mx-16">
                    <div class="overflow-hidden rounded-3xl bg-card shadow-soft">
                      <div class="-m-px grid grid-cols-2 lg:grid-cols-4">
                        @foreach ($settings->statItems() as $i => $stat)
                            <div class="spot group border border-ink/10 bg-card px-5 py-6 transition duration-500 sm:px-8 sm:py-8">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/10 text-brand transition duration-500 group-hover:bg-brand group-hover:text-brand-fg">
                                    <x-icon :name="['users', 'tag', 'key', 'building'][$i % 4]" class="h-[1.15rem] w-[1.15rem]" />
                                </span>
                                <p class="display mt-4 text-[2.6rem] leading-none text-ink sm:text-6xl">
                                    <span data-counter="{{ $stat['value'] }}" data-suffix="{{ $stat['suffix'] ?? '' }}">{{ number_format($stat['value']) }}{{ $stat['suffix'] ?? '' }}</span>
                                </p>
                                <span class="mt-4 block h-px w-8 bg-brand/40 transition-all duration-500 group-hover:w-14"></span>
                                <p class="mt-3 text-[0.66rem] font-semibold uppercase tracking-[0.18em] text-mute sm:text-[0.7rem]">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3b. A word from the CEO (teaser; the full message lives on About Us). Hidden until the admin adds it. --}}
    @if ($ceo = $settings->ceo())
        <section class="bg-card py-12 md:py-20">
            <div class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-8 px-5 sm:px-8 md:grid-cols-12 md:gap-14">
                <div class="mx-auto w-full max-w-[16rem] md:col-span-4 md:max-w-none">
                    <div data-unveil class="relative aspect-[4/5] overflow-hidden rounded-[2rem] bg-card shadow-soft">
                        @if ($ceo['photo'])
                            <img src="{{ $ceo['photo'] }}" alt="{{ $ceo['name'] }}, {{ $ceo['title'] }}" data-fit class="h-full w-full object-cover" loading="lazy" width="400" height="500">
                        @else
                            <div class="flex h-full items-center justify-center text-mute"><x-icon name="user" class="h-16 w-16" /></div>
                        @endif
                    </div>
                </div>
                <div class="md:col-span-8">
                    <p data-reveal class="eyebrow text-brand">{{ c('home.ceo.eyebrow') }}</p>
                    <blockquote data-reveal data-delay="100" class="display mt-5 text-[1.55rem] leading-[1.18] text-ink sm:text-4xl">
                        <span class="text-glow" aria-hidden="true">&ldquo;</span>{{ $ceo['excerpt'] }}<span class="text-glow" aria-hidden="true">&rdquo;</span>
                    </blockquote>
                    <div data-reveal data-delay="200" class="mt-7 flex flex-wrap items-center justify-between gap-5">
                        <div>
                            <p class="font-serif text-2xl text-ink">{{ $ceo['name'] }}</p>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-mute">{{ $ceo['title'] }}</p>
                        </div>
                        <a href="{{ route('about') }}" class="group inline-flex items-center gap-3 text-sm font-semibold text-ink">{{ c('home.ceo.link') }} <span class="circle-link"><x-icon name="arrow" class="h-4 w-4" /></span></a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- 4. Services preview: the three sectors, stacked --}}
    @if ($sectors->isNotEmpty())
        <section class="bg-soft py-12 md:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div class="mb-8 flex flex-col justify-between gap-6 md:mb-10 md:flex-row md:items-end">
                    <div class="max-w-3xl">
                        <p data-reveal class="eyebrow text-brand">{{ c('home.services.eyebrow') }}</p>
                        <h2 data-split class="display mt-5 text-5xl sm:text-7xl">From the land to the finished home, <span class="italic text-mute">under one trusted roof.</span></h2>
                    </div>
                    <a href="{{ route('services') }}" data-reveal class="group inline-flex items-center gap-3 text-sm font-semibold text-ink">
                        {{ c('home.services.link') }}
                        <span class="circle-link"><x-icon name="arrow" class="h-4 w-4" /></span>
                    </a>
                </div>

                <div class="space-y-5 md:space-y-0">
                    @foreach ($sectors as $index => $sector)
                        <div data-stack class="mb-0 md:sticky md:top-24 md:mb-6">
                            <a href="{{ route('services') }}" class="group relative flex min-h-[26rem] origin-top overflow-hidden rounded-[2rem] bg-navy-900 text-white will-change-transform md:min-h-[32rem]">
                                <img src="{{ $sector->imageUrl() }}" alt="{{ $sector->title }}" class="absolute inset-0 h-full w-full object-cover transition duration-[1600ms] ease-out group-hover:scale-105" loading="lazy" width="1200" height="640">
                                <div class="absolute inset-0 bg-gradient-to-r from-navy-950/90 via-navy-950/40 to-transparent"></div>
                                <div class="absolute inset-0 bg-gradient-to-t from-navy-950/55 via-transparent to-transparent"></div>

                                <div class="relative flex w-full flex-col justify-between p-6 sm:p-12">
                                    <span class="font-serif text-2xl text-sky-300">0{{ $index + 1 }}</span>
                                    <div class="max-w-md">
                                        <h3 class="display text-5xl sm:text-7xl">{{ $sector->title }}</h3>
                                        <p class="mt-4 leading-relaxed text-white/75">{{ $sector->summary }}</p>
                                        <span class="mt-7 inline-flex items-center gap-3 text-sm font-semibold">
                                            Learn more
                                            <span class="flex h-11 w-11 items-center justify-center rounded-full border border-white/30 transition duration-500 group-hover:bg-white group-hover:text-navy-950">
                                                <x-icon name="arrow" class="h-4 w-4" />
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 5. Featured properties --}}
    <section class="bg-page py-12 md:py-20">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <p data-reveal class="eyebrow text-brand">{{ c('home.featured.eyebrow') }}</p>
                    <h2 data-split class="display mt-5 max-w-2xl text-5xl sm:text-7xl">{{ ch('home.featured.title') }}</h2>
                </div>
                <a href="{{ route('properties.index') }}" data-reveal class="group inline-flex items-center gap-3 text-sm font-semibold text-ink">
                    {{ c('home.featured.link') }}
                    <span class="circle-link"><x-icon name="arrow" class="h-4 w-4" /></span>
                </a>
            </div>

            <div class="mt-8 md:mt-10">
                @if ($featuredProperties->isNotEmpty())
                    <x-property-carousel :properties="$featuredProperties" />
                @else
                    <div data-reveal class="grain relative overflow-hidden rounded-[2rem] bg-navy-950 px-6 py-12 text-center text-white sm:px-16 md:py-20">
                        <div class="aurora absolute left-1/2 top-0 h-80 w-80 -translate-x-1/2 rounded-full bg-sky-500/30 blur-[100px]"></div>
                        <div class="relative">
                            <p class="eyebrow text-sky-300">{{ c('home.featured.empty_eyebrow') }}</p>
                            <h3 class="display mx-auto mt-6 max-w-2xl text-4xl sm:text-5xl">{{ c('home.featured.empty_title') }}</h3>
                            <p class="mx-auto mt-5 max-w-md text-white/65">{{ c('home.featured.empty_text') }}</p>
                            <a href="{{ route('contact', ['interest' => 'buying']) }}" data-magnetic class="btn btn-sky mt-9">{{ c('home.featured.empty_button') }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- 6. Why Oku: commitments --}}
    <section class="bg-soft py-12 md:py-20">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-12 px-5 sm:px-8 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-4">
                <div class="lg:sticky lg:top-32">
                    <p data-reveal class="eyebrow text-brand">{{ c('home.why.eyebrow') }}</p>
                    <h2 data-split class="display mt-5 text-5xl sm:text-6xl">{{ ch('home.why.title') }}</h2>
                    <p data-reveal data-delay="200" class="mt-6 max-w-sm leading-relaxed text-mute">{{ c('home.why.text') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:col-span-8">
                @foreach ($settings->commitmentItems() as $i => $item)
                    <div data-reveal data-delay="{{ ($i % 2) * 120 }}" class="spot surface p-7 transition duration-500 hover:-translate-y-1 hover:shadow-soft sm:p-10">
                        <span class="display text-5xl text-brand sm:text-6xl">{{ $item['n'] ?? sprintf('%02d', $i + 1) }}</span>
                        <h3 class="mt-8 font-serif text-3xl leading-tight text-ink sm:mt-10">{{ $item['title'] }}</h3>
                        <p class="mt-3 leading-relaxed text-mute">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 7. Gallery preview: shown only once five photos have been uploaded in the admin --}}
    @if (\App\Models\GalleryItem::visible()->count() >= 5)
    <section class="bg-page py-12 md:py-20">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <p data-reveal class="eyebrow text-brand">{{ c('home.gallery.eyebrow') }}</p>
                    <h2 data-split class="display mt-5 max-w-2xl text-5xl sm:text-7xl">{{ ch('home.gallery.title') }}</h2>
                </div>
                <a href="{{ route('gallery') }}" data-reveal class="group inline-flex items-center gap-3 text-sm font-semibold text-ink">
                    {{ c('home.gallery.link') }}
                    <span class="circle-link"><x-icon name="arrow" class="h-4 w-4" /></span>
                </a>
            </div>

            <div class="mt-8 grid auto-rows-[9.5rem] grid-cols-2 gap-3 sm:auto-rows-[13rem] sm:gap-4 md:mt-10 md:grid-cols-4">
                @foreach (array_slice($gallery, 0, 5) as $i => $g)
                    <a href="{{ route('gallery') }}" data-reveal="scale" data-delay="{{ $i * 90 }}" class="group relative overflow-hidden rounded-3xl bg-soft {{ $i === 0 ? 'col-span-2 row-span-2' : '' }}">
                        <img src="{{ $g['image'] }}" alt="{{ $g['title'] ?? 'Oku Lands project' }}" data-fit class="h-full w-full object-cover transition duration-[1400ms] ease-out group-hover:scale-105" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-navy-950/70 via-transparent to-transparent opacity-80 transition group-hover:opacity-100"></div>
                        <span class="absolute bottom-3 left-3 rounded-full bg-white/90 px-3 py-1 text-[0.62rem] font-bold uppercase tracking-[0.14em] text-navy-900 sm:bottom-4 sm:left-4">{{ $g['category'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @endif

    {{-- 8. Realtor programme --}}
    <section class="bg-page pb-12 md:pb-20">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div data-reveal="scale" class="grain hairline relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy-800 via-navy-900 to-navy-950 px-6 py-14 text-white sm:px-14 sm:py-24 md:rounded-[2.5rem]">
                <div class="aurora absolute -left-32 top-0 h-96 w-96 rounded-full bg-sky-500/20 blur-[110px]"></div>
                <div class="relative grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-14">
                    <div>
                        <p class="eyebrow text-sky-300">{{ c('home.realtor.eyebrow') }}</p>
                        <h2 data-split class="display mt-6 text-5xl sm:text-7xl">{{ ch('home.realtor.title', 'italic text-shine-inv') }}</h2>
                        <p class="mt-6 max-w-md text-base leading-relaxed text-white/65 sm:text-lg">{{ c('home.realtor.text') }}</p>
                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('register') }}" data-magnetic class="btn btn-sky">{{ c('home.realtor.button1') }}</a>
                            <a href="{{ route('login') }}" data-magnetic class="btn btn-ghost">{{ c('home.realtor.button2') }}</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                        @foreach (cp('home.realtor.cards') as $i => [$t, $d])
                            <div data-reveal data-delay="{{ $i * 100 }}" class="spot glass rounded-3xl p-6 sm:p-7">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-400/15 text-sm font-bold text-sky-300">{{ $i + 1 }}</span>
                                <h3 class="mt-8 font-serif text-2xl leading-tight sm:mt-10">{{ $t }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-white/60">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 9. Testimonials (only when approved ones exist) --}}
    @if ($testimonials->isNotEmpty())
        <section class="bg-soft py-12 md:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <p data-reveal class="eyebrow mx-auto flex w-max text-brand">{{ c('shared.stories') }}</p>
                <div data-reveal data-delay="150" class="mt-8">
                    <x-testimonial-carousel :testimonials="$testimonials" />
                </div>
            </div>
        </section>
    @endif

    {{-- 10. Blog preview (only when posts exist) --}}
    @if ($posts->isNotEmpty())
        <section class="bg-page py-12 md:py-20">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                    <div>
                        <p data-reveal class="eyebrow text-brand">{{ c('home.blog.eyebrow') }}</p>
                        <h2 data-split class="display mt-5 max-w-2xl text-5xl sm:text-7xl">{{ ch('home.blog.title') }}</h2>
                    </div>
                    <a href="{{ route('blog.index') }}" data-reveal class="group inline-flex items-center gap-3 text-sm font-semibold text-ink">
                        {{ c('home.blog.link') }}
                        <span class="circle-link"><x-icon name="arrow" class="h-4 w-4" /></span>
                    </a>
                </div>
                <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 md:mt-10">
                    @foreach ($posts as $i => $post)
                        <x-post-card :post="$post" data-reveal data-delay="{{ $i * 90 }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 11. FAQ + contact preview --}}
    <section class="bg-soft py-12 md:py-20">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-16 px-5 sm:px-8 lg:grid-cols-2 lg:gap-14">
            <div>
                <p data-reveal class="eyebrow text-brand">{{ c('home.faq.eyebrow') }}</p>
                <h2 data-split class="display mt-5 text-5xl sm:text-6xl">{{ ch('home.faq.title', 'italic text-mute') }}</h2>
                <div data-reveal data-delay="150" class="mt-10">
                    <x-faq-accordion :items="$faqs" />
                </div>
                <a href="{{ route('faq') }}" data-reveal class="group mt-8 inline-flex items-center gap-3 text-sm font-semibold text-ink">
                    {{ c('home.faq.link') }}
                    <span class="circle-link"><x-icon name="arrow" class="h-4 w-4" /></span>
                </a>
            </div>

            <div>
                <p data-reveal class="eyebrow text-brand">{{ c('home.contact.eyebrow') }}</p>
                <h2 data-split class="display mt-5 text-5xl sm:text-6xl">{{ ch('home.contact.title') }}</h2>
                <div data-reveal data-delay="150" class="surface mt-10 p-6 sm:p-9">
                    <x-enquiry-form :interests="$interests" />
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
