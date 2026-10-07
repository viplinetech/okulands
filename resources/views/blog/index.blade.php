<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
<x-layouts.public title="Blog" description="Insights on buying land, verifying titles, construction and farmland investment in Nigeria, from the Oku Lands team." :image="$settings->bannerUrl('blog')">

    <x-page-hero
        :eyebrow="c('blog.hero.eyebrow')"
        crumb="Blog"
        :title="c('blog.hero.title')"
        :accent="c('blog.hero.accent')"
        :subtitle="c('blog.hero.subtitle')"
        :image="$settings->bannerUrl('blog')"
    />

    <section class="bg-page py-10 md:py-16">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">

            @if ($posts->isNotEmpty() || $filtering)
                {{-- Search + categories --}}
                <form method="GET" action="{{ route('blog.index') }}" class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="group" aria-label="Categories">
                        <a href="{{ route('blog.index') }}" class="shrink-0 rounded-full border px-5 py-2.5 text-[0.82rem] font-semibold transition {{ empty($filters['category']) ? 'border-ink bg-ink text-page' : 'border-ink/15 text-ink hover:border-ink/40' }}">All</a>
                        @foreach ($categories as $cat)
                            <a href="{{ route('blog.index', ['category' => $cat]) }}" class="shrink-0 rounded-full border px-5 py-2.5 text-[0.82rem] font-semibold transition {{ ($filters['category'] ?? '') === $cat ? 'border-ink bg-ink text-page' : 'border-ink/15 text-ink hover:border-ink/40' }}">{{ $cat }}</a>
                        @endforeach
                    </div>
                    <label class="relative block md:w-80">
                        <span class="sr-only">Search articles</span>
                        <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-mute" />
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search articles" class="field !rounded-full !py-3 !pl-11">
                        @if (! empty($filters['category'])) <input type="hidden" name="category" value="{{ $filters['category'] }}"> @endif
                    </label>
                </form>
            @endif

            @if ($posts->isNotEmpty())
                {{-- Lead story --}}
                @if ($lead)
                    <a href="{{ route('blog.show', $lead->slug) }}" data-reveal class="group mt-10 grid grid-cols-1 items-center gap-8 md:mt-10 lg:grid-cols-12 lg:gap-14">
                        <div class="relative aspect-[16/10] overflow-hidden rounded-[2rem] bg-soft lg:col-span-7">
                            <img src="{{ $lead->coverUrl() }}" alt="{{ $lead->title }}" data-fit class="h-full w-full object-cover transition duration-[1600ms] ease-out group-hover:scale-105" fetchpriority="high">
                            <span class="absolute left-4 top-4 rounded-full bg-brand px-3.5 py-1.5 text-[0.65rem] font-bold uppercase tracking-[0.14em] text-brand-fg">Latest</span>
                        </div>
                        <div class="lg:col-span-5">
                            <p class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.16em] text-mute">
                                @if ($lead->category)<span class="text-brand">{{ $lead->category }}</span><span aria-hidden="true">&middot;</span>@endif
                                <time datetime="{{ $lead->published_at->toDateString() }}">{{ $lead->published_at->format('M j, Y') }}</time>
                                <span aria-hidden="true">&middot;</span><span>{{ $lead->readingMinutes() }} min read</span>
                            </p>
                            <h2 class="display mt-4 text-4xl text-ink transition group-hover:text-brand sm:text-5xl">{{ $lead->title }}</h2>
                            <p class="mt-4 leading-relaxed text-mute">{{ $lead->summary(200) }}</p>
                            <span class="mt-7 inline-flex items-center gap-3 text-sm font-semibold text-ink">Read article <span class="circle-link"><x-icon name="arrow" class="h-4 w-4" /></span></span>
                        </div>
                    </a>
                @endif

                @php $rest = $lead ? $posts->getCollection()->slice(1) : $posts->getCollection(); @endphp
                @if ($rest->isNotEmpty())
                    <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 md:mt-8">
                        @foreach ($rest as $i => $post)
                            <x-post-card :post="$post" data-reveal data-delay="{{ ($i % 3) * 90 }}" />
                        @endforeach
                    </div>
                @endif

                <div class="mt-10">{{ $posts->links('pagination.premium') }}</div>
            @else
                <div data-reveal class="surface mx-auto mt-6 max-w-2xl px-6 py-12 text-center sm:px-12">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon name="search" class="h-6 w-6" /></span>
                    @if ($filtering)
                        <h2 class="display mt-6 text-4xl">No articles found.</h2>
                        <p class="mx-auto mt-4 max-w-md text-mute">Try a different search or category.</p>
                        <a href="{{ route('blog.index') }}" class="btn btn-outline mt-8">Show all articles</a>
                    @else
                        <h2 class="display mt-6 text-4xl">{{ c('blog.none_title') }}</h2>
                        <p class="mx-auto mt-4 max-w-md text-mute">{{ c('blog.none_text') }}</p>
                        <a href="{{ route('properties.index') }}" class="btn btn-primary mt-8">Browse properties</a>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <x-cta-band :title="c('blog.cta.title')" :text="c('blog.cta.text')" :button1="c('blog.cta.button1')" :href1="route('contact')" :button2="c('blog.cta.button2')" :href2="route('properties.index')" />

</x-layouts.public>
