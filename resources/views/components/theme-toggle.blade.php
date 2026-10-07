{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Light/dark switch. Behaviour lives in app.js (initTheme); icons swap in CSS.
--}}
<button type="button" data-theme-toggle aria-pressed="false" aria-label="Toggle light and dark mode" {{ $attributes->merge(['class' => 'theme-toggle icon-btn']) }}>
    <x-icon name="sun" class="i-sun h-[1.15rem] w-[1.15rem]" />
    <x-icon name="moon" class="i-moon h-[1.15rem] w-[1.15rem]" />
</button>
