<x-layouts.site title="Site map" description="Every page on the Glennon Brothers website.">
    <x-page-header photo="windymains-aerial" title="Site map" kicker="Site map" lead="Every public page on this website." />
    <section class="wrap mt-12 grid gap-10 md:mt-16 md:grid-cols-2 lg:grid-cols-4">
        <div><h2 class="text-lg font-bold">The group</h2><ul class="mt-3 space-y-1">
            @foreach (['home' => 'Home', 'about' => 'About the group', 'news' => 'News', 'sustainability' => 'Sustainability', 'contact' => 'Contact', 'privacy' => 'Privacy notice', 'credits' => 'Photo credits'] as $r => $l)
                <li><a class="inline-flex min-h-11 items-center text-link" href="{{ route($r) }}">{{ $l }}</a></li>
            @endforeach
        </ul></div>
        <div><h2 class="text-lg font-bold">Mills and products</h2><ul class="mt-3 space-y-1">
            <li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('plants.index') }}">All the mills</a></li>
            @foreach ((array) gb('plants') as $k => $p)<li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('plants.show', $k) }}">{{ $p['name'] }}</a></li>@endforeach
            <li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('products') }}">Products</a></li>
            <li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('frame.index') }}">Timber frame (DTE)</a></li>
        </ul></div>
        <div><h2 class="text-lg font-bold">Forms</h2><ul class="mt-3 space-y-1">
            <li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('quotes.create') }}">Trade quote</a></li>
            <li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('frame.create') }}">Timber frame enquiry</a></li>
            <li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('bookings.create') }}">Haulier slot booking</a></li>
            <li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('careers.apply') }}">Job application</a></li>
        </ul></div>
        <div><h2 class="text-lg font-bold">Careers</h2><ul class="mt-3 space-y-1">
            <li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('careers.index') }}">All openings</a></li>
            @foreach ($jobs as $j)<li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('careers.show', $j->slug) }}">{{ $j->title }}</a></li>@endforeach
        </ul></div>
    </section>
</x-layouts.site>
