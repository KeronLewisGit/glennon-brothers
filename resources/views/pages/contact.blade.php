@php
    $plants = (array) gb('plants');
    $dte = (array) gb('dte');
    $atd = (array) gb('atd');
@endphp
<x-layouts.site title="Contact" description="Contact Glennon Brothers: head office in Longford, the mills at Fermoy, Windymains and Troon, and DTE Manufacturing. Phone numbers, email and an enquiry form.">
    <x-page-header title="Contact the mills" kicker="Contact" code="K" photo="troon-entrance"
        :ticket="['Head office' => 'Longford', 'Telephone' => format_phone(gb('phone')), 'Email' => gb('email')]"
        lead="Ring the mill you deal with, or send a message to head office in Longford. For prices, the trade quote form reaches the right sales team directly." />

    <section class="wrap mt-12 md:mt-16" aria-labelledby="sites-title">
        <h2 id="sites-title" class="sr-only">Sites</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($plants as $key => $p)
                <div class="border-2 border-ink bg-surface p-6">
                    <p class="kicker text-ink-3">{{ $p['region'] === 'uk' ? 'Scotland' : 'Ireland' }}</p>
                    <h3 class="mt-1 text-xl font-bold"><a href="{{ route('plants.show', $key) }}" class="hover:text-link">{{ $p['name'] }}</a></h3>
                    <address class="mt-2 not-italic text-ink-2">{{ $p['company'] }}<br>{{ implode(', ', array_slice($p['address'], 0, 3)) }}</address>
                    <p class="mt-3"><a href="{{ tel_href($p['phone']) }}" class="font-display inline-flex min-h-11 items-center gap-2 text-lg font-bold text-link"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($p['phone']) }}</a></p>
                    <p><a href="mailto:{{ $p['email'] }}" class="inline-flex min-h-11 items-center break-all text-link">{{ $p['email'] }}</a></p>
                </div>
            @endforeach
            <div class="border-2 border-ink bg-surface p-6">
                <p class="kicker text-ink-3">Timber frame, Ireland</p>
                <h3 class="mt-1 text-xl font-bold"><a href="{{ route('frame.index') }}" class="hover:text-link">{{ $dte['name'] }}</a></h3>
                <address class="mt-2 not-italic text-ink-2">{{ implode(', ', array_slice($dte['address'], 0, 3)) }}</address>
                <p class="mt-3"><a href="mailto:{{ $dte['email'] }}" class="inline-flex min-h-11 items-center text-link">{{ $dte['email'] }}</a></p>
            </div>
            <div class="border-2 border-ink bg-surface p-6">
                <p class="kicker text-ink-3">Timber frame, UK</p>
                <h3 class="mt-1 text-xl font-bold">{{ $atd['name'] }}</h3>
                <address class="mt-2 not-italic text-ink-2">{{ $atd['address'] }}</address>
                <p class="mt-3"><a href="{{ tel_href($atd['phone']) }}" class="font-display inline-flex min-h-11 items-center gap-2 text-lg font-bold text-link"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($atd['phone']) }}</a></p>
            </div>
        </div>
    </section>

    <section class="wrap mt-16 grid gap-12 lg:grid-cols-[20rem_minmax(0,1fr)]">
        <aside class="space-y-6">
            <div class="bg-slate-900 p-6 text-white on-dark">
                <p class="font-semibold">Need a price?</p>
                <p class="mt-1 text-white/80">The quote form sends your list to the Ireland or UK sales team.</p>
                <a href="{{ route('quotes.create') }}" class="btn btn-light mt-4 w-full">Request a trade quote</a>
            </div>
            <div class="border-2 border-ink bg-surface p-6">
                <p class="font-semibold">Hauliers</p>
                <p class="mt-1 text-ink-2">Book a loading or tipping slot online.</p>
                <a href="{{ route('bookings.create') }}" class="btn btn-outline mt-4 w-full">Book a slot</a>
            </div>
        </aside>

        <form method="POST" action="{{ route('contact.store') }}" class="min-w-0 space-y-8" x-data="sendForm" @submit="submit">
            @csrf
            <x-form-errors />
            <x-honeypot />
            <x-fieldset n="1" title="Send a message to head office">
                <x-radios name="topic" label="What is it about?" :options="enum_options(\App\Enums\EnquiryTopic::class)" :required="true" cols="sm:grid-cols-2" />
                <x-select name="plant" label="Which site?" :optional="true" :options="enum_options(\App\Enums\Plant::class)" placeholder="No particular site" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="name" label="Your name" :required="true" maxlength="120" autocomplete="name" />
                    <x-field name="organisation" label="Company" :optional="true" maxlength="160" autocomplete="organization" />
                    <x-field name="email" label="Email" type="email" :required="true" maxlength="255" autocomplete="email" />
                    <x-field name="phone" label="Phone" type="tel" :optional="true" maxlength="30" autocomplete="tel" inputmode="tel" />
                </div>
                <x-field name="message" label="Message" type="textarea" :required="true" maxlength="3000" rows="6" />
            </x-fieldset>
            <div class="border-t-2 border-ink pt-6">
                <x-submit label="Send message" />
                <p class="hint mt-3">We keep these details only to reply. <a class="text-link" href="{{ route('privacy') }}">Privacy notice</a>.</p>
            </div>
        </form>
    </section>
</x-layouts.site>
