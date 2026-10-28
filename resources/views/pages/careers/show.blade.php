@php
    $plantData = (array) gb('plants.'.$job->plant->value, gb('plants.longford'));
    $address = $job->plant === \App\Enums\Plant::Dte ? (array) gb('dte.address') : (array) $plantData['address'];
    $ld = ['@context' => 'https://schema.org', '@type' => 'JobPosting', 'title' => $job->title, 'description' => $job->summary,
        'datePosted' => $job->created_at->toDateString(), 'employmentType' => $job->employment_type->schema(),
        'hiringOrganization' => ['@type' => 'Organization', 'name' => gb('name'), 'sameAs' => route('home')],
        'jobLocation' => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $address[1] ?? '', 'postalCode' => $address[2] ?? '', 'addressCountry' => $job->plant->region() === \App\Enums\Region::Uk ? 'GB' : 'IE']]]
        + ($job->closes_on ? ['validThrough' => $job->closes_on->endOfDay()->toIso8601String()] : []);
@endphp
<x-layouts.site :title="$job->title" :description="$job->summary" :json-ld="[$ld]">
    <x-page-header :title="$job->title" :kicker="$job->plant->label().' · '.$job->category->short()" code="C" photo="troon-mill" :lead="$job->summary"
        :ticket="['Plant' => $job->plant->label(), 'Role' => $job->category->label(), 'Terms' => $job->employment_type->label(), 'Closes' => $job->closes_on?->format('j F Y') ?? 'Open until filled']">
        <div class="mt-6 flex flex-wrap gap-3"><a href="{{ route('careers.apply', ['job' => $job->slug]) }}" class="btn btn-light">Apply for this job<x-icon name="arrow-right" class="nudge h-4 w-4" /></a><a href="{{ route('careers.index') }}" class="btn btn-on-dark">All openings</a></div>
    </x-page-header>
    <section class="wrap mt-12 grid gap-12 md:mt-16 lg:grid-cols-2">
        @if ($job->dutyList())
            <div><x-stamp code="01">The work</x-stamp><ul class="ticked mt-5 space-y-2.5">@foreach ($job->dutyList() as $d)<li>{{ $d }}</li>@endforeach</ul></div>
        @endif
        @if ($job->requirementList())
            <div><x-stamp code="02">You bring</x-stamp><ul class="ticked mt-5 space-y-2.5">@foreach ($job->requirementList() as $d)<li>{{ $d }}</li>@endforeach</ul></div>
        @endif
    </section>
    <section class="wrap mt-12">
        <dl class="grid gap-6 border-y-2 border-ink py-6 sm:grid-cols-3">
            <div><dt class="kicker text-ink-3">Where</dt><dd class="mt-1">{{ implode(', ', array_slice($address, 0, 3)) }}</dd></div>
            <div><dt class="kicker text-ink-3">Hours</dt><dd class="mt-1">{{ $job->hours ?: $job->employment_type->label() }}</dd></div>
            <div><dt class="kicker text-ink-3">CV</dt><dd class="mt-1">{{ $job->category->needsCv() ? 'Please attach one' : 'Not needed' }}</dd></div>
        </dl>
        @if ($job->is_demo)<p class="mt-6"><span class="demo-badge">Sample posting for the demo</span></p>@endif
        <a href="{{ route('careers.apply', ['job' => $job->slug]) }}" class="btn btn-primary mt-8">Apply for this job<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
    </section>
</x-layouts.site>
