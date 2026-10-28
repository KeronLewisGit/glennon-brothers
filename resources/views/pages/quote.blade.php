@php
    $max = \App\Models\QuoteRequest::MAX_LINES;
    $old = array_values(array_filter((array) old('lines', []), is_array(...)));
    $visible = max(3, count($old));
    $productOptions = [];
    foreach ((array) gb('quote_products') as $k => $p) {
        $productOptions[$p['group']][$k] = $p['label'];
    }
    $treatments = enum_options(\App\Enums\Treatment::class);
    $units = enum_options(\App\Enums\QuoteUnit::class);
@endphp
<x-layouts.site title="Trade quote" description="Request a trade price from Glennon Brothers for construction timber, CLS, fencing, pallet and packaging timber: product, sizes, treatment, quantity and delivery site.">
    <x-page-header title="Request a trade quote" kicker="Trade and merchants" code="Q" photo="packs"
        :ticket="['Ireland' => 'Priced in euro', 'UK' => 'Priced in sterling', 'Lines' => 'Up to '.$max.', or attach a list']"
        lead="List what you need line by line. The request goes to the Ireland or UK sales team, depending on where the timber is going, and you get a reference number straight away." />

    <section class="wrap mt-12 max-w-4xl md:mt-16">
        <form method="POST" action="{{ route('quotes.store') }}" enctype="multipart/form-data" class="space-y-10" x-data="{ submitting: false, rows: {{ $visible }}, max: {{ $max }}, submit(e) { if (e.target.checkValidity()) this.submitting = true } }" @submit="submit">
            @csrf
            <x-form-errors />
            <x-honeypot />

            <x-fieldset n="1" title="Your business">
                <x-select name="customer_type" label="Type of business" :required="true" :options="enum_options(\App\Enums\CustomerType::class)" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="company" label="Company" :required="true" maxlength="160" autocomplete="organization" />
                    <x-field name="account_number" label="Glennon Brothers account number" :optional="true" maxlength="40" hint="If you already buy from us." />
                    <x-field name="contact_name" label="Your name" :required="true" maxlength="120" autocomplete="name" />
                    <x-field name="phone" label="Phone" type="tel" :required="true" maxlength="30" autocomplete="tel" inputmode="tel" />
                    <x-field name="email" label="Email" type="email" :required="true" maxlength="255" autocomplete="email" class="sm:col-span-2" />
                </div>
            </x-fieldset>

            <x-fieldset n="2" title="What you need" intro="Sizes in millimetres, lengths in metres. Leave spare lines empty.">
                <x-error for="lines" />
                <ol class="space-y-4">
                    @for ($i = 0; $i < $max; $i++)
                        @php($line = $old[$i] ?? [])
                        <li class="quote-line border-2 border-line-strong bg-surface p-4 sm:p-5" @if($i >= $visible) hidden @endif :hidden="rows <= {{ $i }}">
                            <p class="kicker flex items-center gap-2 text-ink-3"><x-tree class="h-3.5 w-6 text-mark" />Line {{ $i + 1 }}</p>
                            <div class="mt-3 grid gap-4 sm:grid-cols-6">
                                <div class="sm:col-span-3"><x-select :name="'lines['.$i.'][product]'" label="Product" :options="$productOptions" :value="$line['product'] ?? null" placeholder="Choose a product" /></div>
                                <div class="sm:col-span-3"><x-field :name="'lines['.$i.'][description]'" label="Grade, profile or detail" :optional="true" maxlength="160" :value="$line['description'] ?? null" placeholder="e.g. C16, pointed, weather edge" /></div>
                                <div class="sm:col-span-2"><x-field :name="'lines['.$i.'][thickness_mm]'" label="Thickness (mm)" type="number" min="5" max="400" inputmode="numeric" :value="$line['thickness_mm'] ?? null" /></div>
                                <div class="sm:col-span-2"><x-field :name="'lines['.$i.'][width_mm]'" label="Width (mm)" type="number" min="10" max="600" inputmode="numeric" :value="$line['width_mm'] ?? null" /></div>
                                <div class="sm:col-span-2"><x-field :name="'lines['.$i.'][length_m]'" label="Length (m)" type="number" min="0.3" max="20" step="0.1" inputmode="decimal" :value="$line['length_m'] ?? null" /></div>
                                <div class="sm:col-span-2"><x-select :name="'lines['.$i.'][treatment]'" label="Treatment" :options="$treatments" :value="$line['treatment'] ?? 'any'" :placeholder="null" /></div>
                                <div class="sm:col-span-2"><x-field :name="'lines['.$i.'][quantity]'" label="Quantity" type="number" min="0.01" step="any" inputmode="decimal" :value="$line['quantity'] ?? null" /></div>
                                <div class="sm:col-span-2"><x-select :name="'lines['.$i.'][unit]'" label="Unit" :options="$units" :value="$line['unit'] ?? 'm3'" :placeholder="null" /></div>
                                <div class="sm:col-span-6"><x-field :name="'lines['.$i.'][delivery_site]'" label="Delivery site for this line" :optional="true" maxlength="200" :value="$line['delivery_site'] ?? null" hint="Only if it differs from the main delivery address." /></div>
                            </div>
                        </li>
                    @endfor
                </ol>
                <div class="flex flex-wrap items-center gap-3" x-show="rows < max" x-cloak>
                    <button type="button" class="btn btn-outline" @click="rows++; $nextTick(() => document.getElementById('f-lines-' + (rows - 1) + '--product-').focus())"><x-icon name="plus" class="h-4 w-4" />Add another line</button>
                    <span class="hint !mt-0" x-text="(max - rows) + ' more available'"></span>
                </div>
                <div>
                    <label for="f-schedule" class="label">Cutting list or schedule <span class="opt">(optional)</span></label>
                    <input id="f-schedule" type="file" name="schedule" class="input" accept="{{ '.'.implode(',.', \App\Models\QuoteRequest::SCHEDULE_TYPES) }}" aria-describedby="f-schedule-hint" @error('schedule') aria-invalid="true" @enderror>
                    <span id="f-schedule-hint" class="hint">PDF, Excel, CSV or Word, up to 10 MB. Useful for long lists. Add at least one line above as well.</span>
                    <x-held-upload form="quote" field="schedule" />
                    <x-error for="schedule" />
                </div>
            </x-fieldset>

            <x-fieldset n="3" title="Delivery">
                <x-radios name="country" label="Where is the timber going?" :required="true" cols="sm:grid-cols-3" :options="enum_options(\App\Enums\Country::class)" />
                <x-field name="delivery_address" label="Delivery address" type="textarea" :required="true" maxlength="300" rows="3" autocomplete="street-address" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="postcode" label="Eircode or postcode" :optional="true" maxlength="12" autocomplete="postal-code" class="uppercase" hint="For example N39 AX73 or KA10 6DN." />
                    <x-field name="required_by" label="Needed by" type="date" :optional="true" :min="today()->format('Y-m-d')" />
                </div>
                <x-field name="remarks" label="Anything else" type="textarea" :optional="true" maxlength="3000" hint="Delivery restrictions, part loads, packaging, certification (FSC®) needed." />
                <p class="hint">We use these details only to answer this request. See the <a class="text-link" href="{{ route('privacy') }}">privacy notice</a>.</p>
            </x-fieldset>
            <div class="border-t-2 border-ink pt-6"><x-submit label="Send for pricing" /></div>
        </form>
    </section>
</x-layouts.site>
