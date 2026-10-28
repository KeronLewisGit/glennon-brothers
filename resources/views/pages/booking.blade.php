<x-layouts.site title="Haulier slot booking" description="Hauliers: ask for a loading or tipping slot at the Glennon Brothers mills in Longford, Fermoy, Windymains or Troon.">
    <x-page-header title="Book a yard slot" kicker="Hauliers" code="H" photo="troon-night"
        :ticket="['Book' => 'From tomorrow', 'Up to' => \App\Models\DeliveryBooking::MAX_DAYS_AHEAD.' days ahead', 'Confirmed by' => 'The plant']"
        lead="Collecting timber, delivering logs or taking residues away? Ask for a slot and the plant will confirm it, or offer another time, by email. For today, ring the plant." />

    <section class="wrap mt-12 max-w-3xl md:mt-16">
        <form method="POST" action="{{ route('bookings.store') }}" class="space-y-10" x-data="sendForm" @submit="submit">
            @csrf
            <x-form-errors />
            <x-honeypot />
            <x-fieldset n="1" title="Where and when">
                <x-radios name="plant" label="Plant" :required="true" :value="$selected?->value" :options="enum_options(\App\Enums\Plant::class, \App\Enums\Plant::mills())" />
                <x-radios name="purpose" label="Purpose" :required="true" :options="enum_options(\App\Enums\BookingPurpose::class)" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="slot_date" label="Date" type="date" :required="true" :min="today()->addDay()->format('Y-m-d')" :max="today()->addDays(\App\Models\DeliveryBooking::MAX_DAYS_AHEAD)->format('Y-m-d')" />
                    <x-select name="slot" label="Arrival window" :required="true" :options="enum_options(\App\Enums\BookingSlot::class)" />
                </div>
                <x-field name="order_reference" label="Order or collection reference" :optional="true" maxlength="60" hint="From your sales order or the log purchase contract." />
            </x-fieldset>
            <x-fieldset n="2" title="Vehicle and driver">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="haulier" label="Haulier or company" :required="true" maxlength="160" autocomplete="organization" />
                    <x-field name="driver_name" label="Driver" :optional="true" maxlength="120" />
                    <x-field name="vehicle_reg" label="Registration" :required="true" maxlength="16" class="uppercase" hint="For example 231-D-12345 or SK24 ABC." />
                    <x-select name="vehicle_type" label="Vehicle" :required="true" :options="enum_options(\App\Enums\VehicleType::class)" />
                    <x-field name="email" label="Email" type="email" :required="true" maxlength="255" autocomplete="email" hint="The confirmation goes here." />
                    <x-field name="phone" label="Phone" type="tel" :required="true" maxlength="30" autocomplete="tel" inputmode="tel" />
                </div>
                <x-field name="remarks" label="Notes for the yard" type="textarea" :optional="true" maxlength="1000" />
            </x-fieldset>
            <div class="border-t-2 border-ink pt-6"><x-submit label="Ask for this slot" /></div>
        </form>
    </section>
</x-layouts.site>
