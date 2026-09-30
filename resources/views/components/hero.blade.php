{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)

    Premium hero: crossfades through admin-uploaded images (Site Settings ->
    Hero Images). If none are uploaded yet, falls back to an animated
    gradient/blueprint backdrop so the section never looks empty.
--}}
@props(['images' => []])

<section
    x-data="{
        images: {{ Illuminate\Support\Js::from($images) }},
        active: 0,
        init() {
            if (this.images.length > 1) {
                setInterval(() => { this.active = (this.active + 1) % this.images.length; }, 6000);
            }
        }
    }"
    class="relative flex min-h-[92vh] items-center overflow-hidden bg-navy-950"
>
    {{-- Fallback animated backdrop (always present, visible behind/without photos) --}}
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(212,175,55,.18),transparent_45%),radial-gradient(circle_at_85%_15%,rgba(47,111,237,.28),transparent_40%),radial-gradient(circle_at_50%_100%,rgba(46,139,87,.2),transparent_50%),linear-gradient(160deg,#0a1330_0%,#101c44_45%,#15265a_100%)]"></div>
        <div class="absolute inset-0 opacity-[0.07] [background-image:linear-gradient(rgba(255,255,255,.4)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.4)_1px,transparent_1px)] [background-size:48px_48px]"></div>
    </div>

    {{-- Admin-uploaded photo crossfade --}}
    <template x-if="images.length > 0">
        <div class="absolute inset-0">
            <template x-for="(img, i) in images" :key="i">
                <div
                    class="absolute inset-0 bg-cover bg-center transition-opacity duration-[1800ms] ease-in-out"
                    :style="'background-image:url(' + img + ')'"
                    :class="active === i ? 'opacity-100' : 'opacity-0'"
                ></div>
            </template>
            <div class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/60 to-navy-950/20"></div>
            <div class="absolute inset-0 bg-navy-950/30"></div>
        </div>
    </template>

    {{-- Content --}}
    <div class="relative z-10 mx-auto w-full max-w-7xl px-5 py-28 sm:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <span class="inline-flex items-center rounded-full border border-gold-400/40 bg-white/5 px-5 py-2 text-xs font-bold uppercase tracking-[0.2em] text-gold-400 backdrop-blur-sm">
                Real Estate &middot; Construction &middot; Agriculture
            </span>

            <h1 class="mt-8 font-serif text-4xl font-extrabold leading-[1.08] text-white sm:text-5xl lg:text-6xl">
                Buy Today,
                <span class="block bg-gradient-to-r from-gold-300 via-gold-400 to-gold-300 bg-clip-text text-transparent">
                    Build Tomorrow.
                </span>
            </h1>

            <p class="mx-auto mt-6 max-w-xl text-base leading-relaxed text-white/75 sm:text-lg">
                Verified land and property opportunities across Nigeria, backed by a company built on trust,
                transparency and a realtor network that rewards every referral.
            </p>

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ url('/properties') }}" class="w-full rounded-full bg-gradient-to-r from-gold-400 to-gold-600 px-8 py-4 text-center text-sm font-bold text-navy-950 shadow-premium-lg transition hover:-translate-y-1 hover:shadow-premium-lg sm:w-auto">
                    Explore Properties
                </a>
                <a href="{{ url('/contact') }}" class="w-full rounded-full border border-white/30 bg-white/5 px-8 py-4 text-center text-sm font-bold text-white backdrop-blur-sm transition hover:border-white/60 hover:bg-white/10 sm:w-auto">
                    Book a Free Inspection
                </a>
            </div>
        </div>

        {{-- Trust stat strip --}}
        <div class="mx-auto mt-20 grid max-w-4xl grid-cols-2 gap-6 sm:grid-cols-4">
            @foreach ([
                ['value' => '500+', 'label' => 'Properties Sold'],
                ['value' => '1,200+', 'label' => 'Happy Clients'],
                ['value' => '150+', 'label' => 'Active Realtors'],
                ['value' => '10+', 'label' => 'Years of Trust'],
            ] as $stat)
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-center backdrop-blur-sm">
                    <div class="font-serif text-2xl font-extrabold text-white sm:text-3xl">{{ $stat['value'] }}</div>
                    <div class="mt-1 text-xs uppercase tracking-wider text-white/60">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Scroll cue --}}
    <div class="absolute bottom-8 left-1/2 z-10 -translate-x-1/2 animate-bounce text-white/50">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 5v14m0 0-6-6m6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
</section>
