@php
    $types = implode(', ', array_map('strtoupper', \App\Models\FrameEnquiry::DRAWING_TYPES));
@endphp
<x-layouts.site title="Timber frame enquiry" description="Send DTE your drawings, site location and timeline for a timber frame home, scheme or roof trusses.">
    <x-page-header title="Timber frame enquiry" kicker="DTE Manufacturing" code="DTE" photo="frame-house"
        :ticket="['Send' => 'Drawings, site, timeline', 'To' => 'DTE design office', 'Reply' => 'By email or phone']" ticket-title="DTE Manufacturing"
        lead="Tell DTE about the build and attach whatever drawings you have. You'll get a reference number and a private link to send revised drawings later." />

    <section class="wrap mt-12 max-w-3xl md:mt-16">
        <form method="POST" action="{{ route('frame.store') }}" enctype="multipart/form-data" class="space-y-10" x-data="{ submitting: false, project: @js(old('project_type', '')), submit(e) { if (e.target.checkValidity()) this.submitting = true } }" @submit="submit">
            @csrf
            <x-form-errors />
            <x-honeypot />
            <x-fieldset n="1" title="The project">
                <x-radios name="client_type" label="Who are you?" :required="true" :options="enum_options(\App\Enums\FrameClientType::class)" />
                <div>
                    <x-select name="project_type" label="Type of project" :required="true" :options="enum_options(\App\Enums\FrameProjectType::class)" x-model="project" />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="floor_area_m2" label="Approximate floor area (m²)" type="number" :optional="true" min="10" max="20000" inputmode="numeric" />
                    <div x-show="project === 'scheme'" x-cloak>
                        <x-field name="units" label="Number of homes" type="number" min="2" max="500" inputmode="numeric" hint="For housing schemes." />
                    </div>
                </div>
            </x-fieldset>

            <x-fieldset n="2" title="Site and timeline">
                <x-field name="site_address" label="Site address" type="textarea" :required="true" maxlength="300" rows="3" hint="Townland and nearest town are enough if there is no postal address yet." />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-select name="county" label="County" :required="true" :options="collect((array) gb('counties'))->map(fn ($c) => array_combine($c, $c))->all()" />
                    <x-field name="eircode" label="Eircode" :optional="true" maxlength="8" autocomplete="postal-code" hint="For example N39 AX73." class="uppercase" />
                </div>
                <x-radios name="planning_status" label="Planning permission" :required="true" :options="enum_options(\App\Enums\PlanningStatus::class)" />
                <x-field name="start_month" label="When do you hope to start on site?" type="month" :required="true" :min="now()->format('Y-m')" :max="now()->addYears(3)->format('Y-m')" hint="Month and year. A best guess is fine." />
            </x-fieldset>

            <x-fieldset n="3" title="Drawings">
                <div>
                    <label for="f-drawings" class="label">Drawings <span class="opt">(optional)</span></label>
                    <input id="f-drawings" type="file" name="drawings[]" multiple class="input" accept="{{ '.'.implode(',.', \App\Models\FrameEnquiry::DRAWING_TYPES) }}" aria-describedby="f-drawings-hint" @error('drawings') aria-invalid="true" @enderror>
                    <span id="f-drawings-hint" class="hint">Up to {{ \App\Models\FrameEnquiry::MAX_DRAWINGS }} files, 20 MB each: {{ $types }}. No drawings yet? Send the enquiry anyway and use the link in your email later.</span>
                    <x-held-upload form="frame" field="drawings" />
                    <x-error for="drawings" />
                    @foreach ($errors->getMessages() as $k => $msgs)
                        @if (str_starts_with($k, 'drawings.'))<p class="error"><x-icon name="alert" class="mt-0.5 h-4 w-4 flex-none" />{{ $msgs[0] }}</p>@break @endif
                    @endforeach
                </div>
                <x-field name="message" label="Anything else DTE should know" type="textarea" :optional="true" maxlength="3000" hint="Ground conditions, finishes, energy targets, access to the site." />
            </x-fieldset>

            <x-fieldset n="4" title="Your details">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="name" label="Your name" :required="true" maxlength="120" autocomplete="name" />
                    <x-field name="company" label="Company" :optional="true" maxlength="160" autocomplete="organization" />
                    <x-field name="email" label="Email" type="email" :required="true" maxlength="255" autocomplete="email" hint="Your private drawings link is sent here." />
                    <x-field name="phone" label="Phone" type="tel" :required="true" maxlength="30" autocomplete="tel" inputmode="tel" />
                </div>
                <p class="hint">We use these details only to answer this enquiry. See the <a class="text-link" href="{{ route('privacy') }}">privacy notice</a>.</p>
            </x-fieldset>
            <div class="border-t-2 border-ink pt-6"><x-submit label="Send to DTE" /></div>
        </form>
    </section>
</x-layouts.site>
