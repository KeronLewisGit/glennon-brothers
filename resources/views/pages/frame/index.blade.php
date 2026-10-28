@php($dte = (array) gb('dte'))
<x-layouts.site title="Timber frame homes and roof trusses" description="DTE Manufacturing, on the Glennon Brothers site in Longford, designs, makes and erects timber frame homes and engineered roof trusses. Send your drawings online.">
    <x-page-header title="Timber frame homes and roof trusses" kicker="DTE Manufacturing" code="DTE" photo="frame-house"
        :ticket="['Company' => $dte['name'], 'Site' => 'Longford', 'In the group' => 'Since '.$dte['since'], 'Approved' => 'NSAI']" ticket-title="DTE Manufacturing"
        lead="DTE, trading as Dempsey Timber Engineering, designs, makes and erects timber frame homes and engineered roof trusses for builders, developers and self-builders, from the Glennon Brothers site in Longford.">
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('frame.create') }}" class="btn btn-light">Start an enquiry<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            <a href="tel:{{ phone_digits($dte['phone']) }}" class="btn btn-on-dark"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($dte['phone']) }}</a>
        </div>
    </x-page-header>

    <section class="wrap mt-12 grid gap-12 md:mt-16 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
        <div>
            <x-stamp code="01">What DTE does</x-stamp>
            <div class="prose-copy mt-5 measure">
                <p>DTE joined the group in 2007, when Glennon Brothers acquired Dempsey Timber Engineering. It has since moved to the Longford sawmill site, so the frames are made a short distance from where much of the timber is sawn and dried.</p>
                <p>It makes timber frame wall panels, floors and engineered roof trusses for one-off homes, extensions, housing schemes and commercial buildings, and is approved and monitored by the National Standards Authority of Ireland.</p>
                <p>In Scotland, the group's sister business Alexanders Timber Design in Irvine serves the UK timber frame market.</p>
            </div>
        </div>
        <figure>
            <x-photo key="trusses" alt="Engineered roof trusses stacked in the yard ready for delivery" sizes="(min-width: 1024px) 40vw, 100vw" class="aspect-[4/3] border-2 border-ink" />
            <figcaption class="photo-caption">Roof trusses, from the group's own website.</figcaption>
        </figure>
    </section>

    <section class="mt-20 bg-surface-2 py-14 md:py-20" aria-labelledby="steps-title">
        <div class="wrap">
            <x-stamp code="02">How an enquiry works</x-stamp>
            <h2 id="steps-title" class="display-3 mt-4">From your drawings to a frame on site</h2>
            <ol class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Send your drawings', 'Plans, elevations and sections in PDF, DWG, DXF or image files, with the site address, county and Eircode if there is one.'],
                    ['Tell us the timeline', 'Where you are with planning and the month you hope to start on site, so the design office can plan the work.'],
                    ['Design review', 'The DTE team reviews the drawings and comes back with questions or a quotation. New drawings? Use the private link in your confirmation email.'],
                    ['Manufacture and erection', 'Once ordered, the frame is made in Longford and erected on your prepared foundations.'],
                ] as $i => [$h, $t])
                    <li class="border-t-4 border-ink bg-surface p-6">
                        <span class="step-n">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-4 text-xl font-bold">{{ $h }}</h3>
                        <p class="mt-2 text-ink-2">{{ $t }}</p>
                    </li>
                @endforeach
            </ol>
            <a href="{{ route('frame.create') }}" class="btn btn-primary mt-10">Start a timber frame enquiry<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
        </div>
    </section>

    <section class="wrap mt-16 grid gap-6 md:grid-cols-2">
        <div class="border-2 border-ink bg-surface p-6">
            <h2 class="text-xl font-bold">{{ $dte['name'] }}</h2>
            <address class="mt-3 not-italic text-ink-2">{!! implode('<br>', array_map('e', $dte['address'])) !!}</address>
            <p class="mt-3"><a href="mailto:{{ $dte['email'] }}" class="text-link inline-flex min-h-11 items-center">{{ $dte['email'] }}</a></p>
        </div>
        <div class="border-2 border-ink bg-surface p-6">
            <h2 class="text-xl font-bold">Building in the UK?</h2>
            <p class="mt-2 text-ink-2">Alexanders Timber Design, {{ gb('atd.address') }}.</p>
            <p class="mt-3"><a href="{{ tel_href(gb('atd.phone')) }}" class="text-link inline-flex min-h-11 items-center">{{ format_phone(gb('atd.phone')) }}</a></p>
        </div>
    </section>
</x-layouts.site>
