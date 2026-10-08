/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 *
 * Dependency-free interaction layer. Every module is opt-in via a data-*
 * attribute and fails safe: if anything here throws, the page is still
 * fully readable (see the `.js` gate in app.css). Heavier effects (parallax,
 * stacking, cursor effects) only run on desktop-class devices, so phones get
 * a calm, fast experience.
 */

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
const desktop = window.matchMedia('(min-width: 768px)');
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
const clamp = (v, min = 0, max = 1) => Math.min(max, Math.max(min, v));

/* ---------- Theme (light default; visitor choice persisted) ---------- */

function initTheme() {
    const root = document.documentElement;
    const sync = () => {
        const dark = root.classList.contains('dark');
        $$('[data-theme-toggle]').forEach((b) => b.setAttribute('aria-pressed', String(dark)));
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', dark ? '#060c1f' : '#F5F8FC');
    };
    $$('[data-theme-toggle]').forEach((btn) => {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', () => {
            root.classList.add('theme-anim');
            const dark = root.classList.toggle('dark');
            try { localStorage.setItem('okulands-theme', dark ? 'dark' : 'light'); } catch (e) {}
            sync();
            setTimeout(() => root.classList.remove('theme-anim'), 450);
        });
    });
    sync();
}

/* ---------- Reveal on scroll: [data-reveal] [data-unveil] [data-split] ---------- */

const SHINE = /\b(text-shine-inv|text-glow)\b/;

function initSplit() {
    $$('[data-split]').forEach((el) => {
        if (el.dataset.splitDone) return;
        el.dataset.splitDone = '1';
        let i = 0; // index of every word (drives the rise-in stagger)
        let g = 0; // index within the glowing phrase (drives the light's travel)
        // Gradient text can't follow a transformed child, so a wrapper carrying a
        // glow class hands it down to each individual word instead. `shine` is that
        // class name (or false). Each glowing word's --g staggers its light, so the
        // highlight travels across the phrase left to right, starting immediately.
        const walk = (node, shine) => {
            Array.from(node.childNodes).forEach((child) => {
                if (child.nodeType === 3) {
                    const frag = document.createDocumentFragment();
                    child.textContent.split(/(\s+)/).forEach((part) => {
                        if (!part) return;
                        if (/^\s+$/.test(part)) {
                            frag.appendChild(document.createTextNode(' '));
                        } else {
                            const w = document.createElement('span');
                            w.className = 'split-word';
                            const inner = document.createElement('span');
                            if (shine) {
                                inner.classList.add(shine);
                                inner.style.setProperty('--g', g++);
                            }
                            inner.style.setProperty('--i', i++);
                            inner.textContent = part;
                            w.appendChild(inner);
                            frag.appendChild(w);
                        }
                    });
                    child.replaceWith(frag);
                } else if (child.nodeType === 1 && child.tagName !== 'BR') {
                    const found = (child.className || '').match?.(SHINE)?.[1];
                    if (found) child.classList.remove(found);
                    walk(child, found || shine);
                }
            });
        };
        walk(el, false);
    });
}

function initReveal() {
    const els = $$('[data-reveal], [data-unveil], [data-split]');
    if (!els.length) return;
    if (reduceMotion || !('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-in'));
        return;
    }
    // A fully clip-path'd element has zero visible area and would never report
    // as intersecting, so [data-unveil] is observed through its parent instead.
    const targets = new Map();
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (!e.isIntersecting) return;
            (targets.get(e.target) || e.target).classList.add('is-in');
            io.unobserve(e.target);
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -6% 0px' });
    els.forEach((el) => {
        const d = el.getAttribute('data-delay');
        // Anything already on screen at load appears almost immediately (short delay cap), so a page
        // never feels like it is waiting; below-the-fold items keep their gentle stagger. Phones cap it too.
        const onScreen = el.getBoundingClientRect().top < window.innerHeight;
        const cap = onScreen ? 100 : (desktop.matches ? Infinity : 160);
        if (d) el.style.setProperty('--d', Math.min(Number(d), cap) + 'ms');
        const watch = el.hasAttribute('data-unveil') ? el.parentElement : el;
        if (watch !== el) targets.set(watch, el);
        io.observe(watch);
    });
}

/* ---------- Scroll-linked: progress bar, parallax, scrub text, stacked cards ---------- */

function initScrollEffects() {
    const bar = document.getElementById('progress');
    const parallax = $$('[data-parallax]');
    const scrubs = $$('[data-scrub]').map((el) => {
        const words = [];
        const walk = (node) => {
            Array.from(node.childNodes).forEach((child) => {
                if (child.nodeType === 3) {
                    const frag = document.createDocumentFragment();
                    child.textContent.split(/(\s+)/).forEach((part) => {
                        if (!part) return;
                        if (/^\s+$/.test(part)) return frag.appendChild(document.createTextNode(' '));
                        const s = document.createElement('span');
                        s.className = 'scrub-word';
                        s.textContent = part;
                        words.push(s);
                        frag.appendChild(s);
                    });
                    child.replaceWith(frag);
                } else if (child.nodeType === 1) {
                    walk(child);
                }
            });
        };
        walk(el);
        return { el, words };
    });
    const stacks = $$('[data-stack]');
    if (!bar && !parallax.length && !scrubs.length && !stacks.length) return;

    let ticking = false;
    const update = () => {
        ticking = false;
        const y = window.scrollY;
        const vh = window.innerHeight;
        const wide = desktop.matches && !reduceMotion;

        if (bar) {
            const max = document.documentElement.scrollHeight - vh;
            bar.style.transform = `scaleX(${max > 0 ? clamp(y / max) : 0})`;
        }

        parallax.forEach((el) => {
            if (!wide) {
                el.style.transform = '';
                return;
            }
            const r = el.parentElement.getBoundingClientRect();
            if (r.bottom < -200 || r.top > vh + 200) return;
            const speed = parseFloat(el.dataset.parallax) || 0.15;
            const offset = (r.top + r.height / 2 - vh / 2) * -speed;
            el.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0) scale(1.15)`;
        });

        stacks.forEach((card, i) => {
            const next = stacks[i + 1];
            const inner = card.firstElementChild;
            if (!next || !wide) {
                if (inner && !wide) { inner.style.transform = ''; inner.style.filter = ''; }
                return;
            }
            const nr = next.getBoundingClientRect();
            const p = clamp(1 - (nr.top - card.offsetHeight * 0.2) / (vh * 0.9));
            inner.style.transform = `scale(${(1 - p * 0.06).toFixed(4)})`;
            inner.style.filter = `brightness(${(1 - p * 0.45).toFixed(3)})`;
        });

        scrubs.forEach(({ el, words }) => {
            const r = el.getBoundingClientRect();
            const p = reduceMotion ? 1 : clamp((vh * 0.85 - r.top) / (r.height + vh * 0.35));
            const lit = Math.round(p * words.length);
            words.forEach((w, i) => w.classList.toggle('on', i < lit));
        });
    };

    const onScroll = () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
}

/* ---------- Header: glass on scroll, hides on scroll-down, mobile menu ---------- */

function initHeader() {
    const header = document.getElementById('site-header');
    if (!header) return;
    let last = window.scrollY;

    const onScroll = () => {
        const y = window.scrollY;
        header.classList.toggle('is-solid', y > 40);
        document.querySelector('.fab')?.classList.toggle('is-on', y > 520);
        const menuOpen = document.body.classList.contains('menu-open');
        header.classList.toggle('is-hidden', y > 500 && y > last + 6 && !menuOpen);
        if (y < last - 6) header.classList.remove('is-hidden');
        last = y;
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    const toggle = (open) => {
        document.body.classList.toggle('menu-open', open);
        document.body.style.overflow = open ? 'hidden' : '';
        $$('[data-menu-toggle]').forEach((b) => b.setAttribute('aria-expanded', String(open)));
        if (open) header.classList.remove('is-hidden');
    };
    $$('[data-menu-toggle]').forEach((b) => b.addEventListener('click', () => toggle(!document.body.classList.contains('menu-open'))));
    $$('.menu-panel a').forEach((a) => a.addEventListener('click', () => toggle(false)));
    document.addEventListener('keydown', (e) => e.key === 'Escape' && toggle(false));
    desktop.addEventListener?.('change', (e) => e.matches && toggle(false));
}

/* ---------- Micro-interactions (fine pointers only) ---------- */

function initSpotlight() {
    if (!finePointer) return;
    $$('.spot').forEach((el) => {
        el.addEventListener('pointermove', (e) => {
            const r = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${e.clientX - r.left}px`);
            el.style.setProperty('--my', `${e.clientY - r.top}px`);
        });
    });
}

function initMagnetic() {
    if (!finePointer || reduceMotion) return;
    $$('[data-magnetic]').forEach((el) => {
        el.addEventListener('pointermove', (e) => {
            const r = el.getBoundingClientRect();
            const x = (e.clientX - (r.left + r.width / 2)) * 0.2;
            const y = (e.clientY - (r.top + r.height / 2)) * 0.3;
            el.style.transform = `translate(${x}px, ${y}px)`;
        });
        el.addEventListener('pointerleave', () => {
            el.style.transition = 'transform 0.7s cubic-bezier(0.16, 1, 0.3, 1)';
            el.style.transform = '';
            setTimeout(() => (el.style.transition = ''), 700);
        });
    });
}

/* ---------- Counters ---------- */

function initCounters() {
    const els = $$('[data-counter]');
    if (!els.length) return;
    const run = (el) => {
        const target = parseFloat(el.dataset.counter);
        const suffix = el.dataset.suffix || '';
        if (reduceMotion) {
            el.textContent = target.toLocaleString() + suffix;
            return;
        }
        const t0 = performance.now();
        const step = (now) => {
            const p = clamp((now - t0) / 1800);
            const eased = 1 - Math.pow(1 - p, 4);
            el.textContent = Math.floor(eased * target).toLocaleString() + (p < 1 ? '' : suffix);
            if (p < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString() + suffix;
        };
        requestAnimationFrame(step);
    };
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                run(e.target);
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.5 });
    els.forEach((el) => io.observe(el));
}

/* ---------- Hero slideshow ---------- */

function initHeroSlides() {
    const slides = $$('.hero-slide');
    if (slides.length < 2 || reduceMotion) return;
    let i = 0;
    setInterval(() => {
        if (document.hidden) return;
        slides[i].classList.remove('is-active');
        i = (i + 1) % slides.length;
        slides[i].classList.add('is-active');
    }, 7000);
}

/* ---------- Generic crossfade: [data-crossfade] with .xf-slide children (+ optional dots) ---------- */

function initCrossfade() {
    $$('[data-crossfade]').forEach((box) => {
        const slides = $$('.xf-slide', box);
        if (slides.length < 2 || reduceMotion) return;
        const dots = $$('.xf-dot', box.parentElement);
        let i = 0;
        setInterval(() => {
            if (document.hidden) return;
            slides[i].classList.remove('is-active');
            dots[i]?.classList.remove('is-active');
            i = (i + 1) % slides.length;
            slides[i].classList.add('is-active');
            dots[i]?.classList.add('is-active');
        }, 5500);
    });
}

/* ---------- Draggable rails with progress + arrows ---------- */

function initRails() {
    $$('[data-rail]').forEach((wrap) => {
        const rail = wrap.querySelector('.rail');
        const fill = wrap.querySelector('[data-rail-progress]');
        if (!rail) return;

        const sync = () => {
            const max = rail.scrollWidth - rail.clientWidth;
            const p = max > 0 ? rail.scrollLeft / max : 1;
            if (fill) fill.style.transform = `scaleX(${clamp(0.12 + p * 0.88)})`;
        };
        rail.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync, { passive: true });
        sync();

        wrap.querySelectorAll('[data-rail-dir]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const card = rail.querySelector('[data-card]');
                const step = card ? card.offsetWidth + 24 : 360;
                rail.scrollBy({ left: Number(btn.dataset.railDir) * step, behavior: 'smooth' });
            });
        });

        if (!finePointer) return;
        let down = false, sx = 0, sl = 0, moved = false;
        rail.addEventListener('pointerdown', (e) => {
            down = true; moved = false; sx = e.clientX; sl = rail.scrollLeft;
        });
        window.addEventListener('pointermove', (e) => {
            if (!down) return;
            const dx = e.clientX - sx;
            if (Math.abs(dx) > 4) { moved = true; rail.classList.add('is-dragging'); }
            if (moved) rail.scrollLeft = sl - dx;
        });
        window.addEventListener('pointerup', () => {
            if (!down) return;
            down = false;
            rail.classList.remove('is-dragging');
        });
    });
}

/* ---------- Accordion ---------- */

function initAccordions() {
    $$('.acc-item').forEach((item) => {
        const btn = item.querySelector('button');
        btn.addEventListener('click', () => {
            const open = !item.classList.contains('is-open');
            item.parentElement.querySelectorAll('.acc-item.is-open').forEach((o) => {
                o.classList.remove('is-open');
                o.querySelector('button').setAttribute('aria-expanded', 'false');
            });
            item.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', String(open));
        });
    });
}

/* ---------- Testimonials ---------- */

function initTestimonials() {
    $$('[data-quotes]').forEach((wrap) => {
        const items = $$('[data-quote]', wrap);
        const dots = $$('[data-quote-dot]', wrap);
        if (items.length < 2) return;
        let i = 0, timer;
        const show = (n) => {
            i = (n + items.length) % items.length;
            items.forEach((el, k) => {
                const on = k === i;
                el.style.opacity = on ? '1' : '0';
                el.style.transform = on ? 'none' : 'translateY(14px)';
                el.style.pointerEvents = on ? 'auto' : 'none';
                el.setAttribute('aria-hidden', String(!on));
            });
            dots.forEach((d, k) => {
                d.style.width = k === i ? '2.5rem' : '0.5rem';
                d.style.background = k === i ? 'rgb(var(--brand))' : 'rgb(var(--ink) / 0.2)';
            });
        };
        const start = () => { if (!reduceMotion) timer = setInterval(() => show(i + 1), 7000); };
        dots.forEach((d, k) => d.addEventListener('click', () => { clearInterval(timer); show(k); start(); }));
        wrap.addEventListener('pointerenter', () => clearInterval(timer));
        wrap.addEventListener('pointerleave', () => { clearInterval(timer); start(); });
        // Swipe on touch screens
        let sx = null;
        wrap.addEventListener('touchstart', (e) => { sx = e.touches[0].clientX; }, { passive: true });
        wrap.addEventListener('touchend', (e) => {
            if (sx === null) return;
            const dx = e.changedTouches[0].clientX - sx;
            if (Math.abs(dx) > 50) { clearInterval(timer); show(i + (dx < 0 ? 1 : -1)); start(); }
            sx = null;
        });
        show(0);
        start();
    });
}

/* ---------- Gallery: category filter ---------- */

function initGalleryFilter() {
    $$('[data-gallery]').forEach((gallery) => {
        const buttons = $$('[data-filter]', gallery.parentElement);
        const items = $$('.g-item', gallery);
        const count = document.querySelector('[data-gallery-count]');
        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const cat = btn.dataset.filter;
                buttons.forEach((b) => {
                    const on = b === btn;
                    b.setAttribute('aria-pressed', String(on));
                    b.classList.toggle('bg-ink', on);
                    b.classList.toggle('text-page', on);
                    b.classList.toggle('border-ink', on);
                });
                let shown = 0;
                items.forEach((it) => {
                    const match = cat === 'all' || it.dataset.cat === cat;
                    it.hidden = !match;
                    it.classList.remove('pop');
                    if (match) {
                        it.classList.add('is-in');
                        it.style.setProperty('--d', Math.min(shown, 8) * 45 + 'ms');
                        void it.offsetWidth;
                        it.classList.add('pop');
                        shown++;
                    }
                });
                if (count) count.textContent = shown;
            });
        });
    });
}

/* ---------- Lightbox (gallery + property photos) ---------- */

function initLightbox() {
    const links = $$('a[data-lb]');
    if (!links.length) return;

    const box = document.createElement('div');
    box.className = 'lightbox';
    box.setAttribute('role', 'dialog');
    box.setAttribute('aria-modal', 'true');
    box.setAttribute('aria-label', 'Image viewer');
    box.innerHTML = `
        <div class="flex items-center justify-between px-4 py-3 text-white sm:px-8 sm:py-5">
            <p data-lb-count class="text-xs font-semibold uppercase tracking-[0.25em] text-white/60"></p>
            <button type="button" data-lb-close aria-label="Close" class="flex h-11 w-11 items-center justify-center rounded-full border border-white/25 transition hover:bg-white/10">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
            </button>
        </div>
        <div class="relative flex min-h-0 flex-1 items-center justify-center px-3 sm:px-20">
            <button type="button" data-lb-prev aria-label="Previous image" class="absolute left-2 z-10 flex h-12 w-12 items-center justify-center rounded-full border border-white/25 bg-white/5 text-white backdrop-blur transition hover:bg-white/15 sm:left-6">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <img data-lb-img alt="" class="max-h-full max-w-full rounded-xl object-contain">
            <button type="button" data-lb-next aria-label="Next image" class="absolute right-2 z-10 flex h-12 w-12 items-center justify-center rounded-full border border-white/25 bg-white/5 text-white backdrop-blur transition hover:bg-white/15 sm:right-6">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
        <p data-lb-caption class="px-6 py-4 text-center text-sm text-white/70 safe-bottom"></p>`;
    document.body.appendChild(box);

    const img = box.querySelector('[data-lb-img]');
    const cap = box.querySelector('[data-lb-caption]');
    const count = box.querySelector('[data-lb-count]');
    let group = [], idx = 0, opener = null;

    const visibleGroup = (name) => links.filter((l) => (l.dataset.lb || '') === name && !l.closest('[hidden]'));

    const show = (n, first = false) => {
        idx = (n + group.length) % group.length;
        const l = group[idx];
        const swap = () => {
            img.src = l.getAttribute('href');
            img.alt = l.dataset.caption || '';
            cap.textContent = l.dataset.caption || '';
            count.textContent = `${idx + 1} / ${group.length}`;
            img.classList.remove('is-swapping');
        };
        if (first || reduceMotion) swap();
        else { img.classList.add('is-swapping'); setTimeout(swap, 180); }
        // Warm the neighbours
        [idx + 1, idx - 1].forEach((k) => { const g = group[(k + group.length) % group.length]; if (g) new Image().src = g.getAttribute('href'); });
    };
    const open = (link) => {
        group = visibleGroup(link.dataset.lb);
        idx = group.indexOf(link);
        opener = link;
        show(idx, true);
        box.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        box.querySelector('[data-lb-close]').focus();
    };
    const close = () => {
        box.classList.remove('is-open');
        document.body.style.overflow = document.body.classList.contains('menu-open') ? 'hidden' : '';
        opener?.focus({ preventScroll: true });
    };

    links.forEach((l) => l.addEventListener('click', (e) => { e.preventDefault(); open(l); }));
    box.querySelector('[data-lb-close]').addEventListener('click', close);
    box.querySelector('[data-lb-prev]').addEventListener('click', () => show(idx - 1));
    box.querySelector('[data-lb-next]').addEventListener('click', () => show(idx + 1));
    box.addEventListener('click', (e) => { if (e.target === box || e.target.closest('.relative') === e.target) close(); });
    document.addEventListener('keydown', (e) => {
        if (!box.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowRight') show(idx + 1);
        if (e.key === 'ArrowLeft') show(idx - 1);
    });
    let sx = null;
    box.addEventListener('touchstart', (e) => { sx = e.touches[0].clientX; }, { passive: true });
    box.addEventListener('touchend', (e) => {
        if (sx === null) return;
        const dx = e.changedTouches[0].clientX - sx;
        if (Math.abs(dx) > 50) show(idx + (dx < 0 ? 1 : -1));
        sx = null;
    });
}

/* ---------- Property page: thumbnails swap the main photo ---------- */

function initPropertyGallery() {
    $$('[data-pgallery]').forEach((wrap) => {
        const main = wrap.querySelector('[data-main]');
        const mainLink = wrap.querySelector('[data-main-link]');
        $$('[data-thumb]', wrap).forEach((t) => {
            t.addEventListener('click', () => {
                main.classList.add('opacity-0');
                setTimeout(() => {
                    main.src = t.dataset.thumb;
                    if (mainLink) mainLink.setAttribute('href', t.dataset.full || t.dataset.thumb);
                    main.classList.remove('opacity-0');
                }, 180);
                $$('[data-thumb]', wrap).forEach((o) => o.classList.toggle('ring-2', o === t));
            });
        });
    });
}

/* ---------- Forms: auto-submit filters, friendly submit state ---------- */

function initForms() {
    $$('[data-autosubmit]').forEach((form) => {
        $$('select', form).forEach((s) => s.addEventListener('change', () => form.requestSubmit()));
    });
    $$('form[data-loading]').forEach((form) => {
        if (form.dataset.bound) return;
        form.dataset.bound = '1';
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type="submit"]');
            if (!btn || btn.disabled) return;
            btn.dataset.label = btn.innerHTML;
            btn.innerHTML = 'Sending&hellip;';
            btn.disabled = true;
            btn.classList.add('opacity-70');
        });
    });
}

/* ---------- FAQ knowledge base: instant search, topics, deep links, feedback ---------- */

function initFaqKb() {
    const root = document.querySelector('[data-faq-kb]');
    const input = document.getElementById('faq-search');
    if (!root || !input) return;

    const items = $$('[data-faq-item]', root);
    const groups = $$('[data-faq-group]', root);
    const topics = $$('[data-faq-cat]');
    const status = root.querySelector('[data-faq-status]');
    const empty = root.querySelector('[data-faq-empty]');
    const total = items.length;
    const esc = (t) => t.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const reEsc = (t) => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const originals = new Map(items.map((it) => [it, it.querySelector('.faq-q').textContent]));
    let topic = 'all';

    const setOpen = (it, open) => {
        it.classList.toggle('is-open', open);
        it.querySelector('button').setAttribute('aria-expanded', String(open));
    };

    const apply = () => {
        const words = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
        const re = words.length ? new RegExp('(' + words.map(reEsc).join('|') + ')', 'gi') : null;
        const visible = [];

        items.forEach((it) => {
            const show = (topic === 'all' || it.dataset.cat === topic) && words.every((w) => it.dataset.search.includes(w));
            it.hidden = !show;
            const q = it.querySelector('.faq-q');
            const base = originals.get(it);
            q.innerHTML = show && re ? base.split(re).map((part, i) => (i % 2 ? `<mark class="rounded bg-brand/20 px-0.5 text-inherit">${esc(part)}</mark>` : esc(part))).join('') : esc(base);
            if (show) visible.push(it);
        });

        groups.forEach((g) => { g.hidden = !$$('[data-faq-item]', g).some((i) => !i.hidden); });
        if (empty) empty.hidden = visible.length > 0;

        // With a search term, open the matches when there are only a few; otherwise collapse.
        items.forEach((it) => setOpen(it, words.length > 0 && visible.length <= 3 && visible.includes(it)));

        if (status) {
            status.textContent = words.length
                ? `${visible.length} ${visible.length === 1 ? 'answer' : 'answers'} for “${input.value.trim()}”`
                : topic === 'all' ? `Showing all ${total} answers` : `${visible.length} ${visible.length === 1 ? 'answer' : 'answers'} in ${topic}`;
        }
    };

    input.addEventListener('input', apply);
    topics.forEach((btn) => btn.addEventListener('click', () => {
        topic = btn.dataset.faqCat;
        topics.forEach((b) => {
            const on = b === btn;
            b.setAttribute('aria-pressed', String(on));
            b.classList.toggle('bg-ink', on);
            b.classList.toggle('text-page', on);
            b.classList.toggle('border-ink', on);
        });
        apply();
    }));
    $$('[data-faq-suggest]').forEach((b) => b.addEventListener('click', () => {
        input.value = b.dataset.faqSuggest;
        apply();
        input.focus({ preventScroll: true });
        root.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    }));

    // Press "/" anywhere to jump to the search box.
    document.addEventListener('keydown', (e) => {
        const tag = (document.activeElement?.tagName || '').toLowerCase();
        if (e.key === '/' && !['input', 'textarea', 'select'].includes(tag)) {
            e.preventDefault();
            input.focus();
        }
    });

    // Deep link: /faqs#faq-12 opens that answer.
    const target = location.hash.startsWith('#faq-') ? document.getElementById(location.hash.slice(1)) : null;
    if (input.value.trim()) apply();
    if (target) {
        setOpen(target, true);
        setTimeout(() => target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' }), 300);
    }

    // Ask OkuLands Smart AI: shown when a search finds nothing, answers the visitor's typed
    // search directly, grounded in the site's own FAQ content (see FaqController::ask()).
    const askBtn = root.querySelector('[data-faq-ai-ask]');
    if (askBtn) {
        const answerBox = root.querySelector('[data-faq-ai-answer]');
        const answerText = root.querySelector('[data-faq-ai-text]');
        const label = askBtn.querySelector('span');
        const originalLabel = label?.textContent;

        askBtn.addEventListener('click', async () => {
            const question = input.value.trim();
            if (!question) { input.focus(); return; }

            askBtn.disabled = true;
            if (label) label.textContent = 'Thinking…';
            if (answerBox) answerBox.hidden = true;

            try {
                const res = await fetch(askBtn.dataset.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({ question }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'OkuLands Smart AI could not answer right now.');

                if (answerText) answerText.textContent = data.answer || '';
                if (answerBox) answerBox.hidden = false;
            } catch (e) {
                if (answerText) answerText.textContent = e.message || 'Something went wrong. Please try again, or contact our team below.';
                if (answerBox) answerBox.hidden = false;
            } finally {
                askBtn.disabled = false;
                if (label) label.textContent = originalLabel;
            }
        });
    }

    // "Was this helpful?" (one vote per answer per browser)
    let voted = {};
    try { voted = JSON.parse(localStorage.getItem('okulands-faq-votes') || '{}'); } catch (e) {}
    $$('[data-feedback]', root).forEach((box) => {
        const done = () => { $$('[data-vote]', box).forEach((b) => { b.hidden = true; }); box.querySelector('[data-thanks]').hidden = false; box.querySelector('span.font-semibold').hidden = true; };
        if (voted[box.dataset.id]) return done();
        $$('[data-vote]', box).forEach((btn) => btn.addEventListener('click', () => {
            done();
            voted[box.dataset.id] = 1;
            try { localStorage.setItem('okulands-faq-votes', JSON.stringify(voted)); } catch (e) {}
            fetch(box.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ helpful: btn.dataset.vote }),
            }).catch(() => {});
        }));
    });
}

/* ---------- Instant navigation fallback: prefetch a link's page on hover / touch ---------- */

function initPrefetch() {
    // Chromium already prerenders via the <script type="speculationrules"> block in the layout.
    if (window.HTMLScriptElement?.supports?.('speculationrules')) return;
    const seen = new Set();
    const skip = /^\/(ref\/|login|register|logout|dashboard|profile|forgot-password|reset-password)/;
    const prefetch = (a) => {
        if (!a || a.origin !== location.origin || a.target === '_blank' || a.hasAttribute('download')) return;
        const url = a.pathname + a.search;
        if (a.pathname === location.pathname && a.search === location.search) return;
        if (skip.test(a.pathname) || seen.has(url)) return;
        seen.add(url);
        const l = document.createElement('link');
        l.rel = 'prefetch';
        l.href = url;
        document.head.appendChild(l);
    };
    let timer;
    document.addEventListener('pointerover', (e) => {
        const a = e.target.closest?.('a[href]');
        if (!a) return;
        clearTimeout(timer);
        timer = setTimeout(() => prefetch(a), 65);
    }, { passive: true });
    document.addEventListener('pointerout', () => clearTimeout(timer), { passive: true });
    document.addEventListener('touchstart', (e) => prefetch(e.target.closest?.('a[href]')), { passive: true });
}

/* ---------- Zoom lock: no pinch or double-tap zoom anywhere (site, realtor app, admin) ---------- */

function initZoomLock() {
    // iOS Safari ignores user-scalable=no, so its pinch gestures are cancelled here.
    ['gesturestart', 'gesturechange', 'gestureend'].forEach((ev) => document.addEventListener(ev, (e) => e.preventDefault()));
    document.addEventListener('touchmove', (e) => { if (e.touches && e.touches.length > 1) e.preventDefault(); }, { passive: false });
    // Ctrl/Cmd + mouse-wheel (and trackpad pinch) page zoom.
    window.addEventListener('wheel', (e) => { if (e.ctrlKey) e.preventDefault(); }, { passive: false });
}

/* ---------- Boot ---------- */

/**
 * Portrait photos must never be cropped. Any <img data-fit> that turns out taller than wide is shown whole
 * (object-fit: contain) over a soft blurred copy of itself; masonry images simply take their true ratio.
 * Landscape photos keep the full-bleed cover crop.
 */
function initPortraitFit() {
    const apply = (img) => {
        if (img.dataset.fitDone || !img.naturalWidth) return;
        img.dataset.fitDone = '1';

        // Frames that hold their own ratio (gallery tiles): take the photo's true ratio, so nothing is cut.
        if (img.style.aspectRatio) {
            img.style.aspectRatio = `${img.naturalWidth} / ${img.naturalHeight}`;
            img.style.height = 'auto';
            return;
        }

        // Every other photo is shown whole (never cropped or stretched), centred over a soft blurred copy of itself.
        const box = img.parentElement;
        if (getComputedStyle(box).position === 'static') box.style.position = 'relative';
        box.classList.add('fit-box');
        box.style.setProperty('--fit-bg', `url("${img.currentSrc || img.src}")`);
        img.classList.add('fit-contain');
    };

    document.querySelectorAll('img[data-fit]').forEach((img) => {
        if (img.complete) apply(img);
        else img.addEventListener('load', () => apply(img), { once: true });
    });
}

function boot() {
    const safe = (fn) => { try { fn(); } catch (e) { console.warn(fn.name, e); } };
    safe(initTheme);
    safe(initPortraitFit);
    safe(initZoomLock);
    safe(initPrefetch);
    safe(initSplit);
    safe(initReveal);
    safe(initHeader);
    safe(initScrollEffects);
    safe(initSpotlight);
    safe(initMagnetic);
    safe(initCounters);
    safe(initHeroSlides);
    safe(initCrossfade);
    safe(initRails);
    safe(initAccordions);
    safe(initFaqKb);
    safe(initTestimonials);
    safe(initGalleryFilter);
    safe(initLightbox);
    safe(initPropertyGallery);
    safe(initForms);
}

// Livewire (auth screens) swaps the page body on navigation; re-bind the essentials.
document.addEventListener('livewire:navigated', () => {
    try { initTheme(); initForms(); } catch (e) { console.warn(e); }
});

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();

/* Services quick links (one line on every screen). On phones, links that do not fit move into the "More…" dropdown. */
(() => {
    const box = document.querySelector('[data-quick-jump]');
    if (!box) return;
    const row = box.querySelector('[data-qj-row]');
    const btn = box.querySelector('[data-qj-toggle]');
    const panel = box.querySelector('[data-qj-panel]');
    const pills = [...box.querySelectorAll('[data-qj-pill]')];
    const links = [...box.querySelectorAll('[data-qj-link]')];
    if (!row || !btn || !panel) return;

    const close = () => { panel.classList.add('hidden'); btn.setAttribute('aria-expanded', 'false'); };

    const layout = () => {
        close();
        pills.forEach((p) => { p.hidden = false; });
        links.forEach((l) => { l.style.display = ""; });
        btn.hidden = false;
        const desktop = window.innerWidth >= 1024;
        const moreW = btn.getBoundingClientRect().width + 6;
        const rowLeft = row.getBoundingClientRect().left;
        const limit = row.clientWidth - moreW;
        let cut = pills.length;
        if (!desktop) {
            cut = pills.findIndex((p) => p.getBoundingClientRect().right - rowLeft > limit);
            if (cut === -1) cut = pills.length;
        }
        pills.forEach((p, i) => { p.hidden = i >= cut; });
        links.forEach((l, i) => { l.style.display = i < cut ? "none" : "flex"; });
        btn.hidden = cut >= pills.length || desktop;
    };

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = panel.classList.contains('hidden');
        panel.classList.toggle('hidden', !open);
        btn.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', (e) => { if (!box.contains(e.target)) close(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    links.forEach((a) => a.addEventListener('click', close));

    let t;
    window.addEventListener('resize', () => { clearTimeout(t); t = setTimeout(layout, 120); });
    (document.fonts ? document.fonts.ready : Promise.resolve()).then(layout);
    layout();
})();

/* Phone fields: digits only as the visitor types (spaces, dashes and letters are removed). */
document.addEventListener('input', (e) => {
    const el = e.target;
    if (el && el.matches && el.matches('[data-digits-only]')) {
        let clean = el.value.replace(/\D+/g, '');
        if (el.hasAttribute('data-strip-leading-zero')) clean = clean.replace(/^0+/, '');
        if (clean !== el.value) el.value = clean;
    }
});
