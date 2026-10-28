<x-layouts.site title="News" description="News from Glennon Brothers: awards, sponsorship, the Balcas acquisition and visits to the mills.">
    <x-page-header title="News from the mills" kicker="News" photo="troon-visit" caption="Troon, 2017"
        lead="The group's own press releases, newest first. Each links to the full release on glennonbrothers.ie." />
    <section class="wrap mt-12 md:mt-16">
        <ol class="divide-y divide-line border-y-2 border-brand">
            @foreach ((array) gb('news') as $n)
                <li class="grid gap-6 py-8 md:grid-cols-[10rem_minmax(0,1fr)_14rem]">
                    <p class="font-display text-lg font-bold"><time datetime="{{ $n['date'] }}">{{ \Illuminate\Support\Carbon::parse($n['date'])->format('j M Y') }}</time></p>
                    <div>
                        <h2 class="text-2xl font-bold leading-snug">{{ $n['title'] }}</h2>
                        <p class="mt-2 text-ink-2 measure">{{ $n['summary'] }}</p>
                        <a href="{{ $n['url'] }}" class="mt-2 inline-flex min-h-11 items-center gap-1.5 font-semibold text-link" rel="noopener" target="_blank">Full press release<x-icon name="external" class="h-4 w-4" /><span class="sr-only">(opens glennonbrothers.ie)</span></a>
                    </div>
                    @if ($n['photo'])
                        <x-photo :key="$n['photo']" alt="" sizes="14rem" class="aspect-[4/3] box-photo" />
                    @endif
                </li>
            @endforeach
        </ol>
    </section>
</x-layouts.site>
