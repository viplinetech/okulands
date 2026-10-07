{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Blog post card used on the Blog page, the home preview and "keep reading".
--}}
@props(['post'])

<a href="{{ route('blog.show', $post->slug) }}" {{ $attributes->merge(['class' => 'group flex h-full flex-col']) }}>
    <div class="relative aspect-[16/11] overflow-hidden rounded-3xl bg-soft">
        <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}" data-fit class="h-full w-full object-cover transition duration-[1400ms] ease-out group-hover:scale-105" loading="lazy" width="640" height="440">
        @if ($post->category)
            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3.5 py-1.5 text-[0.65rem] font-bold uppercase tracking-[0.14em] text-navy-900 backdrop-blur">{{ $post->category }}</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col px-1.5 pt-5">
        <p class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.16em] text-mute">
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('M j, Y') }}</time>
            <span aria-hidden="true">&middot;</span>
            <span>{{ $post->readingMinutes() }} min read</span>
        </p>
        <h3 class="mt-3 font-serif text-2xl leading-tight text-ink transition group-hover:text-brand sm:text-[1.7rem]">{{ $post->title }}</h3>
        <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-mute">{{ $post->summary() }}</p>
        <span class="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-semibold text-ink">Read article <x-icon name="arrow" class="h-4 w-4 transition group-hover:translate-x-1" /></span>
    </div>
</a>
