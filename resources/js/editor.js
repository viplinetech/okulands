/*
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 *
 * Admin rich-text editor (Quill). Any <div data-rte><textarea> ... </textarea></div> becomes an editor;
 * the textarea stays as the real form field (and the no-JS fallback), so nothing else in the form changes.
 * Loaded on demand from shell.js only on pages that contain an editor.
 */
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import '../css/editor.css';

const icon = (paths) => `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;
const icons = Quill.import('ui/icons');
icons.undo = icon('<path d="M9 14L4 9l5-5"/><path d="M4 9h10a6 6 0 010 12h-3"/>');
icons.redo = icon('<path d="M15 14l5-5-5-5"/><path d="M20 9H10a6 6 0 000 12h3"/>');
icons.expand = icon('<path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>');

const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

/** Older content is plain text: blank line = paragraph, single line break = <br>. */
function toHtml(value) {
    const v = (value || '').trim();
    if (!v) return '';
    if (/<(p|h[2-4]|ul|ol|blockquote)[\s>/]/i.test(v)) return v;
    return v.split(/\r?\n\s*\r?\n/).map((p) => `<p>${esc(p.trim()).replace(/\r?\n/g, '<br>')}</p>`).join('');
}

/** What gets saved: real lists, bare-domain links get https://, empty lines stay visible. */
function cleanOutput(quill) {
    const box = document.createElement('div');
    box.innerHTML = quill.getSemanticHTML().replace(/&nbsp;/g, ' ');
    box.querySelectorAll('a[href]').forEach((a) => {
        const href = a.getAttribute('href').trim();
        if (!/^(https?:|mailto:|tel:|\/|#)/i.test(href)) a.setAttribute('href', `https://${href}`);
    });
    box.querySelectorAll('p').forEach((p) => {
        if (!p.textContent.trim() && !p.querySelector('br')) p.innerHTML = '<br>';
    });
    return quill.getText().trim() === '' ? '' : box.innerHTML;
}

function words(quill) {
    const t = quill.getText().trim();
    return t ? t.split(/\s+/).length : 0;
}

document.querySelectorAll('[data-rte]').forEach((root) => {
    const source = root.querySelector('textarea');
    if (!source || root.dataset.ready) return;
    root.dataset.ready = '1';

    const compact = root.hasAttribute('data-rte-compact');
    const toolbar = compact
        ? [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link', 'clean'], ['undo', 'redo']]
        : [
            [{ header: [2, 3, 4, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ indent: '-1' }, { indent: '+1' }],
            ['blockquote'],
            [{ align: [] }],
            ['link', 'clean'],
            ['undo', 'redo', 'expand'],
        ];

    const holder = document.createElement('div');
    holder.className = 'rte-holder';
    root.appendChild(holder);
    source.hidden = true;

    const quill = new Quill(holder, {
        theme: 'snow',
        placeholder: root.dataset.placeholder || 'Start writing…',
        formats: ['header', 'bold', 'italic', 'underline', 'strike', 'list', 'indent', 'blockquote', 'align', 'link'],
        modules: {
            history: { delay: 800, maxStack: 200, userOnly: true },
            toolbar: {
                container: toolbar,
                handlers: {
                    undo() { this.quill.history.undo(); },
                    redo() { this.quill.history.redo(); },
                    expand() { root.classList.toggle('rte-full'); document.body.classList.toggle('overflow-hidden', root.classList.contains('rte-full')); },
                },
            },
        },
    });

    // Give the toolbar buttons readable tooltips.
    const tips = {
        'ql-bold': 'Bold (Ctrl+B)', 'ql-italic': 'Italic (Ctrl+I)', 'ql-underline': 'Underline (Ctrl+U)', 'ql-strike': 'Strikethrough',
        'ql-blockquote': 'Quote', 'ql-link': 'Link (Ctrl+K)', 'ql-clean': 'Clear formatting', 'ql-undo': 'Undo (Ctrl+Z)', 'ql-redo': 'Redo (Ctrl+Y)',
        'ql-expand': 'Full screen', 'ql-header': 'Heading', 'ql-align': 'Alignment',
    };
    const bar = root.querySelector('.ql-toolbar');
    Object.entries(tips).forEach(([cls, tip]) => bar?.querySelectorAll(`.${cls}`).forEach((el) => el.setAttribute('title', tip)));
    bar?.querySelectorAll('.ql-list[value="ordered"]').forEach((el) => el.setAttribute('title', 'Numbered list'));
    bar?.querySelectorAll('.ql-list[value="bullet"]').forEach((el) => el.setAttribute('title', 'Bulleted list'));
    bar?.querySelectorAll('.ql-indent[value="+1"]').forEach((el) => el.setAttribute('title', 'Indent'));
    bar?.querySelectorAll('.ql-indent[value="-1"]').forEach((el) => el.setAttribute('title', 'Outdent'));

    // Ctrl+K opens the link box.
    quill.keyboard.addBinding({ key: 'K', shortKey: true }, (range) => {
        if (range && range.length) quill.theme.tooltip.edit('link');
    });

    const status = document.createElement('div');
    status.className = 'rte-status';
    root.appendChild(status);

    const sync = () => {
        source.value = cleanOutput(quill);
        status.textContent = `${words(quill)} words`;
    };

    quill.setContents(quill.clipboard.convert({ html: toHtml(source.value) }), 'silent');
    quill.history.clear();
    status.textContent = `${words(quill)} words`;
    quill.on('text-change', sync);

    const form = source.form;
    if (form) form.addEventListener('submit', sync, true);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && root.classList.contains('rte-full')) {
            root.classList.remove('rte-full');
            document.body.classList.remove('overflow-hidden');
        }
    });
});
