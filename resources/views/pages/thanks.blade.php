<x-layouts.site title="Thank you" :mobile-bar="false">
    <x-page-header photo="lorry" title="Thank you. We have it." :kicker="$kind" code="OK" lead="Your reference number is below. We have emailed it to you as well."
        :ticket="['Reference' => $reference, 'Received' => now()->format('j M Y, H:i')]" />
    <section class="wrap mt-12 md:mt-16">
        <div class="max-w-xl">
            <div class="celebrate mb-12 flex items-center gap-4" role="status">
                <svg class="celebrate-mark h-16 w-16 flex-none" viewBox="0 0 64 64" aria-hidden="true">
                    <circle class="celebrate-ring" cx="32" cy="32" r="29" pathLength="1" />
                    <path class="celebrate-tick" d="M19 33 l9 9 l18 -19" pathLength="1" />
                    @foreach ([[-34, -18], [30, -26], [-26, 30], [36, 20], [4, -38], [-40, 6], [12, 38]] as $i => [$dx, $dy])
                        <rect class="celebrate-chip" x="30" y="30" width="5" height="3" rx="1" style="--dx: {{ $dx }}px; --dy: {{ $dy }}px; --d: {{ 450 + $i * 30 }}ms" />
                    @endforeach
                </svg>
                <p class="text-lg font-semibold">Sent. Your reference is <span class="num text-link">{{ $reference }}</span>.</p>
            </div>
            <x-ticket :title="gb('short_name')" :fields="['Reference' => $reference, 'Received' => now()->format('j F Y, H:i'), 'Submission' => $kind, 'Received by' => gb('short_name').', '.gb('address.city')]" foot="Quote this" :foot-value="$reference" />
            <p class="mt-6 text-ink-2">Quote the reference if you call or email, and we will find your submission straight away.</p>
        </div>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ tel_href(gb('phone')) }}" class="btn btn-outline"><x-icon name="phone" class="h-4 w-4" />{{ format_phone(gb('phone')) }}</a>
            <a href="{{ route('products') }}" class="btn btn-outline">Our products</a>
            <a href="{{ route('home') }}" class="btn btn-primary">Back to the home page</a>
        </div>
    </section>
</x-layouts.site>
