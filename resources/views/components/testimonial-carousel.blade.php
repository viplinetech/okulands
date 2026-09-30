{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
--}}
@props(['testimonials'])

<div
    x-data="{
        items: {{ $testimonials->count() }},
        active: 0,
        timer: null,
        start() { this.timer = setInterval(() => this.active = (this.active + 1) % this.items, 6000); }
    }"
    x-init="start()"
    class="relative"
>
    <div class="overflow-hidden">
        <div class="flex transition-transform duration-700 ease-out" :style="'transform: translateX(-' + (active * 100) + '%)'">
            @foreach ($testimonials as $t)
                <div class="w-full shrink-0 px-2">
                    <div class="mx-auto max-w-2xl rounded-3xl border border-navy-100 bg-navy-50 p-10 text-center shadow-premium dark:border-white/10 dark:bg-navy-900">
                        <div class="flex justify-center gap-1 text-gold-500">
                            @for ($i = 0; $i < $t->rating; $i++)
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.6l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8Z"/></svg>
                            @endfor
                        </div>
                        <p class="mt-5 font-serif text-lg italic leading-relaxed text-navy-700 dark:text-white/80">&ldquo;{{ $t->content }}&rdquo;</p>
                        <p class="mt-6 font-bold text-navy-900 dark:text-white">{{ $t->client_name }}</p>
                        @if ($t->client_role)
                            <p class="text-xs uppercase tracking-wider text-navy-500 dark:text-white/50">{{ $t->client_role }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-8 flex justify-center gap-2">
        @foreach ($testimonials as $i => $t)
            <button
                type="button"
                @click="active = {{ $i }}"
                class="h-2.5 rounded-full transition-all duration-300"
                :class="active === {{ $i }} ? 'w-8 bg-gold-500' : 'w-2.5 bg-navy-200 dark:bg-white/20'"
                aria-label="Go to testimonial {{ $i + 1 }}"
            ></button>
        @endforeach
    </div>
</div>
