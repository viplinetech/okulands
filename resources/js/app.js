/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 *
 * Lightweight scroll-reveal and count-up animation, no extra dependencies.
 * Elements opt in with [data-reveal] and [data-counter="targetNumber"].
 */

function initScrollReveal() {
    const revealEls = document.querySelectorAll('[data-reveal]');
    if (!revealEls.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const delay = entry.target.getAttribute('data-reveal-delay') || 0;
                setTimeout(() => entry.target.classList.add('is-revealed'), Number(delay));
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

    revealEls.forEach((el) => observer.observe(el));
}

function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;

    const animate = (el) => {
        const target = parseFloat(el.getAttribute('data-counter'));
        const suffix = el.getAttribute('data-counter-suffix') || '';
        const duration = 1600;
        const start = performance.now();

        function step(now) {
            const progress = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.floor(eased * target);
            el.textContent = value.toLocaleString() + suffix;
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString() + suffix;
        }
        requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animate(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    counters.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', () => {
    initScrollReveal();
    initCounters();
});
document.addEventListener('livewire:navigated', () => {
    initScrollReveal();
    initCounters();
});
