<x-layouts.site title="Sustainability" description="FSC® chain of custody certification, IPC licensing, ISPM 15, environmental product declarations and full use of every log at Glennon Brothers.">
    <x-page-header title="Every part of the log is used" kicker="Sustainability" code="S" photo="troon-cyclones"
        :ticket="['FSC® CoC' => 'Irish and UK plants', 'Packaging' => 'ISPM 15', 'Energy' => 'Biomass CHP, Troon']"
        lead="Irish and Scottish timber, sawn with almost no waste: chip, sawdust, bark and shavings all go to other industries, and sawdust fuels the kilns." />

    <section class="wrap mt-12 grid gap-12 md:mt-16 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div>
            <x-stamp code="01">Certification</x-stamp>
            <h2 class="display-3 mt-4">As the group publishes it</h2>
            <p class="hint mt-2">Codes as printed on glennonbrothers.ie. Check current status on the FSC public certificate search.</p>
            <dl class="mt-6 divide-y divide-line border-y-2 border-ink">
                @foreach ((array) gb('certifications') as $c)
                    <div class="py-5"><dt class="text-lg font-bold">{{ $c['title'] }}</dt><dd class="mt-1 text-ink-2">{{ $c['body'] }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="space-y-8">
            <figure class="relative">
                <x-photo key="forest" alt="A managed conifer plantation" sizes="(min-width: 1024px) 45vw, 100vw" class="aspect-[4/3] border-2 border-ink" />
                <span class="photo-note">Illustrative photo</span>
            </figure>
            <div class="border-2 border-ink bg-surface p-6">
                <h2 class="text-xl font-bold">Environmental Product Declarations</h2>
                <p class="mt-1 text-ink-2">The group publishes EPDs for three product groups.</p>
                <ul class="mt-3 space-y-1">
                    @foreach ((array) gb('epds') as $e)
                        <li><a href="{{ $e['url'] }}" class="inline-flex min-h-11 items-center gap-2 font-semibold text-link" rel="noopener" target="_blank"><x-icon name="download" class="h-4 w-4" />{{ $e['label'] }} (PDF)</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="mt-20 bg-surface-2 py-14 md:py-20">
        <div class="wrap">
            <x-stamp code="02">Nothing wasted</x-stamp>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ((array) gb('products.residues.items') as $i)
                    <div class="border-t-4 border-mark bg-surface p-6"><h3 class="text-xl font-bold">{{ $i['name'] }}</h3><p class="mt-2 text-ink-2">{{ $i['detail'] }}</p></div>
                @endforeach
            </div>
            <p class="mt-8 max-w-[65ch] text-ink-2">At Troon, a £14 million biomass combined heat and power plant first generated electricity on 17 March 2017. It powers the site and can export up to 2.5 MW to the National Grid.</p>
        </div>
    </section>
</x-layouts.site>
