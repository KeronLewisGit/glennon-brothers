@php
    $mills = \App\Enums\Plant::cases();
    $total = $plantCounts->sum();
@endphp
<x-layouts.site title="Careers" description="Jobs at Glennon Brothers: general operatives, maintenance fitters and electricians, HGV drivers and timber frame erectors at Longford, Fermoy, Windymains, Troon and DTE.">
    <x-page-header title="Work at the mills" kicker="Careers" code="C" photo="troon-mill"
        :ticket="['Plants' => 'Longford, Fermoy, Windymains, Troon', 'Timber frame' => 'DTE, Longford', 'Openings' => (string) $total]"
        lead="The mills hire all year: operatives for the saw, grading and planing lines, maintenance fitters and electricians, drivers, and timber frame erectors for DTE. Pick an opening, or send one application for any plant." />

    <section class="wrap mt-10 md:mt-14" aria-label="Filter openings">
        <div class="space-y-4">
            <nav class="tag-row" aria-label="Plant">
                <a href="{{ route('careers.index', array_filter(['family' => $category?->value])) }}" class="tag" @if(! $plant) aria-current="true" @endif>All plants <span class="count">{{ $total }}</span></a>
                @foreach ($mills as $p)
                    <a href="{{ route('careers.index', array_filter(['plant' => $p->value, 'family' => $category?->value])) }}" class="tag" @if($plant === $p) aria-current="true" @endif>{{ $p->label() }} <span class="count">{{ $plantCounts[$p->value] ?? 0 }}</span></a>
                @endforeach
            </nav>
            <nav class="tag-row" aria-label="Role family">
                <a href="{{ route('careers.index', array_filter(['plant' => $plant?->value])) }}" class="tag" @if(! $category) aria-current="true" @endif>All roles</a>
                @foreach (\App\Enums\JobCategory::cases() as $c)
                    <a href="{{ route('careers.index', array_filter(['plant' => $plant?->value, 'family' => $c->value])) }}" class="tag" @if($category === $c) aria-current="true" @endif>{{ $c->short() }}</a>
                @endforeach
            </nav>
        </div>

        <div class="mt-10 flex flex-wrap items-end justify-between gap-4">
            <h2 class="display-3">{{ $jobs->isEmpty() ? 'No openings match' : $jobs->count().' '.\Illuminate\Support\Str::plural('opening', $jobs->count()) }}</h2>
            <a href="{{ route('careers.apply') }}" class="btn btn-outline">General application<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
        </div>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
            @forelse ($jobs as $job)
                <article class="card-link border-2 border-ink bg-surface p-6" data-reveal>
                    <p class="kicker flex items-center gap-2 text-ink-3"><x-tree class="h-3.5 w-6 text-mark" />{{ $job->plant->label() }} · {{ $job->category->short() }}</p>
                    <h3 class="mt-2 text-[1.375rem] font-bold leading-snug"><a class="stretch" href="{{ route('careers.show', $job) }}">{{ $job->title }}</a></h3>
                    <p class="mt-2 text-ink-2">{{ $job->summary }}</p>
                    <p class="mt-4 flex flex-wrap gap-x-5 gap-y-1 text-sm text-ink-3">
                        <span class="inline-flex items-center gap-1.5"><x-icon name="briefcase" class="h-4 w-4" />{{ $job->employment_type->label() }}</span>
                        @if ($job->hours)<span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="h-4 w-4" />{{ $job->hours }}</span>@endif
                        @if ($job->closes_on)<span class="inline-flex items-center gap-1.5"><x-icon name="calendar" class="h-4 w-4" />Closes {{ $job->closes_on->format('j M') }}</span>@endif
                    </p>
                    @if ($job->is_demo)<p class="mt-4"><span class="demo-badge">Sample posting</span></p>@endif
                </article>
            @empty
                <p class="border-2 border-line-strong bg-surface p-6 text-ink-2 md:col-span-2">Nothing posted for this choice right now. Send a general application and HR will keep it for the next opening.</p>
            @endforelse
        </div>
    </section>

    <section class="wrap mt-20 grid items-center gap-10 lg:grid-cols-2">
        <figure>
            <x-photo key="operator" alt="A Glennon Brothers operator at the controls in the mill" sizes="(min-width: 1024px) 45vw, 100vw" class="aspect-[4/3] border-2 border-ink" />
            <figcaption class="photo-caption">From the group's own website.</figcaption>
        </figure>
        <div class="space-y-8">
            @foreach ((array) gb('careers.points') as $i => $p)
                <div class="flex gap-4"><span class="step-n">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span><div><h3 class="text-xl font-bold">{{ $p['title'] }}</h3><p class="mt-1 text-ink-2">{{ $p['text'] }}</p></div></div>
            @endforeach
        </div>
    </section>
</x-layouts.site>
