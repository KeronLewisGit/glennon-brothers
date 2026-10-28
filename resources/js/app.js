import Alpine from 'alpinejs';

document.documentElement.classList.add('js'); // also set in <head>; kept for safety


const root = document.documentElement;
const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
const motion = () => root.classList.contains('motion');

/* Theme toggle. The initial theme is set by an inline script in <head> to avoid a flash. */
window.toggleTheme = () => {
    const current = root.dataset.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    const next = current === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    try { localStorage.setItem('theme', next); } catch (e) { /* storage blocked: the choice lasts this page only */ }
};

/* The new theme spreads out from the button in a circle (View Transitions), where supported. */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-theme-toggle]');
    if (!btn) return;
    if (!document.startViewTransition || reduced()) { window.toggleTheme(); return; }
    const box = btn.getBoundingClientRect();
    const x = box.left + box.width / 2;
    const y = box.top + box.height / 2;
    const r = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
    root.classList.add('theme-wipe');
    const t = document.startViewTransition(() => window.toggleTheme());
    t.ready.then(() => root.animate(
        { clipPath: [`circle(0px at ${x}px ${y}px)`, `circle(${r}px at ${x}px ${y}px)`] },
        { duration: 650, easing: 'cubic-bezier(0.22, 1, 0.36, 1)', pseudoElement: '::view-transition-new(root)' },
    )).catch(() => {});
    t.finished.finally(() => root.classList.remove('theme-wipe'));
});

/* Run once per element when it scrolls into view (straight away without IntersectionObserver). */
const onView = (items, fn, options = { rootMargin: '0px 0px -8% 0px' }) => {
    if (!items.length) return;
    if (!('IntersectionObserver' in window)) { items.forEach(fn); return; }
    const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (entry.isIntersecting) { fn(entry.target); io.unobserve(entry.target); }
    }), options);
    items.forEach((el) => io.observe(el));
};

/* Scroll reveal */
const reveal = () => onView([...document.querySelectorAll('[data-reveal]')], (el) => el.classList.add('is-visible'));

/* Hero headline, word by word. The words stay plain text, so the heading reads normally. */
const splitWords = () => {
    if (!motion()) return;
    document.querySelectorAll('[data-split]').forEach((el) => {
        const words = el.textContent.trim().split(/\s+/);
        el.textContent = '';
        words.forEach((w, i) => {
            const span = document.createElement('span');
            span.className = 'w';
            span.style.setProperty('--i', i);
            span.textContent = w;
            el.append(span, i < words.length - 1 ? ' ' : '');
        });
    });
};

/* Count-ups for the published figures. The final value is in the page from the start; screen
   readers get it from a hidden copy while the visible number counts. */
const countUps = () => {
    const items = [...document.querySelectorAll('[data-count]')];
    if (!motion()) return;
    items.forEach((el) => {
        const text = el.textContent.trim();
        const m = text.match(/^([\d,]+)(.*)$/);
        if (!m) return;
        el.dataset.final = text;
        el.dataset.target = m[1].replace(/,/g, '');
        el.dataset.suffix = m[2];
        const copy = document.createElement('span');
        copy.className = 'sr-only';
        copy.textContent = text;
        el.after(copy);
        el.setAttribute('aria-hidden', 'true');
        el.textContent = '0' + m[2];
    });
    onView(items.filter((el) => el.dataset.target), (el) => {
        const target = Number(el.dataset.target);
        const start = performance.now();
        const fmt = new Intl.NumberFormat('en-IE');
        const tick = (now) => {
            const p = Math.min(1, (now - start) / 1600);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt.format(Math.round(target * eased)) + el.dataset.suffix;
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = el.dataset.final;
        };
        requestAnimationFrame(tick);
    }, { threshold: 0.6 });
};

/* Plant map: routes draw when the map comes into view; pins and cards highlight each other. */
const plantMap = () => {
    onView([...document.querySelectorAll('.plant-map')], (el) => el.classList.add('is-drawn'), { threshold: 0.35 });
    const link = (key, on) => document.querySelectorAll(`[data-plant="${key}"]`).forEach((el) => el.classList.toggle('is-hot', on));
    document.querySelectorAll('[data-plant]').forEach((el) => {
        const key = el.dataset.plant;
        ['mouseenter', 'focusin'].forEach((ev) => el.addEventListener(ev, () => link(key, true)));
        ['mouseleave', 'focusout'].forEach((ev) => el.addEventListener(ev, () => link(key, false)));
    });
};

/* From log to frame: the illustration follows the step in the middle of the screen. */
const processStory = () => {
    const section = document.querySelector('[data-process]');
    if (!section || !('IntersectionObserver' in window)) return;
    const steps = [...section.querySelectorAll('.process-step')];
    const set = (n) => {
        section.dataset.step = n;
        steps.forEach((s) => s.classList.toggle('is-active', s.dataset.step === n));
    };
    set('1');
    const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (entry.isIntersecting) set(entry.target.dataset.step);
    }), { rootMargin: '-45% 0px -45% 0px' });
    steps.forEach((s) => io.observe(s));
};

/* About timeline: the red line draws down as you scroll through it (transform only). */
const timeline = () => {
    const el = document.querySelector('[data-timeline]');
    if (!el || !motion()) return;
    let queued = false;
    const update = () => {
        queued = false;
        const box = el.getBoundingClientRect();
        const passed = (innerHeight * 0.7 - box.top) / box.height;
        el.style.setProperty('--drawn', Math.max(0, Math.min(1, passed)).toFixed(3));
    };
    const queue = () => { if (!queued) { queued = true; requestAnimationFrame(update); } };
    addEventListener('scroll', queue, { passive: true });
    addEventListener('resize', queue);
    update();
};

/* Submit buttons: one press only, with a visible "Sending…" state. */
Alpine.data('sendForm', () => ({
    submitting: false,
    submit(e) {
        if (!e.target.checkValidity()) return;
        this.submitting = true;
    },
}));

/* Column chart hover: one tooltip that follows the focused or hovered column. */
Alpine.data('columnChart', () => ({
    tip: null,
    show(el) {
        const box = el.getBoundingClientRect();
        const host = this.$root.getBoundingClientRect();
        this.tip = { label: el.dataset.label, value: el.dataset.value, x: box.left - host.left + box.width / 2, y: box.top - host.top };
    },
    hide() { this.tip = null; },
}));

/* Dashboard chrome: density, mobile nav, "/" to search. */
Alpine.data('adminShell', () => ({
    nav: false,
    density: (() => { try { return localStorage.getItem('density') || 'comfortable'; } catch (e) { return 'comfortable'; } })(),
    init() {
        this.$el.dataset.density = this.density;
        window.addEventListener('keydown', (e) => {
            const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);
            if (e.key === '/' && !typing) {
                const search = document.querySelector('[data-search]');
                if (search) { e.preventDefault(); search.focus(); }
            }
        });
    },
    toggleDensity() {
        this.density = this.density === 'compact' ? 'comfortable' : 'compact';
        this.$el.dataset.density = this.density;
        try { localStorage.setItem('density', this.density); } catch (e) { /* ignore */ }
    },
}));

window.Alpine = Alpine;
Alpine.start();
window.gbMotionReady = true;
const boot = () => { splitWords(); countUps(); reveal(); plantMap(); processStory(); timeline(); };
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
