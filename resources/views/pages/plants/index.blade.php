@php($plants = (array) gb('plants'))
<x-layouts.site title="Our mills" description="Glennon Brothers runs four sawmills: Longford and Fermoy in Ireland, Windymains and Troon in Scotland. Addresses, phone numbers and what each mill makes.">
    <x-page-header title="Four sawmills, two countries" kicker="Our mills" photo="troon-harbour" position="center 60%" caption="Glennon Brothers sawmill, Troon harbour"
        :ticket="['Ireland' => 'Longford, Fermoy', 'Scotland' => 'Windymains, Troon', 'Head office' => 'Longford']"
        lead="Two mills in Ireland supply the Irish market and export; two in Scotland supply the UK. Each has its own yard team and phone line. Quotes go to the Ireland or UK sales team." />

    <section class="wrap mt-12 md:mt-16" aria-label="The mills">
        <ol class="grid gap-8">
            @foreach ($plants as $key => $p)
                <li class="grid overflow-hidden box md:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)]" data-reveal>
                    <x-photo :key="$p['photo']" :alt="$p['photo_alt']" sizes="(min-width: 768px) 45vw, 100vw" class="aspect-[4/3] md:aspect-auto md:min-h-full" />
                    <div class="flex flex-col p-6 md:p-8">
                        <p class="kicker text-ink-3">{{ $p['region'] === 'uk' ? 'Scotland' : 'Ireland' }} · {{ $p['since_label'] }}</p>
                        <h2 class="display-3 mt-2"><a href="{{ route('plants.show', $key) }}" class="hover:text-link">{{ $p['name'] }}</a></h2>
                        <p class="mt-1 font-semibold text-ink-2">{{ $p['role'] }} · {{ $p['company'] }}</p>
                        <p class="mt-4 text-ink-2 measure">{{ $p['summary'] }}</p>
                        <dl class="mt-6 grid gap-x-8 gap-y-4 border-t border-line pt-5 sm:grid-cols-2">
                            <div><dt class="kicker text-ink-3">Address</dt><dd class="mt-1"><address class="not-italic">{{ implode(', ', array_slice($p['address'], 0, 3)) }}</address></dd></div>
                            <div><dt class="kicker text-ink-3">Telephone</dt><dd class="mt-1"><a class="text-link inline-flex min-h-11 items-center font-semibold underline-offset-4 hover:underline" href="{{ tel_href($p['phone']) }}">{{ format_phone($p['phone']) }}</a></dd></div>
                        </dl>
                        <div class="mt-auto flex flex-wrap gap-3 pt-6">
                            <a href="{{ route('plants.show', $key) }}" class="btn btn-outline">About {{ $p['name'] }}<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                            <a href="{{ $p['map'] }}" class="btn btn-ghost" rel="noopener" target="_blank"><x-icon name="map-pin" class="h-4 w-4" />Map<span class="sr-only"> (opens Google Maps)</span></a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="wrap mt-16 grid gap-6 md:grid-cols-2">
        <div class="border-t-4 border-mark bg-surface p-6 shadow-sm">
            <h2 class="text-2xl font-bold">DTE Manufacturing, Longford</h2>
            <p class="mt-2 text-ink-2">Timber frame homes and roof trusses, made on the Longford sawmill site.</p>
            <a href="{{ route('frame.index') }}" class="mt-4 inline-flex min-h-11 items-center gap-1.5 font-semibold text-link">Timber frame<x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
        <div class="box border-t-4 border-t-brand p-6 shadow-sm">
            <h2 class="text-2xl font-bold">The wider group</h2>
            <p class="mt-2 text-ink-2">Since 2021 the group also includes Balcas, with its Enniskillen sawmill and renewable energy plants at Enniskillen and Invergordon. Balcas keeps its own website and sales team.</p>
            <a href="{{ route('about') }}" class="mt-4 inline-flex min-h-11 items-center gap-1.5 font-semibold text-link">About the group<x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
    </section>
</x-layouts.site>
