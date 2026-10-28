@php
    $d = (array) gb('directors');
    $timeline = (array) gb('timeline');
@endphp
<x-layouts.site title="About the group" description="Glennon Brothers: a family-run Irish timber processor founded in Longford in 1913 by William and James Glennon, now run by joint managing directors Pat and Mike Glennon.">
    <x-page-header title="A family sawmilling business since 1913" kicker="About the group" code="1913" photo="centenary" caption="The group's centenary, 2013"
        :ticket="['Founded' => 'Longford, '.gb('founded'), 'By' => 'William and James Glennon', 'Run by' => $d['names']]"
        lead="Four generations on, Glennon Brothers is still owned and run by the family that bought a disused Longford flax mill in 1913." />

    <section class="wrap mt-12 grid gap-12 md:mt-16 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
        <div>
            <x-stamp code="01">The family</x-stamp>
            <div class="prose-copy mt-5 measure">
                <p>Brothers William and James Glennon returned from the United States, bought the flax mill site and buildings on the Dublin Road in Longford in 1913, and set up a sawmill. The business moved from sawing hardwoods for local uses to the softwood operation it runs today.</p>
                <p>{{ $d['text'] }}</p>
                <p>Today the group processes Irish and Scottish timber at Longford, Fermoy, Windymains and Troon, makes timber frame homes and roof trusses through DTE, and since 2021 includes Balcas.</p>
            </div>
        </div>
        <figure>
            <x-photo key="directors" alt="Pat and Mike Glennon, joint managing directors of Glennon Brothers" sizes="(min-width: 1024px) 40vw, 100vw" class="aspect-[4/3] border-2 border-ink" />
            <figcaption class="mt-3"><span class="font-semibold">{{ $d['names'] }}</span>, {{ $d['title'] }}. <span class="photo-caption">From the group's own website.</span></figcaption>
        </figure>
    </section>

    <section id="history" class="mt-20 bg-surface-2 py-14 md:py-20" aria-labelledby="timeline-title">
        <div class="wrap">
            <x-stamp code="02">Growth rings</x-stamp>
            <h2 id="timeline-title" class="display-3 mt-4">From water power to biomass CHP</h2>
            <ol class="timeline mt-10">
                @foreach ($timeline as [$year, $text])
                    <li class="grid grid-cols-[4.5rem_minmax(0,1fr)] gap-4 border-t border-line-strong py-4 sm:grid-cols-[7rem_minmax(0,1fr)]">
                        <span class="font-display num text-2xl font-bold text-mark">{{ $year }}</span>
                        <span class="pt-1 text-ink-2 measure">{{ $text }}</span>
                    </li>
                @endforeach
            </ol>
            <p class="hint mt-4">From the history page of glennonbrothers.ie and the group's press releases.</p>
        </div>
    </section>

    <section class="wrap mt-20" aria-labelledby="awards-title">
        <x-stamp code="03">Awards</x-stamp>
        <h2 id="awards-title" class="display-3 mt-4">Recognised by the trade</h2>
        <div class="mt-8 grid gap-5 md:grid-cols-2">
            @foreach ((array) gb('awards') as $a)
                <div class="flex gap-4 border-2 border-ink bg-surface p-6">
                    <x-icon name="award" class="h-8 w-8 flex-none text-mark" />
                    <div>
                        <p class="kicker text-ink-3">{{ $a['by'] }} · {{ $a['year'] }}</p>
                        <h3 class="mt-1 text-xl font-bold">{{ $a['title'] }}</h3>
                        @if ($a['note'])<p class="text-ink-2">{{ $a['note'] }}</p>@endif
                        <a href="{{ $a['url'] }}" class="mt-2 inline-flex min-h-11 items-center gap-1.5 text-link" rel="noopener" target="_blank">Press release<x-icon name="external" class="h-4 w-4" /></a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="wrap mt-16">
        <div class="border-t-2 border-ink pt-6">
            <h2 class="kicker text-ink-3">The group's brands</h2>
            <ul class="mt-3 flex flex-wrap gap-x-8 gap-y-2 font-display text-xl font-bold">
                @foreach ((array) gb('brands') as $b)<li>{{ $b }}</li>@endforeach
            </ul>
        </div>
    </section>
</x-layouts.site>
