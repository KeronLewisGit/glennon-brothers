<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#162e38">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('img/brand/favicon-32.png') }}" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="{{ asset('img/brand/apple-touch-icon.png') }}">
<script>
    // Apply the saved theme before first paint so there is no flash.
    document.documentElement.classList.add('js');
    // Motion is opt-in: only with JavaScript and without a reduced-motion preference. If the app
    // script has not started within 3 s, drop it again so nothing waits on an animation.
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.classList.add('motion');
        setTimeout(function () { if (!window.gbMotionReady) document.documentElement.classList.remove('motion'); }, 3000);
    }
    try { var t = localStorage.getItem('theme'); if (t === 'dark' || t === 'light') document.documentElement.dataset.theme = t; } catch (e) {}
</script>
@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
