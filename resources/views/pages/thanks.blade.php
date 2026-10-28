<x-layouts.site title="Thank you" :mobile-bar="false">
    <x-page-header photo="lorry" title="Thank you. We have it." :kicker="$kind" code="OK" lead="Your reference number is below. We have emailed it to you as well."
        :ticket="['Reference' => $reference, 'Received' => now()->format('j M Y, H:i')]" />
    <section class="wrap mt-12 md:mt-16">
        <div class="max-w-xl">
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
