@php
    $plants = (array) gb('plants');
    $products = (array) gb('products');
    $news = array_slice((array) gb('news'), 0, 3);
    $productIcons = ['construction' => 'stack', 'fencing' => 'fence', 'pallet' => 'pallet', 'decking' => 'layers', 'residues' => 'leaf'];
    // The range as the group lists it on its own home page.
    $range = ['Kiln-dried carcassing', 'CLS', 'Pallet and packing case timber', 'Machined whitewoods', 'Glendeck decking', 'Glenfence', 'Fencing', 'Timber frame homes', 'Roof trusses', 'Wood chip', 'Sawdust', 'Bark', 'Shavings'];
    $steps = [
        ['Logs in', 'Home-grown logs come into the mill yards. At Troon they arrive by boat from the coasts and islands of Scotland, which cuts lorry miles.'],
        ['Sawn', 'Sawlines break each log down into boards, posts and rails. Fermoy put in a new Linck sawline in 2001.'],
        ['Dried and graded', 'Timber is kiln dried and strength graded. The group gives its kiln-drying capacity as 220,000 m³ a year, and sawdust from the mills fuels the kilns.'],
        ['Packed and sent', 'Carcassing, CLS, fencing and pallet timber go out in packs to merchants and manufacturers in Ireland and the UK. Chip goes to MDF, bark to mulch, shavings to bedding.'],
        ['Built into homes', 'DTE, on the Longford site, designs, makes and erects timber frame homes and engineered roof trusses.'],
    ];
@endphp
<x-layouts.site>
    {{-- Hero: the home page's own version of the shared header band (the current site's banner between red rules). --}}
    <section class="home-hero rings relative isolate overflow-hidden bg-slate-900 text-white on-dark">
        <div class="bale" aria-hidden="true"></div>
        <div class="hero-photo absolute inset-0 -z-10">
            <x-photo key="troon-harbour" alt="" :eager="true" sizes="100vw" class="h-full w-full" position="center 60%" />
            <div class="hero-scrim absolute inset-0"></div>
        </div>
        <div class="wrap grid gap-10 pb-16 pt-12 md:pb-24 md:pt-20 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-end">
            <div class="min-w-0">
                <x-tree :draw="true" class="hero-mark h-12 w-[5.3rem] text-[#ff5468] md:h-14 md:w-[6.2rem]" />
                <x-stamp code="1913" class="mt-5">Longford · Fermoy · Windymains · Troon</x-stamp>
                <h1 class="display-1 mt-5 max-w-[17ch]" data-split>Irish and Scottish timber, sawn by one family since 1913.</h1>
                <p class="lead hero-in mt-6 max-w-[56ch] text-white/85" style="--d: 900ms">Four sawmills supply construction timber, CLS, fencing and pallet wood to merchants and manufacturers across Ireland and the UK. DTE, on our Longford site, builds timber frame homes and roof trusses.</p>
                <div class="hero-in mt-8 flex flex-wrap gap-3" style="--d: 1050ms">
                    <a href="{{ route('quotes.create') }}" class="btn btn-primary">Request a trade quote<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    <a href="{{ route('plants.index') }}" class="btn btn-on-dark">See the four mills</a>
                </div>
            </div>
            <x-ticket class="hero-in hidden lg:block" style="--d: 1200ms" title="Glennon Brothers" :fields="['Founded' => 'Longford, '.gb('founded'), 'Mills' => 'Longford, Fermoy, Windymains, Troon', 'Markets' => 'Ireland and the UK', 'Timber frame' => 'DTE Manufacturing']" foot="Telephone" :foot-value="format_phone(gb('phone'))" />
        </div>
        <span class="photo-note" style="bottom: 0.9rem">Glennon Brothers sawmill, Troon harbour</span>
        <div class="bale" aria-hidden="true"></div>
    </section>

    {{-- The range, as a marquee (a static, wrapping list with reduced motion). --}}
    <section class="marquee bg-slate-950 text-white" aria-label="What the mills make">
        <div class="marquee-track">
            @foreach ([false, true] as $copy)
                <ul class="marquee-list" @if($copy) aria-hidden="true" @endif>
                    @foreach ($range as $item)
                        <li><x-tree class="h-3 w-5 flex-none text-[#ff5468]" />{{ $item }}</li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </section>

    {{-- Welcome: the current home page's layout (product and company boxes beside the welcome text). --}}
    <section class="wrap mt-16 grid gap-10 md:mt-24 lg:grid-cols-[19rem_minmax(0,1fr)] lg:gap-16" aria-labelledby="welcome-title">
        <div class="order-2 grid gap-8 sm:grid-cols-2 lg:order-1 lg:grid-cols-1">
            <div>
                <span class="footer-tab">Our products</span>
                <div class="box box-tabbed grid grid-cols-2 gap-3 p-4" data-reveal>
                    <a href="{{ route('products') }}#decking" class="brand-tile"><span class="brand-word">Glendeck</span><span class="text-sm text-ink-3">Decking</span></a>
                    <a href="{{ route('products') }}#fencing" class="brand-tile"><span class="brand-word italic">Glenfence</span><span class="text-sm text-ink-3">Fencing</span></a>
                </div>
            </div>
            <div>
                <span class="footer-tab">Our companies</span>
                <ul class="box box-tabbed divide-y divide-line px-4" data-reveal>
                    <li><a href="{{ route('plants.show', 'windymains') }}" class="flex min-h-14 items-center justify-between gap-3 font-semibold hover:text-link">Windymains Timber<x-icon name="arrow-right" class="h-4 w-4 text-mark" /></a></li>
                    <li><a href="{{ route('frame.index') }}" class="flex min-h-14 items-center justify-between gap-3 font-semibold hover:text-link">DTE Manufacturing<x-icon name="arrow-right" class="h-4 w-4 text-mark" /></a></li>
                    <li><a href="{{ gb('atd.website') }}" rel="noopener" class="flex min-h-14 items-center justify-between gap-3 font-semibold hover:text-link">Alexanders Timber Design<x-icon name="external" class="h-4 w-4 text-mark" /><span class="sr-only">(opens another website)</span></a></li>
                </ul>
            </div>
        </div>
        <div class="order-1 lg:order-2">
            <h2 id="welcome-title" class="display-2 text-link">Welcome to Glennon Brothers.</h2>
            <div class="prose-copy mt-6 text-[1.125rem] text-ink-2 measure">
                <p>Since 1913, Glennon Brothers has been manufacturing high-quality timber products. Through our plants in Longford and Fermoy, and Windymains Timber and Troon in Scotland, we supply both the Irish and UK markets with products for the construction, pallet wood and fencing industries.</p>
                <p>Products include kiln-dried strength-graded carcassing, pallet and packing case material, CLS for the timber frame manufacturing industry and machined whitewoods for the construction and garden shed industries. We also offer Glendeck timber decking and fencing for outdoor use, as well as timber frame homes and engineered roof trusses.</p>
                <p>Residual products such as wood chip, sawdust, bark and shavings go to a range of other uses.</p>
            </div>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('about') }}" class="btn btn-outline">About the group<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                <a href="{{ route('contact') }}" class="btn btn-ghost">Contact us</a>
            </div>
        </div>
    </section>

    {{-- The mills, on a map --}}
    <section class="mt-20 bg-surface-2 py-16 md:mt-28 md:py-24" aria-labelledby="mills-title">
        <div class="wrap grid items-center gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:gap-16">
            <div class="order-2 lg:order-1">
                <x-plant-map class="mx-auto w-full max-w-[34rem]" />
                <p class="hint mt-3 text-center">Schematic map. Mill positions by town; coastline from Natural Earth.</p>
            </div>
            <div class="order-1 lg:order-2">
                <x-stamp code="01">Four mills, two countries</x-stamp>
                <h2 id="mills-title" class="display-2 mt-4 max-w-[22ch]">From a Longford flax mill to the harbour at Troon.</h2>
                <ul class="mt-8 grid gap-3 sm:grid-cols-2" data-plant-list>
                    @foreach ($plants as $key => $p)
                        <li class="plant-card box group relative p-5" data-plant="{{ $key }}" data-reveal style="--reveal-delay: {{ $loop->index * 80 }}ms">
                            <p class="kicker text-ink-3">{{ $p['region'] === 'uk' ? 'Scotland' : 'Ireland' }} · {{ $p['since_label'] }}</p>
                            <h3 class="mt-1.5 text-xl font-bold"><a class="after:absolute after:inset-0" href="{{ route('plants.show', $key) }}">{{ $p['name'] }}</a></h3>
                            <p class="mt-1 text-ink-2">{{ $p['role'] }}</p>
                            <span class="mt-3 inline-flex items-center gap-1.5 text-[0.9375rem] font-semibold text-link">Visit {{ $p['name'] }}<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('plants.index') }}" class="btn btn-outline mt-8">All the mills<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            </div>
        </div>
    </section>

    {{-- From log to frame: a scroll story. The illustration follows the step in view. --}}
    <section class="process wrap mt-20 md:mt-28" data-process data-step="1" aria-labelledby="process-title">
        <x-stamp code="02">How the mills work</x-stamp>
        <h2 id="process-title" class="display-2 mt-4 max-w-[24ch]">From the log yard to a timber frame home.</h2>
        <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-16">
            <div class="process-visual hidden lg:block">
                <div class="box sticky top-10 overflow-hidden p-6">
                    <x-process-scene class="w-full" />
                    <ol class="process-dots mt-4 flex justify-center gap-2" aria-hidden="true">
                        @foreach ($steps as $i => $s)<li data-dot="{{ $i + 1 }}"></li>@endforeach
                    </ol>
                </div>
            </div>
            <ol class="process-steps">
                @foreach ($steps as $i => [$name, $text])
                    <li class="process-step" data-step="{{ $i + 1 }}">
                        <span class="step-n">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3 class="text-2xl font-bold">{{ $name }}</h3>
                            <p class="mt-2 text-ink-2 measure">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Sourced figures, counted up when they come into view --}}
    <section class="rings mt-20 bg-slate-900 py-14 text-white on-dark md:mt-28 md:py-20" aria-labelledby="figures-title">
        <div class="bale -mt-14 mb-14 md:-mt-20 md:mb-20" aria-hidden="true"></div>
        <div class="wrap">
            <h2 id="figures-title" class="sr-only">The group in figures</h2>
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="figure-stamp"><b>{{ gb('founded') }}</b><span class="mt-2 block text-white/80">William and James Glennon open a sawmill on the Longford flax-mill site</span></div>
                @foreach ((array) gb('figures') as $f)
                    <div class="figure-stamp"><b data-count>{{ $f['value'] }}</b><span class="mt-2 block text-white/80">{{ ucfirst($f['label']) }}</span></div>
                @endforeach
                <div class="figure-stamp"><b><span data-count>6</span>×</b><span class="mt-2 block text-white/80">TTJ Softwood Trader / Sawmiller of the Year, most recently in September 2025</span></div>
            </div>
            <p class="mt-8 text-[0.9375rem] text-white/70">Figures as published on glennonbrothers.ie and in the group's own press releases.</p>
        </div>
    </section>

    {{-- Products --}}
    <section class="wrap mt-20 md:mt-28" aria-labelledby="products-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <x-stamp code="03">Our products</x-stamp>
                <h2 id="products-title" class="display-2 mt-4 max-w-[24ch]">Graded, dried and treated for the job it will do.</h2>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('products') }}" class="btn btn-outline">Product sizes<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                <a href="{{ route('quotes.create') }}" class="btn btn-primary">Ask for prices</a>
            </div>
        </div>
        <ul class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $key => $prod)
                <li data-reveal style="--reveal-delay: {{ $loop->index * 60 }}ms">
                    <a href="{{ route('products') }}#{{ $key }}" class="lift box group flex h-full flex-col gap-3 p-6">
                        <x-icon :name="$productIcons[$key] ?? 'stack'" class="h-8 w-8 text-mark" />
                        <span class="font-display text-[1.3rem] font-bold leading-snug">{{ $prod['title'] }}</span>
                        <span class="text-ink-2">{{ \Illuminate\Support\Str::limit($prod['intro'], 130) }}</span>
                        <span class="mt-auto inline-flex items-center gap-1.5 pt-2 font-semibold text-link">Sizes and details<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                    </a>
                </li>
            @endforeach
            <li data-reveal style="--reveal-delay: 300ms">
                <a href="{{ route('frame.index') }}" class="lift group flex h-full flex-col gap-3 rounded-[6px] bg-slate-900 p-6 text-white shadow-[var(--shadow-card)] on-dark">
                    <x-icon name="frame" class="h-8 w-8 text-[#ff5468]" />
                    <span class="font-display text-[1.3rem] font-bold leading-snug">Timber frame and roof trusses</span>
                    <span class="text-white/80">Designed, made and erected by DTE in Longford, for self-builders and developers.</span>
                    <span class="mt-auto inline-flex items-center gap-1.5 pt-2 font-semibold text-white">DTE timber frame<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                </a>
            </li>
        </ul>
    </section>

    {{-- Timber frame --}}
    <section class="mt-20 md:mt-28" aria-labelledby="frame-title">
        <div class="wrap grid items-center gap-10 lg:grid-cols-2">
            <div class="relative" data-reveal>
                <x-photo key="frame-house" alt="A two-storey timber frame house under construction, its wall panels and roof trusses in place" sizes="(min-width: 1024px) 50vw, 100vw" class="box-photo aspect-[4/3]" />
                <span class="photo-note">Illustrative photo</span>
            </div>
            <div>
                <x-stamp code="DTE">Timber frame homes</x-stamp>
                <h2 id="frame-title" class="display-2 mt-4">Send us your drawings. We'll design the frame.</h2>
                <p class="mt-5 text-ink-2 measure">DTE Manufacturing, on the Longford site, designs, makes and erects timber frame homes and engineered roof trusses for the construction industry and for one-off self-builders. It is approved and monitored by the National Standards Authority of Ireland.</p>
                <ul class="ticked mt-6 space-y-2 text-ink-2">
                    <li>Upload plans, elevations or a full drawing set with your enquiry</li>
                    <li>Tell us the site, the planning position and when you hope to start</li>
                    <li>Send revised drawings later through your own private link</li>
                </ul>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('frame.create') }}" class="btn btn-primary">Start a timber frame enquiry<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    <a href="{{ route('frame.index') }}" class="btn btn-outline">How it works</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Careers --}}
    <section class="wrap mt-20 md:mt-28" aria-labelledby="careers-title">
        <div class="rings relative isolate overflow-hidden rounded-[6px] bg-slate-900 p-8 text-white shadow-[var(--shadow-card)] on-dark md:p-12">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-end">
                <div>
                    <x-stamp code="{{ $openJobs }}">{{ $openJobs === 1 ? 'Opening' : 'Openings' }} across the group</x-stamp>
                    <h2 id="careers-title" class="display-2 mt-4">Operatives, fitters, electricians, drivers and erectors.</h2>
                    <p class="mt-5 max-w-[56ch] text-white/85">The mills run shifts all year, and DTE hires timber frame erectors. Apply to one plant or to any of them; operatives and drivers need no CV.</p>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('careers.index') }}" class="btn btn-primary">See the openings<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    <a href="{{ route('careers.apply') }}" class="btn btn-on-dark">General application</a>
                </div>
            </div>
            <ul class="mt-10 grid gap-3 border-t border-white/15 pt-8 sm:grid-cols-2 lg:grid-cols-5">
                @foreach (\App\Enums\JobCategory::cases() as $c)
                    <li><a href="{{ route('careers.index', ['family' => $c->value]) }}" class="flex min-h-12 items-center gap-2 font-semibold text-white/90 hover:text-white"><x-tree class="h-3.5 w-6 flex-none text-[#ff5468]" />{{ $c->label() }}</a></li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Trade and haulage --}}
    <section class="wrap mt-20 grid gap-6 md:mt-28 md:grid-cols-2" aria-label="For trade customers and hauliers">
        <a href="{{ route('quotes.create') }}" class="lift box group flex flex-col gap-3 p-7" data-reveal>
            <x-icon name="calculator" class="h-8 w-8 text-mark" />
            <span class="font-display text-2xl font-bold">Trade and merchant quotes</span>
            <span class="text-ink-2">List the products, sizes, treatment and quantities, and where each load is going. Your request goes straight to the Ireland or UK sales team, who reply in euro or sterling.</span>
            <span class="mt-2 inline-flex items-center gap-1.5 font-semibold text-link">Request a quote<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
        </a>
        <a href="{{ route('bookings.create') }}" class="lift box group flex flex-col gap-3 p-7" data-reveal style="--reveal-delay: 90ms">
            <x-icon name="truck" class="h-8 w-8 text-mark" />
            <span class="font-display text-2xl font-bold">Hauliers: book a yard slot</span>
            <span class="text-ink-2">Collecting sawn timber, delivering logs or taking residues away? Ask for a loading or tipping slot at any of the four mills, and the yard will confirm it.</span>
            <span class="mt-2 inline-flex items-center gap-1.5 font-semibold text-link">Book a slot<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
        </a>
    </section>

    {{-- News --}}
    <section class="wrap mt-20 md:mt-28" aria-labelledby="news-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <x-stamp code="04">Press</x-stamp>
                <h2 id="news-title" class="display-2 mt-4">News</h2>
            </div>
            <a href="{{ route('news') }}" class="btn btn-outline">All news<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach ($news as $n)
                <article class="box flex flex-col border-t-4 border-t-brand p-6" data-reveal style="--reveal-delay: {{ $loop->index * 80 }}ms">
                    <p class="kicker text-ink-3"><time datetime="{{ $n['date'] }}">{{ \Illuminate\Support\Carbon::parse($n['date'])->format('j F Y') }}</time></p>
                    <h3 class="mt-2 text-xl font-bold leading-snug">{{ $n['title'] }}</h3>
                    <p class="mt-2 text-ink-2">{{ $n['summary'] }}</p>
                    <a href="{{ $n['url'] }}" class="mt-auto inline-flex min-h-11 items-center gap-1.5 pt-3 font-semibold text-link" rel="noopener" target="_blank">Full press release<x-icon name="external" class="h-4 w-4" /><span class="sr-only">(opens glennonbrothers.ie)</span></a>
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.site>
