@php
    $plants = (array) gb('plants');
    $products = (array) gb('products');
    $news = array_slice((array) gb('news'), 0, 3);
    $productIcons = ['construction' => 'stack', 'fencing' => 'fence', 'pallet' => 'pallet', 'decking' => 'layers', 'residues' => 'leaf'];
@endphp
<x-layouts.site>
    {{-- Hero: the home page's own version of the shared header band. --}}
    <section class="rings relative isolate overflow-hidden bg-slate-900 text-white on-dark">
        <div class="hero-photo absolute inset-0 -z-10">
            <x-photo key="troon-harbour" alt="" :eager="true" sizes="100vw" class="h-full w-full" position="center 60%" />
            <div class="hero-scrim absolute inset-0"></div>
        </div>
        <div class="wrap grid gap-10 pb-14 pt-12 md:pb-24 md:pt-24 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-end">
            <div class="min-w-0">
                <x-stamp code="1913">Longford · Fermoy · Windymains · Troon</x-stamp>
                <h1 class="display-1 mt-6 max-w-[16ch]">Irish and Scottish timber, sawn by one family since 1913.</h1>
                <p class="lead mt-6 max-w-[56ch] text-white/85">Four sawmills supply construction timber, CLS, fencing and pallet wood to merchants and manufacturers across Ireland and the UK. DTE, on our Longford site, builds timber frame homes and roof trusses.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('quotes.create') }}" class="btn btn-light">Request a trade quote<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    <a href="{{ route('plants.index') }}" class="btn btn-on-dark">See the four mills</a>
                </div>
            </div>
            <x-ticket class="hidden rotate-[-1.5deg] lg:block" title="Glennon Brothers" :fields="['Founded' => 'Longford, '.gb('founded'), 'Mills' => 'Longford, Fermoy, Windymains, Troon', 'Markets' => 'Ireland and the UK', 'Timber frame' => 'DTE Manufacturing']" foot="Telephone" :foot-value="format_phone(gb('phone'))" />
        </div>
        <span class="photo-note" style="bottom: 2.9rem">Glennon Brothers sawmill, Troon harbour</span>
        <div class="bale tall" aria-hidden="true"></div>
    </section>

    {{-- The mills --}}
    <section class="wrap mt-16 md:mt-24" aria-labelledby="mills-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <x-stamp code="01">Four mills, two countries</x-stamp>
                <h2 id="mills-title" class="display-2 mt-4 max-w-[22ch]">From a Longford flax mill to the harbour at Troon.</h2>
            </div>
            <a href="{{ route('plants.index') }}" class="btn btn-outline">All the mills<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
        </div>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($plants as $key => $p)
                <article class="card-link group flex flex-col border-2 border-ink bg-surface" data-reveal style="--reveal-delay: {{ $loop->index * 70 }}ms">
                    <x-photo :key="$p['photo']" :alt="$p['photo_alt']" sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" class="card-img aspect-[4/3]" />
                    <div class="flex flex-1 flex-col p-5">
                        <p class="kicker text-ink-3">{{ $p['region'] === 'uk' ? 'Scotland' : 'Ireland' }} · {{ $p['since_label'] }}</p>
                        <h3 class="mt-2 text-2xl font-bold"><a class="stretch" href="{{ route('plants.show', $key) }}">{{ $p['name'] }}</a></h3>
                        <p class="mt-1 font-semibold text-ink-2">{{ $p['role'] }}</p>
                        <p class="mt-3 text-ink-2">{{ $p['summary'] }}</p>
                        <span class="mt-auto inline-flex items-center gap-1.5 pt-4 font-semibold text-link">Visit {{ $p['name'] }}<x-icon name="arrow-right" class="h-4 w-4" /></span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Sourced figures --}}
    <section class="mt-20 bg-surface-2 py-14 md:mt-28 md:py-20" aria-labelledby="figures-title">
        <div class="wrap">
            <h2 id="figures-title" class="sr-only">The group in figures</h2>
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="figure-stamp"><b>{{ gb('founded') }}</b><span class="mt-2 block text-ink-2">William and James Glennon open a sawmill on the Longford flax-mill site</span></div>
                @foreach ((array) gb('figures') as $f)
                    <div class="figure-stamp"><b>{{ $f['value'] }}</b><span class="mt-2 block text-ink-2">{{ ucfirst($f['label']) }}</span></div>
                @endforeach
                <div class="figure-stamp"><b>6×</b><span class="mt-2 block text-ink-2">TTJ Softwood Trader / Sawmiller of the Year, most recently in September 2025</span></div>
            </div>
            <p class="hint mt-8">Figures as published on glennonbrothers.ie and in the group's own press releases.</p>
        </div>
    </section>

    {{-- Products --}}
    <section class="wrap mt-20 md:mt-28" aria-labelledby="products-title">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:items-start">
            <div class="lg:sticky lg:top-28">
                <x-stamp code="02">What leaves the yard</x-stamp>
                <h2 id="products-title" class="display-2 mt-4">Graded, dried and treated for the job it will do.</h2>
                <p class="mt-5 text-ink-2 measure">Kiln-dried, strength-graded carcassing and CLS for builders and timber frame makers; posts, rails and boards for fencing; pallet and packaging timber cut to length. Nothing is wasted: chip, sawdust, bark and shavings all go to other industries.</p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach (\App\Enums\Treatment::cases() as $t)
                        @continue($t === \App\Enums\Treatment::Any)
                        <span class="chip" data-tone="neutral"><x-icon name="check" />{{ $t->label() }}</span>
                    @endforeach
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('products') }}" class="btn btn-outline">Product sizes<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    <a href="{{ route('quotes.create') }}" class="btn btn-primary">Ask for prices</a>
                </div>
            </div>
            <ul class="grid border-l-2 border-t-2 border-ink sm:grid-cols-2">
                @foreach ($products as $key => $prod)
                    <li class="border-b-2 border-r-2 border-ink" data-reveal>
                        <a href="{{ route('products') }}#{{ $key }}" class="group flex h-full flex-col gap-3 bg-surface p-6 hover:bg-surface-2">
                            <x-icon :name="$productIcons[$key] ?? 'stack'" class="h-7 w-7 text-mark" />
                            <span class="font-display text-[1.375rem] font-bold leading-snug">{{ $prod['title'] }}</span>
                            <span class="text-ink-2">{{ \Illuminate\Support\Str::limit($prod['intro'], 130) }}</span>
                        </a>
                    </li>
                @endforeach
                <li class="border-b-2 border-r-2 border-ink">
                    <a href="{{ route('frame.index') }}" class="group flex h-full flex-col gap-3 bg-slate-900 p-6 text-white on-dark hover:bg-slate-800">
                        <x-icon name="frame" class="h-7 w-7 text-[#ff8a98]" />
                        <span class="font-display text-[1.375rem] font-bold leading-snug">Timber frame and roof trusses</span>
                        <span class="text-white/80">Designed, made and erected by DTE in Longford, for self-builders and developers.</span>
                    </a>
                </li>
            </ul>
        </div>
    </section>

    {{-- Timber frame --}}
    <section class="mt-20 md:mt-28" aria-labelledby="frame-title">
        <div class="wrap grid items-center gap-10 lg:grid-cols-2">
            <div class="relative">
                <x-photo key="frame-house" alt="A two-storey timber frame house under construction, its wall panels and roof trusses in place" sizes="(min-width: 1024px) 50vw, 100vw" class="aspect-[4/3] border-2 border-ink" />
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
        <div class="rings relative isolate overflow-hidden bg-slate-900 p-8 text-white on-dark md:p-12">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-end">
                <div>
                    <x-stamp code="{{ $openJobs }}">{{ $openJobs === 1 ? 'Opening' : 'Openings' }} across the group</x-stamp>
                    <h2 id="careers-title" class="display-2 mt-4">Operatives, fitters, electricians, drivers and erectors.</h2>
                    <p class="mt-5 max-w-[56ch] text-white/85">The mills run shifts all year, and DTE hires timber frame erectors. Apply to one plant or to any of them; operatives and drivers need no CV.</p>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('careers.index') }}" class="btn btn-light">See the openings<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    <a href="{{ route('careers.apply') }}" class="btn btn-on-dark">General application</a>
                </div>
            </div>
            <ul class="mt-10 grid gap-3 border-t border-white/15 pt-8 sm:grid-cols-2 lg:grid-cols-5">
                @foreach (\App\Enums\JobCategory::cases() as $c)
                    <li><a href="{{ route('careers.index', ['family' => $c->value]) }}" class="flex min-h-12 items-center gap-2 font-semibold text-white/90 hover:text-white"><x-tree class="h-4 w-7 flex-none text-[#ff8a98]" />{{ $c->label() }}</a></li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Trade and haulage --}}
    <section class="wrap mt-20 grid gap-6 md:mt-28 md:grid-cols-2" aria-label="For trade customers and hauliers">
        <a href="{{ route('quotes.create') }}" class="group flex flex-col gap-3 border-2 border-ink bg-surface p-7 hover:bg-surface-2">
            <x-icon name="calculator" class="h-8 w-8 text-mark" />
            <span class="font-display text-2xl font-bold">Trade and merchant quotes</span>
            <span class="text-ink-2">List the products, sizes, treatment and quantities, and where each load is going. Your request goes straight to the Ireland or UK sales team, who reply in euro or sterling.</span>
            <span class="mt-2 inline-flex items-center gap-1.5 font-semibold text-link">Request a quote<x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" /></span>
        </a>
        <a href="{{ route('bookings.create') }}" class="group flex flex-col gap-3 border-2 border-ink bg-surface p-7 hover:bg-surface-2">
            <x-icon name="truck" class="h-8 w-8 text-mark" />
            <span class="font-display text-2xl font-bold">Hauliers: book a yard slot</span>
            <span class="text-ink-2">Collecting sawn timber, delivering logs or taking residues away? Ask for a loading or tipping slot at any of the four mills, and the yard will confirm it.</span>
            <span class="mt-2 inline-flex items-center gap-1.5 font-semibold text-link">Book a slot<x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" /></span>
        </a>
    </section>

    {{-- News --}}
    <section class="wrap mt-20 md:mt-28" aria-labelledby="news-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <x-stamp code="03">From the press page</x-stamp>
                <h2 id="news-title" class="display-2 mt-4">News</h2>
            </div>
            <a href="{{ route('news') }}" class="btn btn-outline">All news<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach ($news as $n)
                <article class="flex flex-col border-t-4 border-mark bg-surface p-6 shadow-sm" data-reveal>
                    <p class="kicker text-ink-3"><time datetime="{{ $n['date'] }}">{{ \Illuminate\Support\Carbon::parse($n['date'])->format('j F Y') }}</time></p>
                    <h3 class="mt-2 text-xl font-bold leading-snug">{{ $n['title'] }}</h3>
                    <p class="mt-2 text-ink-2">{{ $n['summary'] }}</p>
                    <a href="{{ $n['url'] }}" class="mt-auto inline-flex min-h-11 items-center gap-1.5 pt-3 font-semibold text-link" rel="noopener" target="_blank">Full press release<x-icon name="external" class="h-4 w-4" /><span class="sr-only">(opens glennonbrothers.ie)</span></a>
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.site>
