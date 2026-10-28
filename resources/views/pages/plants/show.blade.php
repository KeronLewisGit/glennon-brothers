@php
    $uk = $plant['region'] === 'uk';
    $team = $uk ? 'UK sales' : 'Ireland sales';
@endphp
<x-layouts.site :title="$plant['name'].' sawmill'" :description="$plant['name'].': '.$plant['summary']" :og-image="$plant['photo']">
    <x-page-header :title="$plant['name']" :kicker="$plant['role']" :code="strtoupper(substr($key, 0, 2))" :photo="$plant['photo']" :caption="$plant['company']"
        :ticket="['Mill' => $plant['name'], 'Company' => $plant['company'], 'Country' => $uk ? 'Scotland' : 'Ireland', 'Sales team' => $team]"
        :lead="$plant['summary']">
        <nav aria-label="Breadcrumb" class="mt-6 text-sm text-white/75"><a href="{{ route('plants.index') }}" class="inline-flex min-h-11 items-center gap-1.5 hover:text-white"><x-icon name="arrow-left" class="h-4 w-4" />All the mills</a></nav>
    </x-page-header>

    <div class="wrap mt-12 grid gap-12 md:mt-16 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div>
            <x-stamp code="01">The mill</x-stamp>
            <div class="prose-copy mt-5 measure">
                @foreach ($plant['story'] as $para)<p>{{ $para }}</p>@endforeach
            </div>
            <dl class="mt-10 grid gap-6 border-t-2 border-ink pt-6 sm:grid-cols-3">
                @foreach ($plant['facts'] as [$label, $value])
                    <div><dt class="kicker text-ink-3">{{ $label }}</dt><dd class="font-display mt-1 text-xl font-bold">{{ $value }}</dd></div>
                @endforeach
            </dl>

            <div class="mt-14">
                <x-stamp code="02">Jobs at {{ $plant['name'] }}</x-stamp>
                @if ($jobs->isEmpty())
                    <p class="mt-5 text-ink-2 measure">No openings posted for {{ $plant['name'] }} right now. Send a general application and HR will keep it for the next opening here.</p>
                @else
                    <ul class="mt-5 divide-y divide-line border-y border-line">
                        @foreach ($jobs as $job)
                            <li><a href="{{ route('careers.show', $job) }}" class="flex min-h-14 flex-wrap items-center justify-between gap-2 py-3 font-semibold hover:text-link"><span>{{ $job->title }} @if($job->is_demo)<span class="demo-badge ml-2">Sample</span>@endif</span><span class="text-sm font-normal text-ink-3">{{ $job->employment_type->label() }}</span></a></li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('careers.index', ['plant' => $key]) }}" class="btn btn-outline mt-6">Careers at {{ $plant['name'] }}<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="border-2 border-ink bg-surface p-6">
                <h2 class="text-xl font-bold">Contact {{ $plant['name'] }}</h2>
                <address class="mt-3 not-italic text-ink-2">{{ $plant['company'] }}<br>{!! implode('<br>', array_map('e', $plant['address'])) !!}</address>
                <p class="mt-4"><a href="{{ tel_href($plant['phone']) }}" class="font-display inline-flex min-h-11 items-center gap-2 text-xl font-bold text-link"><x-icon name="phone" class="h-5 w-5" />{{ format_phone($plant['phone']) }}</a></p>
                <p><a href="mailto:{{ $plant['email'] }}" class="inline-flex min-h-11 items-center gap-2 break-all text-link"><x-icon name="mail" class="h-4 w-4 flex-none" />{{ $plant['email'] }}</a></p>
                <a href="{{ $plant['map'] }}" class="btn btn-outline mt-4 w-full" rel="noopener" target="_blank"><x-icon name="map-pin" class="h-4 w-4" />Open in Google Maps</a>
            </div>
            <div class="bg-slate-900 p-6 text-white on-dark">
                <h2 class="text-xl font-bold">Buying timber?</h2>
                <p class="mt-2 text-white/80">Trade quotes for {{ $plant['name'] }} go to the {{ $team }} team and are priced in {{ $uk ? 'sterling' : 'euro' }}.</p>
                <a href="{{ route('quotes.create') }}" class="btn btn-light mt-4 w-full">Request a trade quote</a>
                <a href="{{ route('bookings.create', ['plant' => $key]) }}" class="btn btn-on-dark mt-3 w-full"><x-icon name="truck" class="h-4 w-4" />Book a haulier slot</a>
            </div>
        </aside>
    </div>
</x-layouts.site>
