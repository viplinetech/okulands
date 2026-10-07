{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Swipeable / draggable, scroll-snapping property rail with arrows and a progress hairline (see initRails).
--}}
@props(['properties'])

<div data-rail class="relative">
    <div class="rail -mx-5 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth px-5 pb-6 sm:-mx-8 sm:gap-6 sm:px-8">
        @foreach ($properties as $index => $property)
            <div data-card data-reveal data-delay="{{ $index * 90 }}" class="w-[80vw] shrink-0 snap-start sm:w-[22rem] lg:w-[24rem]">
                <x-property-card :property="$property" />
            </div>
        @endforeach
    </div>

    <div class="mt-4 flex items-center gap-6">
        <div class="h-px flex-1 bg-ink/10">
            <div data-rail-progress class="h-px origin-left bg-ink transition-transform duration-300" style="transform: scaleX(0.12)"></div>
        </div>
        <div class="hidden gap-2 sm:flex">
            <button type="button" data-rail-dir="-1" class="flex h-12 w-12 items-center justify-center rounded-full border border-ink/20 text-ink transition hover:bg-ink hover:text-page" aria-label="Previous">
                <x-icon name="arrow" class="h-4 w-4 rotate-180" />
            </button>
            <button type="button" data-rail-dir="1" class="flex h-12 w-12 items-center justify-center rounded-full border border-ink/20 text-ink transition hover:bg-ink hover:text-page" aria-label="Next">
                <x-icon name="arrow" class="h-4 w-4" />
            </button>
        </div>
    </div>
</div>
