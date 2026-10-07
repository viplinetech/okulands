<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
<x-layouts.public title="Page not found" :hide-fab="true">
    <section class="grain relative isolate flex min-h-[80svh] items-center overflow-hidden bg-navy-950 pt-28 text-white">
        <div class="aurora absolute -left-24 top-1/4 h-[28rem] w-[28rem] rounded-full bg-sky-500/25 blur-[120px]"></div>
        <div class="relative mx-auto max-w-3xl px-5 py-20 text-center sm:px-8">
            <p class="eyebrow text-sky-300">Error 404</p>
            <h1 class="display mt-6 text-[clamp(3.4rem,11vw,8rem)]">Lost your <span class="italic text-shine-inv">way?</span></h1>
            <p class="mx-auto mt-6 max-w-md text-white/70">The page you are looking for has moved or no longer exists. Let&rsquo;s get you back on track.</p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}" class="btn btn-sky">Back to home</a>
                <a href="{{ route('properties.index') }}" class="btn btn-ghost">Browse properties</a>
            </div>
        </div>
    </section>
</x-layouts.public>
