{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
--}}
@props(['image' => null, 'label' => '500+ Available Listings'])

<div class="relative">
    <div class="aspect-[4/5] overflow-hidden rounded-lg border border-navy-900/10 bg-navy-100 dark:border-white/10 dark:bg-navy-800">
        <img
            src="{{ $image ?? 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80' }}"
            alt="Oku Lands & Properties development"
            class="h-full w-full object-cover"
            width="600" height="750"
            loading="lazy"
        >
    </div>

    <div class="absolute -bottom-6 -right-6 rounded-md border border-gold-400/60 bg-white px-6 py-4 shadow-premium dark:bg-navy-900">
        <p class="font-serif text-xl font-semibold text-navy-900 dark:text-white">{{ $label }}</p>
    </div>
</div>
