{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)

    Runs synchronously in <head>, before any CSS paints, so the correct
    light/dark class is on <html> from the very first frame (no flash of
    the wrong theme). Site default is light; a visitor's own choice in
    localStorage always wins once they've toggled it.
--}}
<script>
    (function () {
        try {
            var stored = localStorage.getItem('okulands-theme');
            var theme = stored === 'dark' || stored === 'light' ? stored : '{{ $default ?? 'light' }}';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    })();
</script>
