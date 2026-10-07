{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Large-format quote slider (see initTestimonials). Swipeable on touch screens.
--}}
@props(['testimonials'])

<div data-quotes class="mx-auto max-w-4xl text-center">
    <div class="grid">
        @foreach ($testimonials as $t)
            <figure data-quote class="col-start-1 row-start-1 transition duration-[900ms] ease-out {{ $loop->first ? '' : 'opacity-0' }}">
                <div class="flex justify-center gap-1 text-brand">
                    @for ($i = 0; $i < $t->rating; $i++)
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 1.6l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8Z"/></svg>
                    @endfor
                </div>
                <blockquote class="display mt-7 text-[1.7rem] text-ink sm:text-5xl sm:leading-[1.08]">&ldquo;{{ $t->content }}&rdquo;</blockquote>
                <figcaption class="mt-9 flex flex-col items-center gap-3">
                    @if ($t->avatar)
                        <img src="{{ \App\Models\SiteSetting::media($t->avatar) }}" alt="" class="h-12 w-12 rounded-full object-cover" loading="lazy" width="48" height="48">
                    @endif
                    <div>
                        <p class="text-sm font-bold text-ink">{{ $t->client_name }}</p>
                        @if ($t->client_role)
                            <p class="mt-1 text-xs font-medium uppercase tracking-[0.2em] text-mute">{{ $t->client_role }}</p>
                        @endif
                    </div>
                </figcaption>
            </figure>
        @endforeach
    </div>

    @if ($testimonials->count() > 1)
        <div class="mt-10 flex justify-center gap-2">
            @foreach ($testimonials as $i => $t)
                <button type="button" data-quote-dot class="h-2 rounded-full transition-all duration-500" style="width: {{ $i === 0 ? '2.5rem' : '0.5rem' }}; background: {{ $i === 0 ? 'rgb(var(--brand))' : 'rgb(var(--ink) / 0.2)' }}" aria-label="Show testimonial {{ $i + 1 }}"></button>
            @endforeach
        </div>
    @endif
</div>
