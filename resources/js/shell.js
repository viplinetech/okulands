/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 *
 * Behaviour for the Realtor dashboard and Admin area (loaded after app.js).
 * Everything is opt-in through data-* attributes and fails safe.
 */

const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

/* ---------- "More" bottom sheet (realtor app) + slide-in drawer (admin hamburger) ---------- */

function initOverlays() {
    const setup = (name, cls) => {
        const open = () => document.body.classList.add(cls);
        const close = () => document.body.classList.remove(cls);
        $$(`[data-${name}-open]`).forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); open(); }));
        $$(`[data-${name}-close]`).forEach((b) => b.addEventListener('click', close));
        $$(`[data-${name}-toggle]`).forEach((b) => b.addEventListener('click', () => document.body.classList.toggle(cls)));
        document.addEventListener('keydown', (e) => e.key === 'Escape' && close());
        // Following a link inside the overlay closes it (the next page replaces this one anyway).
        $$(`[data-${name}-panel] a`).forEach((a) => a.addEventListener('click', close));
        window.matchMedia('(min-width: 1024px)').addEventListener?.('change', (e) => e.matches && close());
        return close;
    };
    const closeSheet = setup('sheet', 'sheet-open');
    setup('drawer', 'drawer-open');

    // Swipe the sheet down to dismiss it.
    const sheet = document.querySelector('[data-sheet-panel]');
    if (sheet) {
        let y0 = null;
        sheet.addEventListener('touchstart', (e) => { if (sheet.scrollTop <= 0) y0 = e.touches[0].clientY; }, { passive: true });
        sheet.addEventListener('touchmove', (e) => {
            if (y0 === null) return;
            const dy = e.touches[0].clientY - y0;
            if (dy > 0) sheet.style.transform = `translateY(${dy}px)`;
        }, { passive: true });
        sheet.addEventListener('touchend', (e) => {
            if (y0 === null) return;
            const dy = e.changedTouches[0].clientY - y0;
            sheet.style.transform = '';
            if (dy > 90) closeSheet();
            y0 = null;
        });
    }
}

/* ---------- Copy / native share ---------- */

async function copyText(text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch (e) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0;font-size:16px';
        document.body.appendChild(ta);
        ta.select();
        const ok = document.execCommand?.('copy');
        ta.remove();
        return !!ok;
    }
}

function flashLabel(btn, text) {
    const label = btn.querySelector('[data-label]') || btn;
    const old = label.textContent;
    label.textContent = text;
    setTimeout(() => { label.textContent = old; }, 1600);
}

function initCopyShare() {
    $$('[data-copy]').forEach((btn) => btn.addEventListener('click', async () => {
        const ok = await copyText(btn.dataset.copy);
        flashLabel(btn, ok ? (btn.dataset.copied || 'Copied!') : 'Press Ctrl+C');
    }));
    $$('[data-share]').forEach((btn) => btn.addEventListener('click', async () => {
        const data = { title: btn.dataset.title || document.title, text: btn.dataset.text || '', url: btn.dataset.share };
        if (navigator.share) {
            try { await navigator.share(data); } catch (e) { /* cancelled */ }
        } else {
            const ok = await copyText(data.url);
            flashLabel(btn, ok ? 'Link copied!' : 'Press Ctrl+C');
        }
    }));
}

/* ---------- Confirm dialog for destructive actions ---------- */

/**
 * "Remove photo" buttons. Nothing is deleted until the form is saved: a confirmation popup comes first, then the
 * photo is marked for removal (its hidden checkbox is ticked and it is dimmed). Pressing "Undo" clears the mark.
 */
function initRemovePhoto() {
    const buttons = $$('[data-remove-photo]');
    const modal = document.getElementById('confirm-modal');
    if (!buttons.length || !modal) return;

    const text = modal.querySelector('[data-confirm-text]');
    const yes = modal.querySelector('[data-confirm-yes]');
    const no = modal.querySelector('[data-confirm-no]');
    let pending = null;

    const setMarked = (btn, marked) => {
        const box = document.getElementById(btn.dataset.removeTarget);
        const wrap = btn.closest('[data-photo]');
        if (box) box.checked = marked;
        if (wrap) wrap.classList.toggle('opacity-50', marked);
        btn.querySelector('[data-remove-text]').textContent = marked ? 'Undo removal' : 'Remove photo';
        btn.classList.toggle('text-brand', marked);
    };

    buttons.forEach((btn) => btn.addEventListener('click', () => {
        const box = document.getElementById(btn.dataset.removeTarget);
        if (box && box.checked) { setMarked(btn, false); return; }
        pending = btn;
        const label = btn.dataset.removeLabel ? ` (${btn.dataset.removeLabel})` : '';
        text.textContent = `Remove this photo${label}? It will disappear from the website when you press Save.`;
        yes.textContent = 'Yes, remove photo';
        modal.hidden = false;
        yes.focus();
    }));

    yes.addEventListener('click', () => {
        if (pending) setMarked(pending, true);
        pending = null;
        yes.textContent = 'Yes, continue';
    });
    no.addEventListener('click', () => { pending = null; yes.textContent = 'Yes, continue'; });
}

function initConfirm() {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;
    const text = modal.querySelector('[data-confirm-text]');
    const yes = modal.querySelector('[data-confirm-yes]');
    const no = modal.querySelector('[data-confirm-no]');
    let pending = null;

    const close = () => { modal.hidden = true; pending = null; };
    no.addEventListener('click', close);
    modal.addEventListener('click', (e) => e.target === modal && close());
    document.addEventListener('keydown', (e) => e.key === 'Escape' && !modal.hidden && close());
    yes.addEventListener('click', () => {
        const form = pending;
        close();
        if (form) { form.dataset.confirmed = '1'; form.requestSubmit(); }
    });

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed) return;
        e.preventDefault();
        pending = form;
        text.textContent = form.dataset.confirm;
        yes.textContent = form.dataset.confirmYes || 'Yes, continue';
        modal.hidden = false;
        yes.focus();
    }, true);
}

/* ---------- Repeaters (settings: values, stats, team…) ---------- */

function initRepeaters() {
    $$('[data-repeater]').forEach((box) => {
        const tpl = box.querySelector('template');
        const list = box.querySelector('[data-repeater-list]');
        if (!tpl || !list) return;
        let next = list.children.length + 1000; // unique array keys, never reused

        box.querySelector('[data-repeater-add]')?.addEventListener('click', () => {
            list.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__INDEX__', String(next++)));
            list.lastElementChild?.querySelector('input,textarea')?.focus();
        });
        box.addEventListener('click', (e) => {
            e.target.closest('[data-repeater-remove]')?.closest('[data-repeater-row]')?.remove();
        });
    });
}

/* ---------- Image pickers: instant local preview + drag & drop ---------- */

function initUploads() {
    $$('input[type="file"][data-preview]').forEach((input) => {
        const target = document.querySelector(input.dataset.preview);
        const zone = input.closest('.dropzone');
        const render = () => {
            if (!target) return;
            target.innerHTML = '';
            Array.from(input.files || []).forEach((file) => {
                if (!file.type.startsWith('image/')) return;
                const img = document.createElement('img');
                img.className = 'h-full w-full object-cover';
                img.alt = '';
                img.src = URL.createObjectURL(file);
                const wrap = document.createElement('div');
                wrap.className = 'thumb';
                wrap.appendChild(img);
                target.appendChild(wrap);
            });
        };
        input.addEventListener('change', render);
        if (zone) {
            ['dragenter', 'dragover'].forEach((ev) => zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.add('is-over'); }));
            ['dragleave', 'drop'].forEach((ev) => zone.addEventListener(ev, () => zone.classList.remove('is-over')));
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                if (e.dataTransfer?.files?.length) { input.files = e.dataTransfer.files; render(); }
            });
        }
    });
}

/* ---------- Tabs ---------- */

function initTabs() {
    $$('[data-tabs]').forEach((box) => {
        const buttons = $$('[data-tab]', box);
        const panels = $$('[data-tab-panel]', box);
        const show = (id) => {
            buttons.forEach((b) => b.classList.toggle('is-active', b.dataset.tab === id));
            panels.forEach((p) => { p.hidden = p.dataset.tabPanel !== id; });
            try { sessionStorage.setItem('okulands-tab-' + location.pathname, id); } catch (e) {}
        };
        let start = new URLSearchParams(location.search).get('tab');
        try { start = start || sessionStorage.getItem('okulands-tab-' + location.pathname); } catch (e) {}
        if (!buttons.some((b) => b.dataset.tab === start)) start = buttons[0]?.dataset.tab;
        buttons.forEach((b) => b.addEventListener('click', () => show(b.dataset.tab)));
        if (start) show(start);
    });
}

/* ---------- Misc ---------- */

function initFlash() {
    $$('.flash-success[data-autohide]').forEach((el) => setTimeout(() => {
        el.style.transition = 'opacity .5s, transform .5s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-6px)';
        setTimeout(() => el.remove(), 500);
    }, 6000));
}

/* Disable a submit button after the first tap so a double-tap can't post twice. */
function initSingleSubmit() {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.multi !== undefined || e.defaultPrevented) return;
        const btn = form.querySelector('button[type="submit"]:not([data-multi])');
        if (btn) setTimeout(() => { btn.disabled = true; btn.classList.add('opacity-70'); }, 0);
    });
}

/* Select-all for checkbox groups: <input data-check-all="g"> + <input data-check="g"> */
function initCheckAll() {
    $$('[data-check-all]').forEach((all) => all.addEventListener('change', () => {
        $$(`[data-check="${all.dataset.checkAll}"]`).forEach((c) => { c.checked = all.checked; });
    }));
}

/* "Generate using OkuLands Smart AI" buttons next to a rich-text field. Gathers sibling form
   fields by name (title, location… for a property; title, category for a blog post), posts them
   to the matching AI endpoint, and drops the result into that editor via root.setRteHtml(). */
function initAiGenerate() {
    const fieldsFor = {
        property: ['title', 'location', 'sector', 'type', 'price', 'size', 'bedrooms', 'bathrooms'],
        blog: ['title', 'category'],
    };

    $$('[data-ai-generate]').forEach((btn) => {
        const form = btn.closest('form');
        const status = btn.closest('.form-full')?.querySelector('[data-ai-status]');
        const label = btn.querySelector('[data-ai-label]');
        const originalLabel = label?.textContent;

        btn.addEventListener('click', async () => {
            const kind = btn.dataset.aiGenerate;
            const target = document.getElementById(btn.dataset.aiTarget);
            if (!form || !target || !target.setRteHtml) return;

            const names = fieldsFor[kind] || [];
            const payload = {};
            let missingTitle = false;
            names.forEach((name) => {
                const el = form.querySelector(`[name="${name}"]`);
                let val = '';
                if (el instanceof HTMLSelectElement) {
                    val = el.selectedOptions[0]?.textContent.trim() || '';
                } else if (el) {
                    val = el.value.trim();
                }
                if (name === 'title' && !val) missingTitle = true;
                if (val) payload[name] = val;
            });

            if (status) { status.textContent = ''; status.classList.remove('err'); }

            if (missingTitle) {
                if (status) { status.textContent = 'Please fill in the title first.'; status.classList.add('err'); }
                return;
            }

            btn.disabled = true;
            if (label) label.textContent = 'Generating…';

            try {
                const res = await fetch(btn.dataset.aiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'The AI could not generate text right now.');

                target.setRteHtml(data.html || '');
                if (status) status.textContent = 'Draft generated. Review and edit it before saving.';
            } catch (e) {
                if (status) { status.textContent = e.message || 'Something went wrong. Please try again.'; status.classList.add('err'); }
            } finally {
                btn.disabled = false;
                if (label) label.textContent = originalLabel;
            }
        });
    });
}

/* One-click "Generate [X] using OkuLands Smart AI" at the top of a New-record page: the AI picks
   its own topic and fills in every field (title, category, summary, body) with nothing typed
   first. See data-ai-generate above for the per-field, title-driven version. */
function initAiFullGenerate() {
    const btn = document.querySelector('[data-ai-full]');
    if (!btn) return;

    const form = document.querySelector('form');
    const status = document.querySelector('[data-ai-full-status]');
    const label = btn.querySelector('[data-ai-full-label]');
    const originalLabel = label?.textContent;

    const setField = (name, value) => {
        const el = form?.querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    };

    btn.addEventListener('click', async () => {
        btn.disabled = true;
        if (label) label.textContent = 'Generating…';
        if (status) { status.textContent = 'OkuLands Smart AI is drafting a full post. This can take a few seconds.'; status.classList.remove('err'); }

        try {
            const res = await fetch(btn.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({}),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'OkuLands Smart AI could not generate a post right now.');

            setField('title', data.title);
            setField('category', data.category);
            setField('excerpt', data.excerpt);

            const bodyRoot = form?.querySelector('[data-rte]');
            if (bodyRoot?.setRteHtml) {
                bodyRoot.setRteHtml(data.body || '');
            } else {
                const textarea = form?.querySelector('[name="body"]');
                if (textarea) textarea.value = data.body || '';
            }

            if (status) status.textContent = 'Draft generated below. Review it, add a cover photo, then publish when ready.';
            form?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e) {
            if (status) { status.textContent = e.message || 'Something went wrong. Please try again.'; status.classList.add('err'); }
        } finally {
            btn.disabled = false;
            if (label) label.textContent = originalLabel;
        }
    });
}

function boot() {
    const safe = (fn) => { try { fn(); } catch (e) { console.warn(fn.name, e); } };
    safe(initOverlays);
    safe(initCopyShare);
    safe(initConfirm);
    safe(initRemovePhoto);
    safe(initRepeaters);
    safe(initUploads);
    safe(initTabs);
    safe(initFlash);
    safe(initSingleSubmit);
    safe(initCheckAll);
    safe(initAiGenerate);
    safe(initAiFullGenerate);
    // The rich-text editor is only downloaded on pages that have one.
    if (document.querySelector('[data-rte]')) import('./editor.js').catch(() => {});
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();

