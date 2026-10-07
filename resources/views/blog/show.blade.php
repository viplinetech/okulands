<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
@php
    $url = route('blog.show', $post->slug);
@endphp
<x-layouts.public :title="$post->title" :description="$post->summary(155)" :image="$post->coverUrl()">
    <x-slot:head>
        <script type="application/ld+json">{!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'image' => $post->coverUrl(),
            'datePublished' => $post->published_at?->toIso8601String(),
            'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : null,
            'publisher' => ['@type' => 'Organization', 'name' => $settings->site_name],
        ]), JSON_UNESCAPED_SLASHES) !!}</script>
    </x-slot:head>

    <x-page-hero
        :eyebrow="$post->category ?: 'Article'"
        :title="$post->title"
        crumb="Article"
        :image="$post->coverUrl()"
        :compact="true"
    >
        <p data-reveal data-delay="450" class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold uppercase tracking-[0.16em] text-white/65">
            @if ($post->author)<span>By {{ $post->author->name }}</span><span aria-hidden="true">&middot;</span>@endif
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
            <span aria-hidden="true">&middot;</span><span>{{ $post->readingMinutes() }} min read</span>
        </p>
    </x-page-hero>

    <article class="bg-page py-10 md:py-16">
        <div class="mx-auto max-w-3xl px-5 sm:px-8">
            @if ($post->excerpt)
                <p data-reveal class="display text-2xl leading-snug text-ink sm:text-4xl sm:leading-[1.15]">{{ $post->excerpt }}</p>
            @endif

            <x-prose data-reveal :html="$post->body" :class="'mt-10 text-[1.08rem] leading-[1.85] text-ink/85'.(! $post->excerpt ? ' prose-dropcap' : '')" />

            {{-- Share --}}
            <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-y border-ink/10 py-6">
                <p class="text-sm font-semibold text-ink">Found this useful? Share it.</p>
                <div class="flex gap-2">
                    <a href="https://wa.me/?text={{ rawurlencode($post->title.' '.$url) }}" target="_blank" rel="noopener" aria-label="Share on WhatsApp" class="icon-btn text-ink"><x-icon name="whatsapp" class="h-[1.15rem] w-[1.15rem]" /></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($url) }}" target="_blank" rel="noopener" aria-label="Share on Facebook" class="icon-btn text-ink"><x-icon name="facebook" class="h-[1.15rem] w-[1.15rem]" /></a>
                </div>
            </div>

            <a href="{{ route('blog.index') }}" class="group mt-8 inline-flex items-center gap-3 text-sm font-semibold text-ink">
                <span class="circle-link"><x-icon name="arrow" class="h-4 w-4 rotate-180" /></span> Back to all articles
            </a>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="bg-soft py-12 md:py-16">
            <div class="mx-auto max-w-7xl px-5 sm:px-8">
                <h2 data-split class="display text-4xl sm:text-6xl">Keep <span class="italic text-glow">reading.</span></h2>
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $i => $r)
                        <x-post-card :post="$r" data-reveal data-delay="{{ $i * 90 }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta-band title="Ready to make your move?" text="Book a free site inspection, or ask our team anything about buying with confidence." />

</x-layouts.public>
