@php
    $plantOptions = enum_options(\App\Enums\Plant::class) + ['any' => 'Any plant'];
@endphp
<x-layouts.site title="Apply" description="Apply to work at Glennon Brothers: Longford, Fermoy, Windymains, Troon or DTE.">
    <x-page-header :title="$selected ? 'Apply: '.$selected->title : 'Apply to work with us'" kicker="Careers" photo="troon-mill"
        :ticket="['Takes' => 'About three minutes', 'CV' => 'Maintenance and office roles', 'Goes to' => 'Group HR']"
        lead="Operatives, drivers and erectors can apply without a CV. Maintenance, electrical and office roles need one." />
    <section class="wrap mt-12 max-w-3xl md:mt-16">
        <form method="POST" action="{{ route('careers.store') }}" enctype="multipart/form-data" class="space-y-10" x-data="{ submitting: false, category: @js(old('category', $selected?->category->value ?? '')), submit(e) { if (e.target.checkValidity()) this.submitting = true } }" @submit="submit">
            @csrf
            <x-form-errors />
            <x-honeypot />
            <x-fieldset n="1" title="The work">
                @if ($jobs->isNotEmpty())
                    <x-select name="job_posting_id" label="Opening" :options="$jobs->mapWithKeys(fn ($j) => [$j->id => $j->title.' · '.$j->plant->label()])->all()" :value="$selected?->id" placeholder="General application (no specific opening)" />
                @endif
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-select name="plant" label="Plant" :options="$plantOptions" :value="$selected?->plant->value" placeholder="Choose a plant" hint="If you chose an opening, its plant is used." />
                    <x-select name="category" label="Role family" :required="true" :value="$selected?->category->value" :options="enum_options(\App\Enums\JobCategory::class)" x-model="category" hint="If you chose an opening, its role family is used." />
                </div>
                <x-select name="experience" label="Experience in this kind of work" :required="true" :options="enum_options(\App\Enums\Experience::class)" />
                <div x-show="category === 'driver'" x-cloak>
                    <x-field name="licences" label="Licences and CPC" maxlength="160" hint="For example C+E, Driver CPC, HIAB / lorry-mounted crane, forklift." />
                </div>
                <x-field name="qualifications" label="Tickets, trades and qualifications" type="textarea" :optional="true" maxlength="2000" hint="For example Safe Pass, CSCS, forklift, qualified electrician, fitter, apprenticeship." />
                <x-field name="available_from" label="Earliest start date" type="date" :optional="true" :min="today()->format('Y-m-d')" />
            </x-fieldset>
            <x-fieldset n="2" title="About you">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="first_name" label="First name" :required="true" maxlength="80" autocomplete="given-name" />
                    <x-field name="last_name" label="Last name" :required="true" maxlength="80" autocomplete="family-name" />
                    <x-field name="phone" label="Phone" type="tel" :required="true" maxlength="30" autocomplete="tel" inputmode="tel" hint="We call or text this number." />
                    <x-field name="email" label="Email" type="email" maxlength="255" autocomplete="email" hint="Needed for maintenance and office roles." />
                </div>
                <x-field name="home_area" label="Town you live in" :optional="true" maxlength="80" autocomplete="address-level2" />
                <x-radios name="right_to_work" label="Do you have the right to work in Ireland or the UK (where the job is)?" :required="true" :options="['1' => 'Yes', '0' => 'Not yet / need sponsorship']" />
            </x-fieldset>
            <x-fieldset n="3" title="CV and message">
                <div>
                    <label for="f-cv" class="label">CV</label>
                    <input id="f-cv" type="file" name="cv" class="input" accept=".pdf,.doc,.docx" aria-describedby="f-cv-hint" @error('cv') aria-invalid="true" @enderror>
                    <span id="f-cv-hint" class="hint">PDF or Word, up to 5 MB. Optional for operatives, drivers and erectors.</span>
                    <x-held-upload form="application" field="cv" />
                    <x-error for="cv" />
                </div>
                <x-field name="message" label="Anything else" type="textarea" :optional="true" maxlength="2000" />
                <label class="choice items-start"><input type="checkbox" name="consent" value="1" class="mt-0.5" @checked(old('consent')) required><span>I agree to Glennon Brothers keeping my application for up to 12 months to consider me for work (<a class="text-link" href="{{ route('privacy') }}#applicants">privacy notice</a>).</span></label>
                <x-error for="consent" />
            </x-fieldset>
            <div class="border-t-2 border-brand pt-6"><x-submit label="Send my application" /></div>
        </form>
    </section>
</x-layouts.site>
