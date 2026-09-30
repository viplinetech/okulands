{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)

    Split hero: headline + property search on the left, a single admin-uploaded
    photo on the right (framed, with floating trust/credential chips). Falls
    back to a soft branded pattern card when no photo has been uploaded yet.
--}}
@props(['images' => []])

<section class="relative overflow-hidden bg-gradient-to-br from-navy-50 via-white to-gold-50/40 pb-16 pt-12 dark:from-navy-950 dark:via-navy-950 dark:to-navy-900 sm:pb-24 sm:pt-16">
    <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-gold-300/20 blur-[100px] animate-floaty dark:bg-gold-500/10"></div>
    <div class="absolute -right-20 top-40 h-80 w-80 rounded-full bg-navy-300/20 blur-[100px] animate-floaty dark:bg-blue-500/10" style="animation-delay:2s"></div>

    <div class="relative mx-auto grid max-w-7xl grid-cols-1 items-center gap-14 px-5 sm:px-8 lg:grid-cols-2 lg:gap-10">

        {{-- Left: headline + search --}}
        <div>
            <span data-reveal="fade" class="inline-flex items-center rounded-full border border-gold-400/50 bg-gold-50 px-5 py-2 text-xs font-bold uppercase tracking-[0.2em] text-gold-700 dark:border-gold-400/30 dark:bg-white/5 dark:text-gold-400">
                Real Estate &middot; Construction &middot; Agriculture
            </span>

            <h1 class="mt-7 font-serif text-4xl font-extrabold leading-[1.1] text-navy-900 dark:text-white sm:text-5xl lg:text-6xl">
                <span class="word-reveal block">
                    @foreach (['Building', 'Legacies,'] as $i => $word)
                        <span style="animation-delay:{{ 0.1 + $i * 0.12 }}s">{{ $word }}</span>@if(!$loop->last)&nbsp;@endif
                    @endforeach
                </span>
                <span class="word-reveal mt-1 block bg-gradient-to-r from-gold-500 via-gold-600 to-gold-500 bg-clip-text text-transparent text-shimmer">
                    @foreach (['One', 'Property', 'at', 'a', 'Time.'] as $i => $word)
                        <span style="animation-delay:{{ 0.4 + $i * 0.08 }}s">{{ $word }}</span>@if(!$loop->last)&nbsp;@endif
                    @endforeach
                </span>
            </h1>

            <p data-reveal data-reveal-delay="450" class="mt-6 max-w-lg text-base leading-relaxed text-navy-600 dark:text-white/70 sm:text-lg">
                Your trusted partner in real estate, construction and agriculture, helping you secure
                verified land, build with confidence, and grow lasting wealth across Nigeria.
            </p>

            {{-- Search bar --}}
            <form data-reveal data-reveal-delay="600" action="{{ url('/properties') }}" method="GET" class="mt-9 flex flex-col gap-3 rounded-2xl border border-navy-100 bg-white p-3 shadow-premium-lg dark:border-white/10 dark:bg-navy-900 sm:flex-row sm:items-center">
                <div class="flex flex-1 items-center gap-2 px-2">
                    <svg class="h-5 w-5 shrink-0 text-gold-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3"/></svg>
                    <input type="text" name="q" placeholder="Enter Location or City" class="w-full border-0 bg-transparent p-2 text-sm text-navy-900 placeholder:text-navy-400 focus:outline-none focus:ring-0 dark:text-white dark:placeholder:text-white/40">
                </div>
                <div class="hidden h-8 w-px bg-navy-100 dark:bg-white/10 sm:block"></div>
                <div class="flex flex-1 items-center gap-2 px-2">
                    <svg class="h-5 w-5 shrink-0 text-gold-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <select name="sector" class="w-full border-0 bg-transparent p-2 text-sm text-navy-900 focus:outline-none focus:ring-0 dark:text-white">
                        <option value="">Any Sector</option>
                        <option value="real_estate">Real Estate</option>
                        <option value="construction">Construction</option>
                        <option value="agriculture">Agriculture</option>
                    </select>
                </div>
                <button type="submit" class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-gold-500 to-gold-600 px-6 py-3.5 text-sm font-bold text-navy-950 shadow-premium transition hover:-translate-y-0.5 hover:shadow-premium-lg">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3" stroke-linecap="round"/></svg>
                    Search
                </button>
            </form>

            <p data-reveal data-reveal-delay="700" class="mt-4 text-sm text-navy-500 dark:text-white/50">
                Input your preferred location or city and
                <a href="{{ url('/properties') }}" class="font-semibold text-gold-600 underline decoration-gold-300 underline-offset-4 hover:text-gold-700 dark:text-gold-400">Explore all Properties &rarr;</a>
            </p>
        </div>

        {{-- Right: single framed image with floating chips --}}
        <div data-reveal="right" class="relative">
            <div class="relative overflow-hidden rounded-[2rem] border border-white/60 bg-navy-100 shadow-premium-lg dark:border-white/10 dark:bg-navy-900">
                <div class="aspect-[4/5] sm:aspect-[5/4]">
                    @if (!empty($images[0] ?? null))
                        <img src="{{ $images[0] }}" alt="Oku Lands & Properties" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(212,175,55,.25),transparent_50%),radial-gradient(circle_at_80%_80%,rgba(42,69,147,.3),transparent_50%),linear-gradient(160deg,#101c44_0%,#15265a_100%)]">
                            <x-brand-mark class="h-28 w-28 opacity-90" />
                        </div>
                    @endif
                </div>
            </div>

            {{-- Floating chip: multi-sector credential --}}
            <div class="absolute -left-6 top-8 flex items-center gap-3 rounded-2xl border border-navy-100 bg-white/95 px-4 py-3 shadow-premium backdrop-blur-sm dark:border-white/10 dark:bg-navy-900/95 sm:-left-10">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-navy-900 text-gold-400 dark:bg-gold-500 dark:text-navy-950">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21h18M6 21V9l6-4 6 4v12M10 21v-6h4v6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-navy-900 dark:text-white">We Build Too</p>
                    <p class="text-xs text-navy-500 dark:text-white/50">Full construction services</p>
                </div>
            </div>

            {{-- Floating chip: trust --}}
            <div class="absolute -bottom-6 -right-4 flex items-center gap-3 rounded-2xl border border-navy-100 bg-white/95 px-4 py-3 shadow-premium backdrop-blur-sm dark:border-white/10 dark:bg-navy-900/95 sm:-right-8">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold-500 text-navy-950">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7 7a1 1 0 0 1-1.4 0l-3-3a1 1 0 1 1 1.4-1.4L9 11.6l6.3-6.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-navy-900 dark:text-white">Verified Titles</p>
                    <p class="text-xs text-navy-500 dark:text-white/50">Every deal, secured</p>
                </div>
            </div>
        </div>
    </div>
</section>
