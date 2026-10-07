<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
-->
@php
    $selected = array_key_exists((string) request('interest'), $interests) ? request('interest') : null;
    $tel = $settings->phone ? preg_replace('/\s+/', '', $settings->phone) : null;
    $mapsSearch = $settings->address ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($settings->address) : null;
    $embed = $settings->map_embed_url && str_starts_with($settings->map_embed_url, 'https://') ? $settings->map_embed_url : null;
    $cards = array_filter([
        $tel ? ['phone', 'Call us', $settings->phone, 'tel:'.$tel] : null,
        $settings->whatsapp ? ['whatsapp', 'WhatsApp', 'Chat with our team', $settings->whatsappUrl('Hello Oku Lands, I would like to make an enquiry.')] : null,
        $settings->email ? ['mail', 'Email', $settings->email, 'mailto:'.$settings->email] : null,
        $settings->office_hours ? ['clock', 'Office hours', $settings->office_hours, null] : null,
    ]);
@endphp
<x-layouts.public title="Contact" description="Contact Oku Lands & Properties: book a free site inspection, ask about a property or speak to our team." :image="$settings->bannerUrl('contact')">

    <x-page-hero
        :eyebrow="c('contact.hero.eyebrow')"
        crumb="Contact"
        :title="c('contact.hero.title')"
        :accent="c('contact.hero.accent')"
        :subtitle="c('contact.hero.subtitle')"
        :image="$settings->bannerUrl('contact')"
    />

    <section class="bg-page py-12 md:py-16">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-5 sm:px-8 lg:grid-cols-12 lg:gap-14">

            {{-- Form --}}
            <div class="lg:col-span-7">
                <div data-reveal class="surface shadow-soft p-6 sm:p-10">
                    <h2 class="display text-4xl text-ink sm:text-5xl">{{ c('contact.form.title') }}</h2>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-mute">{{ c('contact.form.text') }}</p>
                    <div class="mt-8">
                        <x-enquiry-form :interests="$interests" :selected="$selected" />
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="space-y-4 lg:col-span-5">
                @foreach ($cards as $i => [$icon, $label, $value, $href])
                    @if ($href)
                        <a href="{{ $href }}" @if (str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif data-reveal data-delay="{{ $i * 80 }}" class="spot surface group flex items-center gap-5 p-5 transition duration-500 hover:-translate-y-0.5 sm:p-6">
                    @else
                        <div data-reveal data-delay="{{ $i * 80 }}" class="surface flex items-center gap-5 p-5 sm:p-6">
                    @endif
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon :name="$icon" class="h-5 w-5" /></span>
                        <div class="min-w-0">
                            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-mute">{{ $label }}</p>
                            <p class="mt-1 break-words font-semibold text-ink">{{ $value }}</p>
                        </div>
                    @if ($href) </a> @else </div> @endif
                @endforeach

                @if ($settings->address)
                    <div data-reveal data-delay="240" class="surface overflow-hidden">
                        @if ($embed)
                            <iframe src="{{ $embed }}" title="Map to our office" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="h-64 w-full border-0 sm:h-72"></iframe>
                        @endif
                        <div class="flex items-start gap-4 p-5 sm:p-6">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon name="pin" class="h-5 w-5" /></span>
                            <div class="min-w-0">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-mute">Visit our office</p>
                                <p class="mt-1 text-sm leading-relaxed text-ink">{{ $settings->address }}</p>
                                <a href="{{ $mapsSearch }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-brand hover:underline">Get directions <x-icon name="arrow" class="h-4 w-4" /></a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

</x-layouts.public>
