{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Closing call-to-action panel shown above the footer on inner pages.
--}}
@props(['title' => null, 'text' => null, 'image' => null, 'button1' => null, 'href1' => null, 'button2' => null, 'href2' => null])

@php
    $b1 = $button1 ?: c('cta.button1');
    $u1 = $href1 ?: route('contact', ['interest' => 'inspection']);
    $b2 = $button2 ?: c('cta.button2');
    $u2 = $href2 ?: route('register');
@endphp

<section class="px-5 py-8 sm:px-8 md:py-10">
    <div data-reveal="scale" class="grain hairline relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-navy-950 px-6 py-12 text-center text-white sm:px-14 md:rounded-[2.5rem] md:py-16">
        @if ($image)
            <img src="{{ $image }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-25" loading="lazy">
        @endif
        <div class="aurora absolute left-1/2 top-0 h-80 w-80 -translate-x-1/2 rounded-full bg-sky-500/30 blur-[100px]"></div>
        <div class="relative">
            <h2 class="display mx-auto max-w-3xl text-4xl sm:text-6xl">{{ $title ?: c('cta.title') }}</h2>
            <p class="mx-auto mt-5 max-w-lg text-white/65">{{ $text ?: c('cta.text') }}</p>
            <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ $u1 }}" data-magnetic class="btn btn-sky w-full sm:w-auto">{{ $b1 }}</a>
                <a href="{{ $u2 }}" data-magnetic class="btn btn-ghost w-full sm:w-auto">{{ $b2 }}</a>
            </div>
        </div>
    </div>
</section>
