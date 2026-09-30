{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)

    Cinematic hero: Ken Burns crossfade through admin-uploaded images, word-by-word
    headline reveal, and a floating glass search card straddling the hero's
    bottom edge (the signature pattern used by premium real-estate brands).
    Falls back to an animated gradient/blueprint backdrop when no photos exist yet.
--}}
@props(['images' => []])

<section
    x-data="{
        images: {{ Illuminate\Support\Js::from($images) }},
        active: 0,
        init() {
            if (this.images.length > 1) {
                setInterval(() => { this.active = (this.active + 1) % this.images.length; }, 7000);
            }
        }
    }"
    class="relative flex min-h-screen items-center overflow-hidden bg-navy-950"
>
    {{-- Fallback animated backdrop --}}
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(212,175,55,.2),transparent_45%),radial-gradient(circle_at_85%_15%,rgba(47,111,237,.3),transparent_40%),radial-gradient(circle_at_50%_100%,rgba(46,139,87,.22),transparent_50%),linear-gradient(160deg,#0a1330_0%,#101c44_45%,#15265a_100%)]"></div>
        <div class="absolute inset-0 opacity-[0.07] [background-image:linear-gradient(rgba(255,255,255,.4)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.4)_1px,transparent_1px)] [background-size:48px_48px]"></div>
        <div class="absolute -left-24 top-0 h-96 w-96 rounded-full bg-gold-500/20 blur-[100px] animate-floaty"></div>
        <div class="absolute -right-24 bottom-24 h-96 w-96 rounded-full bg-blue-500/20 blur-[100px] animate-floaty" style="animation-delay:2s"></div>
    </div>

    {{-- Admin-uploaded photo crossfade with slow Ken Burns zoom --}}
    <template x-if="images.length > 0">
        <div class="absolute inset-0">
            <template x-for="(img, i) in images" :key="i">
                <div
                    class="absolute inset-0 bg-cover bg-center transition-opacity duration-[2000ms] ease-in-out"
                    :class="active === i ? 'opacity-100 animate-kenburns' : 'opacity-0'"
                    :style="'background-image:url(' + img + ')'"
                ></div>
            </template>
            <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/50 to-navy-950/30"></div>
            <div class="absolute inset-0 bg-navy-950/25"></div>
        </div>
    </template>

    {{-- Content --}}
    <div class="relative z-10 mx-auto w-full max-w-7xl px-5 pb-20 pt-32 sm:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <span data-reveal="fade" class="inline-flex items-center rounded-full border border-gold-400/40 bg-white/5 px-5 py-2 text-xs font-bold uppercase tracking-[0.2em] text-gold-400 backdrop-blur-sm">
                Real Estate &middot; Construction &middot; Agriculture
            </span>

            <h1 class="mt-8 font-serif text-4xl font-extrabold leading-[1.08] text-white sm:text-5xl lg:text-7xl">
                <span class="word-reveal block" style="--i:0">
                    @foreach (['Buy', 'Today,'] as $i => $word)
                        <span style="animation-delay:{{ 0.15 + $i * 0.12 }}s">{{ $word }}</span>@if(!$loop->last)&nbsp;@endif
                    @endforeach
                </span>
                <span class="word-reveal mt-1 block bg-gradient-to-r from-gold-300 via-gold-400 to-gold-300 bg-clip-text text-transparent text-shimmer">
                    @foreach (['Build', 'Tomorrow.'] as $i => $word)
                        <span style="animation-delay:{{ 0.45 + $i * 0.12 }}s">{{ $word }}</span>@if(!$loop->last)&nbsp;@endif
                    @endforeach
                </span>
            </h1>

            <p data-reveal data-reveal-delay="500" class="mx-auto mt-6 max-w-xl text-base leading-relaxed text-white/75 sm:text-lg">
                Verified land and property opportunities across Nigeria, backed by a company built on trust,
                transparency and a realtor network that rewards every referral.
            </p>
        </div>
    </div>

    {{-- Floating glass search / CTA card, straddling the hero's bottom edge --}}
    <div data-reveal="scale" data-reveal-delay="650" class="absolute inset-x-0 -bottom-16 z-20 px-5 sm:px-8">
        <div class="mx-auto max-w-5xl rounded-3xl border border-white/15 bg-white/10 p-6 shadow-premium-lg backdrop-blur-xl sm:p-8">
            <form action="{{ url('/properties') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/70">Location or keyword</label>
                    <input type="text" name="q" placeholder="e.g. Amansea, Awka" class="w-full rounded-xl border-0 bg-white/90 px-4 py-3 text-sm text-navy-900 placeholder:text-navy-400 focus:ring-2 focus:ring-gold-400">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-white/70">Sector</label>
                    <select name="sector" class="w-full rounded-xl border-0 bg-white/90 px-4 py-3 text-sm text-navy-900 focus:ring-2 focus:ring-gold-400">
                        <option value="">All Sectors</option>
                        <option value="real_estate">Real Estate</option>
                        <option value="construction">Construction</option>
                        <option value="agriculture">Agriculture</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-gold-400 to-gold-600 px-6 py-3 text-sm font-bold text-navy-950 shadow-premium transition hover:-translate-y-0.5 hover:shadow-premium-lg">
                        Search Properties
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
