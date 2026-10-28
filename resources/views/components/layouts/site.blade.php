@props(['title' => null, 'description' => null, 'jsonLd' => [], 'mobileBar' => true, 'ogImage' => null])
@php
    $pageTitle = $title ? $title.' · '.gb('short_name') : gb('short_name').' · Sawmillers in Longford since 1913';
    $metaDescription = $description ?? gb('description');
    $phone = gb('phone');
    $email = gb('email');
    $address = gb('address');
    $og = $ogImage ?? gb('og_photo');
    $nav = [
        ['route' => 'plants.index', 'match' => 'plants.*', 'label' => 'Our mills'],
        ['route' => 'products', 'match' => 'products', 'label' => 'Products'],
        ['route' => 'frame.index', 'match' => 'frame.*', 'label' => 'Timber frame'],
        ['route' => 'sustainability', 'match' => 'sustainability', 'label' => 'Sustainability'],
        ['route' => 'about', 'match' => ['about', 'news'], 'label' => 'About'],
        ['route' => 'careers.index', 'match' => 'careers.*', 'label' => 'Careers'],
        ['route' => 'contact', 'match' => 'contact', 'label' => 'Contact'],
    ];
    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => gb('name'),
        'alternateName' => gb('alternate_names'),
        'url' => route('home'),
        'telephone' => '+'.phone_digits($phone),
        'email' => $email,
        'foundingDate' => (string) gb('founded'),
        'image' => photo((string) gb('og_photo'))['largest'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $address['street'],
            'addressLocality' => $address['city'],
            'postalCode' => $address['postcode'],
            'addressCountry' => 'IE',
        ],
        'areaServed' => ['Ireland', 'United Kingdom'],
        'sameAs' => array_values((array) gb('social')),
    ];
@endphp
<!DOCTYPE html>
<html lang="en-IE">
<head>
    @include('partials.head')
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if (gb('preview'))
        <meta name="robots" content="noindex, nofollow">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ gb('short_name') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ photo($og)['largest'] }}">
    <meta property="og:locale" content="en_IE">
    <meta name="twitter:card" content="summary_large_image">
    @foreach (array_merge([$organization], $jsonLd) as $ld)
        <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach
</head>
<body class="min-h-screen {{ $mobileBar ? 'pb-[5.25rem] lg:pb-0' : '' }}" x-data="{ menu: false }" @keydown.escape.window="menu = false">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-surface focus:px-4 focus:py-3 focus:shadow-md">Skip to content</a>

    @if (gb('preview'))
        <div class="bg-[#f3e2c2] text-sm text-[#162e38]">
            <p class="wrap flex items-start gap-2 py-2.5"><x-icon name="alert" class="mt-0.5 h-4 w-4 flex-none" /><span><strong class="font-bold">Concept preview prepared for Glennon Brothers.</strong> This is not the group's official website. Forms sent here do not reach the group.</span></p>
        </div>
    @endif

    <div class="hidden bg-slate-950 text-[0.875rem] text-white/80 lg:block">
        <div class="wrap flex h-10 items-center gap-6">
            <span class="inline-flex items-center gap-2"><x-icon name="map-pin" class="h-4 w-4 text-[#ff8a98]" />{{ $address['street'] }}, {{ $address['city'] }} {{ $address['postcode'] }}</span>
            <a href="{{ tel_href($phone) }}" class="inline-flex min-h-10 items-center gap-2 hover:text-white"><x-icon name="phone" class="h-4 w-4 text-[#ff8a98]" />{{ format_phone($phone) }}</a>
            <a href="mailto:{{ $email }}" class="inline-flex min-h-10 items-center gap-2 hover:text-white"><x-icon name="mail" class="h-4 w-4 text-[#ff8a98]" />{{ $email }}</a>
            <span class="ml-auto flex items-center gap-6">
                <a href="{{ route('news') }}" class="inline-flex min-h-10 items-center hover:text-white">News</a>
                <a href="{{ route('bookings.create') }}" class="inline-flex min-h-10 items-center gap-2 font-semibold text-white hover:text-[#f3d9a6]"><x-icon name="calendar" class="h-4 w-4" />Haulier slot booking</a>
            </span>
        </div>
    </div>

    <header class="sticky top-0 z-40 border-b border-line bg-surface/95 backdrop-blur">
        <div class="wrap flex h-[4.5rem] items-center gap-2 min-[400px]:gap-3">
            <a href="{{ route('home') }}" class="mr-auto inline-flex min-h-11 flex-none items-center" aria-label="Glennon Brothers, home"><x-logo /></a>

            <nav class="hidden items-center lg:flex" aria-label="Main">
                @foreach ($nav as $item)
                    @php($current = request()->routeIs($item['match']))
                    <a href="{{ route($item['route']) }}" @class(['relative whitespace-nowrap px-2 py-2.5 text-[0.9375rem] font-semibold transition-colors xl:px-3', 'text-ink' => $current, 'text-ink-2 hover:text-ink' => ! $current]) @if($current) aria-current="page" @endif>
                        {{ $item['label'] }}
                        @if ($current)<span class="absolute inset-x-2 -bottom-[0.95rem] h-[3px] bg-mark xl:inset-x-3" aria-hidden="true"></span>@endif
                    </a>
                @endforeach
            </nav>

            <button type="button" onclick="toggleTheme()" class="hidden h-11 w-11 flex-none items-center justify-center rounded text-ink-2 hover:bg-surface-2 hover:text-ink sm:inline-flex" aria-label="Switch light or dark theme">
                <x-icon name="moon" class="h-5 w-5 dark-hide" /><x-icon name="sun" class="hidden h-5 w-5 dark-show" />
            </button>
            <a href="{{ route('quotes.create') }}" class="btn btn-primary btn-sm hidden flex-none whitespace-nowrap lg:inline-flex">Trade quote<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            <button type="button" class="inline-flex h-11 w-11 flex-none items-center justify-center rounded ring-1 ring-line-strong lg:hidden" @click="menu = !menu" :aria-expanded="menu.toString()" aria-controls="mobile-menu" aria-label="Menu">
                <x-icon name="menu" x-show="!menu" /><x-icon name="x" x-show="menu" x-cloak />
            </button>
        </div>
        <div id="mobile-menu" class="max-h-[calc(100dvh-4.5rem)] overflow-y-auto border-t border-line bg-surface lg:hidden" x-show="menu" x-cloak x-transition.opacity>
            <nav class="wrap flex flex-col py-3" aria-label="Mobile">
                <a href="{{ route('home') }}" class="rounded px-1 py-3 text-xl font-bold">Home</a>
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" class="rounded px-1 py-3 text-xl font-bold" @if(request()->routeIs($item['match'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ route('news') }}" class="rounded px-1 py-3 text-xl font-bold">News</a>
                <a href="{{ route('bookings.create') }}" class="rounded px-1 py-3 text-xl font-bold">Haulier slot booking</a>
                <div class="mt-3 grid gap-3 border-t border-line pt-5 sm:grid-cols-2">
                    <a href="{{ route('quotes.create') }}" class="btn btn-primary">Request a trade quote</a>
                    <a href="{{ tel_href($phone) }}" class="btn btn-outline"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($phone) }}</a>
                </div>
                <button type="button" onclick="toggleTheme()" class="mt-3 flex min-h-11 items-center gap-2 rounded px-1 text-left font-semibold text-ink-2"><x-icon name="moon" class="h-5 w-5" />Switch light or dark theme</button>
            </nav>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="site-footer mt-24 bg-slate-900 text-white/75">
        <div class="bale tall" aria-hidden="true"></div>
        <div class="rings rings-left">
            <div class="wrap grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-[1.3fr_1fr_1fr_1.2fr]">
                <div>
                    <x-logo tone="light" />
                    <p class="mt-5 max-w-xs text-white/75">{{ gb('footer_line') }}</p>
                    <a href="{{ tel_href($phone) }}" class="font-display mt-6 inline-flex min-h-12 items-center gap-2.5 text-2xl font-bold text-white hover:text-[#f3d9a6]"><x-icon name="phone" class="h-5 w-5" />{{ format_phone($phone) }}</a>
                    <p class="mt-1"><a class="inline-flex min-h-11 items-center hover:text-white" href="mailto:{{ $email }}">{{ $email }}</a></p>
                </div>
                <div>
                    <h2 class="kicker font-sans text-white/60">Timber</h2>
                    <ul class="mt-4 space-y-1">
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('products') }}">Products</a></li>
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('frame.index') }}">Timber frame (DTE)</a></li>
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('sustainability') }}">Sustainability</a></li>
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('about') }}">About the group</a></li>
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('news') }}">News</a></li>
                    </ul>
                </div>
                <div>
                    <h2 class="kicker font-sans text-white/60">Work with us</h2>
                    <ul class="mt-4 space-y-1">
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('quotes.create') }}">Request a trade quote</a></li>
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('frame.create') }}">Timber frame enquiry</a></li>
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('bookings.create') }}">Haulier slot booking</a></li>
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('careers.index') }}">Careers</a></li>
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h2 class="kicker font-sans text-white/60">The mills</h2>
                    <ul class="mt-4 space-y-1">
                        @foreach ((array) gb('plants') as $key => $p)
                            <li><a class="inline-flex min-h-11 items-center gap-2 hover:text-white" href="{{ route('plants.show', $key) }}">{{ $p['name'] }}<span class="text-white/60">· {{ $p['region'] === 'uk' ? 'Scotland' : 'Ireland' }}</span></a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10">
                <div class="wrap flex flex-col gap-3 py-6 text-sm text-white/65 md:flex-row md:items-center md:justify-between">
                    <p>© {{ date('Y') }} {{ gb('name') }}.</p>
                    <p class="flex flex-wrap gap-x-5">
                        <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('privacy') }}">Privacy</a>
                        <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('credits') }}">Photo credits</a>
                        <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('sitemap.page') }}">Site map</a>
                        <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('login') }}">Staff sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    @if ($mobileBar)
        {{-- Thumb-reach actions on phones: call head office, email, ask for a trade quote. --}}
        <nav aria-label="Quick actions" class="fixed inset-x-0 bottom-0 z-30 border-t border-white/10 bg-slate-900/95 px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur lg:hidden">
            <div class="flex gap-2">
                <a href="{{ tel_href($phone) }}" class="btn btn-on-dark flex-1 px-2 text-[0.9375rem]"><x-icon name="phone" class="h-4 w-4" />Call</a>
                <a href="mailto:{{ $email }}" class="btn btn-on-dark flex-1 px-2 text-[0.9375rem]"><x-icon name="mail" class="h-4 w-4" />Email</a>
                <a href="{{ route('quotes.create') }}" class="btn btn-light flex-[1.5] whitespace-nowrap px-2 text-[0.9375rem]">Trade quote</a>
            </div>
        </nav>
    @endif
</body>
</html>
