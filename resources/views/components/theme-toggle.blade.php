{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
--}}
<button
    type="button"
    x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('okulands-theme', this.dark ? 'dark' : 'light');
        }
    }"
    @click="toggle()"
    :aria-pressed="dark"
    aria-label="Toggle dark mode"
    class="relative inline-flex h-9 w-16 shrink-0 items-center rounded-full border border-navy-200 bg-navy-100 transition-colors duration-300 ease-out dark:border-navy-700 dark:bg-navy-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-400 focus-visible:ring-offset-2"
>
    <span
        class="absolute inset-y-1 left-1 flex h-7 w-7 items-center justify-center rounded-full bg-white shadow-premium transition-transform duration-300 ease-out dark:translate-x-7 dark:bg-navy-950"
        :class="dark ? 'translate-x-7' : 'translate-x-0'"
    >
        <svg x-show="!dark" x-transition.opacity class="h-4 w-4 text-gold-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4V2m0 20v-2m8-8h2M2 12h2m14.14-6.14 1.42-1.42M4.44 19.56l1.42-1.42M19.56 19.56l-1.42-1.42M4.44 4.44l1.42 1.42M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none"/></svg>
        <svg x-show="dark" x-transition.opacity class="h-4 w-4 text-blue-300" fill="currentColor" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z"/></svg>
    </span>
</button>
