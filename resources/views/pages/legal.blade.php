<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Privacy Policy / Terms of Use. Wording is admin-editable (Page content > Legal pages).
-->
<x-layouts.public :title="$title" :description="$summary" :image="$settings->bannerUrl('about')">

    <x-page-hero eyebrow="Legal" :crumb="$title" :title="$title" :subtitle="$summary" :image="$settings->bannerUrl('about')" :compact="true" />

    <article class="bg-page py-10 md:py-16">
        <div class="mx-auto max-w-3xl px-5 sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-mute">Last updated: {{ c('legal.updated') }}</p>
            <x-prose :html="c($key)" class="mt-6 text-[1.02rem] leading-[1.8] text-ink/85" />

            <div class="mt-12 flex flex-wrap items-center gap-3 border-t border-ink/10 pt-8 text-sm">
                <span class="text-mute">Questions about this page?</span>
                <a href="{{ route('contact') }}" class="font-semibold text-brand hover:underline">Contact Oku Lands</a>
            </div>
        </div>
    </article>

</x-layouts.public>
