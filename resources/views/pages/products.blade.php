@php
    $products = (array) gb('products');
    $icons = ['construction' => 'stack', 'fencing' => 'fence', 'pallet' => 'pallet', 'decking' => 'layers', 'residues' => 'leaf'];
@endphp
<x-layouts.site title="Products" description="Kiln-dried strength-graded carcassing, CLS, fencing, Glendeck decking, pallet and packaging timber, and wood residues from Glennon Brothers' four sawmills.">
    <x-page-header title="Sawn, dried, graded and treated" kicker="Products" code="P" photo="troon-stacks"
        :ticket="['Species' => 'Home-grown spruce', 'Drying' => 'Kiln dried or green', 'Treatment' => 'Pressure or heat', 'Sizes' => 'Millimetres']"
        lead="Construction timber, fencing, decking and pallet wood from Irish and Scottish forests, and a use for every by-product of the log. Sizes below are as the group lists them, in millimetres.">
        <nav aria-label="Product groups" class="tag-row mt-8">
            @foreach ($products as $key => $p)
                <a href="#{{ $key }}" class="tag">{{ $p['title'] }}</a>
            @endforeach
        </nav>
    </x-page-header>

    <div class="wrap mt-12 space-y-16 md:mt-16 md:space-y-24">
        @foreach ($products as $key => $p)
            <section id="{{ $key }}" class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:gap-14" aria-labelledby="{{ $key }}-title">
                <div @class(['lg:order-2' => $loop->even])>
                    @if ($p['photo'])
                        <x-photo :key="$p['photo']" :alt="$p['photo_alt']" sizes="(min-width: 1024px) 45vw, 100vw" class="aspect-[4/3] border-2 border-ink" />
                    @else
                        <div class="kerf-grid flex aspect-[4/3] items-end border-2 border-ink bg-surface-2 p-6 text-ink" aria-hidden="true">
                            <x-icon :name="$icons[$key]" class="h-16 w-16 text-mark" />
                        </div>
                    @endif
                </div>
                <div class="min-w-0">
                    <x-stamp :code="str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT)">Product line</x-stamp>
                    <h2 id="{{ $key }}-title" class="display-3 mt-4">{{ $p['title'] }}</h2>
                    <p class="mt-4 text-ink-2 measure">{{ $p['intro'] }}</p>
                    <dl class="mt-6 divide-y divide-line border-y-2 border-ink">
                        @foreach ($p['items'] as $item)
                            <div class="grid gap-1 py-4 sm:grid-cols-[13rem_minmax(0,1fr)] sm:gap-6">
                                <dt class="font-display text-lg font-bold">{{ $item['name'] }}</dt>
                                <dd class="text-ink-2">{{ $item['detail'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    @if ($p['note'])<p class="hint mt-4 measure">{{ $p['note'] }}</p>@endif
                    <a href="{{ route('quotes.create') }}" class="btn btn-outline mt-6">Ask for a price<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                </div>
            </section>
            @unless ($loop->last)<div class="tree-rule" aria-hidden="true"><x-tree /></div>@endunless
        @endforeach
    </div>

    <section class="wrap mt-20">
        <div class="rings relative isolate overflow-hidden bg-slate-900 p-8 text-white on-dark md:p-12">
            <h2 class="display-3">Trade, merchant and manufacturer pricing</h2>
            <p class="mt-3 max-w-[60ch] text-white/85">List what you need line by line: product, section size, length, treatment, quantity and delivery site. Ireland quotes come in euro, UK quotes in sterling.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('quotes.create') }}" class="btn btn-light">Request a trade quote<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                <a href="{{ route('sustainability') }}" class="btn btn-on-dark">Certification and EPDs</a>
            </div>
        </div>
    </section>
</x-layouts.site>
