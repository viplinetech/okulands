<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    FAQ knowledge base / Help Center. Content comes from the admin-managed faq_items table.
-->
@php
    $suggestions = ['payment plans', 'inspection', 'title', 'commission', 'construction'];
    $tel = $settings->phone ? preg_replace('/\s+/', '', $settings->phone) : null;
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $groups->flatten(1)->take(50)->map(fn ($f) => [
            '@type' => 'Question',
            'name' => $f->question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => \App\Support\RichText::text($f->answer)],
        ])->values()->all(),
    ];
@endphp
<x-layouts.public title="FAQs & Help Center" description="Answers to common questions about buying land, titles, payment plans, inspections, construction, agriculture and the Oku Lands realtor programme." :image="$settings->bannerUrl('contact')">
    <x-slot:head>
        @if ($total)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif
    </x-slot:head>

    <x-page-hero
        :eyebrow="c('faq.hero.eyebrow')"
        crumb="FAQs"
        :title="c('faq.hero.title')"
        :accent="c('faq.hero.accent')"
        :subtitle="c('faq.hero.subtitle')"
        :image="$settings->bannerUrl('contact')"
    >
        @if ($total)
            <div data-reveal data-delay="600" class="mt-8 max-w-2xl">
                <form role="search" onsubmit="return false" class="relative">
                    <label for="faq-search" class="sr-only">Search the knowledge base</label>
                    <x-icon name="search" class="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-white/60" />
                    <input id="faq-search" type="search" autocomplete="off" value="{{ request('q') }}" placeholder="Search {{ $total }} answers…"
                           class="w-full rounded-full border border-white/25 bg-white/10 py-4 pl-14 pr-16 text-base font-medium text-white shadow-2xl shadow-black/20 backdrop-blur-xl placeholder:text-white/50 focus:border-sky-300 focus:ring-4 focus:ring-sky-300/20">
                    <kbd class="pointer-events-none absolute right-5 top-1/2 hidden -translate-y-1/2 rounded-md border border-white/25 px-2 py-0.5 text-xs font-semibold text-white/60 sm:block" aria-hidden="true">/</kbd>
                </form>
                <p class="mt-4 flex flex-wrap items-center gap-2 text-xs text-white/60">
                    <span class="font-semibold uppercase tracking-[0.2em]">Popular</span>
                    @foreach ($suggestions as $s)
                        <button type="button" data-faq-suggest="{{ $s }}" class="rounded-full border border-white/20 px-3 py-1 font-medium text-white/80 transition hover:border-sky-300 hover:text-white">{{ $s }}</button>
                    @endforeach
                </p>
            </div>
        @endif
    </x-page-hero>

    <section class="bg-page py-10 md:py-16">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            @if ($total)
                <div data-faq-kb class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-12">

                    {{-- Categories --}}
                    <aside class="lg:col-span-3">
                        <div class="lg:sticky lg:top-28">
                            <p class="mb-3 hidden text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-mute lg:block">Browse by topic</p>
                            <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:none] lg:flex-col lg:gap-1 lg:overflow-visible [&::-webkit-scrollbar]:hidden" role="group" aria-label="Topics">
                                <button type="button" data-faq-cat="all" aria-pressed="true"
                                        class="flex shrink-0 items-center justify-between gap-3 rounded-full border border-ink bg-ink px-4 py-2.5 text-[0.82rem] font-semibold text-page transition lg:rounded-xl">
                                    All topics <span class="text-xs opacity-60">{{ $total }}</span>
                                </button>
                                @foreach ($groups as $category => $entries)
                                    <button type="button" data-faq-cat="{{ $category }}" aria-pressed="false"
                                            class="flex shrink-0 items-center justify-between gap-3 rounded-full border border-ink/15 px-4 py-2.5 text-[0.82rem] font-semibold text-ink transition hover:border-ink/40 lg:rounded-xl lg:border-transparent lg:hover:bg-soft">
                                        {{ $category }} <span class="text-xs opacity-50">{{ $entries->count() }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </aside>

                    {{-- Answers --}}
                    <div class="lg:col-span-9">
                        <p data-faq-status class="mb-6 text-sm text-mute" aria-live="polite">Showing all {{ $total }} answers</p>

                        <div class="space-y-12">
                            @foreach ($groups as $category => $entries)
                                <section data-faq-group>
                                    <h2 class="display text-3xl text-ink sm:text-4xl">{{ $category }}</h2>
                                    <div class="mt-4 divide-y divide-ink/10 border-y border-ink/10">
                                        @foreach ($entries as $item)
                                            <div id="faq-{{ $item->id }}" data-faq-item data-cat="{{ $category }}" data-search="{{ \Illuminate\Support\Str::lower($item->question.' '.\App\Support\RichText::text($item->answer).' '.$category) }}" class="acc-item scroll-mt-28">
                                                <h3>
                                                    <button type="button" aria-expanded="false" class="flex w-full items-center justify-between gap-6 py-5 text-left sm:py-6">
                                                        <span class="faq-q font-serif text-xl leading-snug text-ink sm:text-2xl">{{ $item->question }}</span>
                                                        <span class="acc-plus flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-ink/20 text-ink"><x-icon name="plus" class="h-4 w-4" /></span>
                                                    </button>
                                                </h3>
                                                <div class="acc-body">
                                                    <div>
                                                        <x-prose :html="$item->answer" class="max-w-2xl pb-5 text-[0.98rem] leading-relaxed text-mute" />
                                                        <div data-feedback data-id="{{ $item->id }}" data-url="{{ route('faq.feedback', $item) }}" class="flex flex-wrap items-center gap-3 pb-7 text-xs text-mute">
                                                            <span class="font-semibold">Was this helpful?</span>
                                                            <button type="button" data-vote="yes" class="rounded-full border border-ink/15 px-3.5 py-1.5 font-semibold text-ink transition hover:border-brand hover:text-brand">Yes</button>
                                                            <button type="button" data-vote="no" class="rounded-full border border-ink/15 px-3.5 py-1.5 font-semibold text-ink transition hover:border-brand hover:text-brand">No</button>
                                                            <span data-thanks hidden class="font-semibold text-brand">Thanks for your feedback.</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>

                        {{-- No results --}}
                        <div data-faq-empty hidden class="surface px-6 py-14 text-center sm:px-12">
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon name="search" class="h-6 w-6" /></span>
                            <h2 class="display mt-6 text-3xl text-ink sm:text-4xl">No answers match your search.</h2>
                            <p class="mx-auto mt-3 max-w-md text-mute">Try a different word, or ask our team directly. We usually reply the same day.</p>
                            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                                <a href="{{ route('contact') }}" class="btn btn-primary">Ask our team</a>
                                @if ($settings->whatsapp)
                                    <a href="{{ $settings->whatsappUrl('Hello Oku Lands, I have a question.') }}" target="_blank" rel="noopener" class="btn btn-outline"><x-icon name="whatsapp" class="h-5 w-5 text-[#25D366]" /> WhatsApp us</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="surface mx-auto max-w-xl px-6 py-14 text-center">
                    <h2 class="display text-4xl">{{ c('faq.empty_title') }}</h2>
                    <p class="mt-4 text-mute">{{ c('faq.empty_text') }}</p>
                    <a href="{{ route('contact') }}" class="btn btn-primary mt-7">Contact us</a>
                </div>
            @endif
        </div>
    </section>

    {{-- Still need help? --}}
    <section class="bg-soft py-12 md:py-16">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="max-w-2xl">
                <p data-reveal class="eyebrow text-brand">{{ c('faq.help.eyebrow') }}</p>
                <h2 data-split class="display mt-4 text-4xl sm:text-6xl">{{ ch('faq.help.title') }}</h2>
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                @foreach (array_filter([
                    $settings->whatsapp ? ['whatsapp', 'WhatsApp', 'Chat with our team', $settings->whatsappUrl('Hello Oku Lands, I have a question.')] : null,
                    $tel ? ['phone', 'Call us', $settings->phone, 'tel:'.$tel] : null,
                    ['mail', 'Send a message', 'We reply the same day', route('contact')],
                ]) as $i => [$icon, $label, $value, $href])
                    <a href="{{ $href }}" @if (str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif data-reveal data-delay="{{ $i * 90 }}" class="spot surface group flex items-center gap-5 p-5 transition duration-500 hover:-translate-y-0.5 sm:p-6">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon :name="$icon" class="h-5 w-5" /></span>
                        <span class="min-w-0">
                            <span class="block text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-mute">{{ $label }}</span>
                            <span class="mt-1 block break-words font-semibold text-ink">{{ $value }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <x-cta-band />

</x-layouts.public>
