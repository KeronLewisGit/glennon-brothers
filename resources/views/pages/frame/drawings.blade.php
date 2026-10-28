<x-layouts.site title="Send more drawings" :mobile-bar="false">
    <x-page-header title="Send more drawings" kicker="DTE · {{ $enquiry->reference }}" code="DTE" photo="trusses"
        :ticket="['Reference' => $enquiry->reference, 'Project' => $enquiry->project_type->label(), 'Status' => $enquiry->status->label()]" ticket-title="DTE Manufacturing"
        :lead="'Add revised or extra drawings to enquiry '.$enquiry->reference.'. They go straight to the DTE team. This link is private: please don\'t share it.'" />

    <section class="wrap mt-12 grid max-w-5xl gap-10 md:mt-16 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <div>
            @if (session('status'))
                <p class="mb-6 flex items-start gap-2 border-l-4 border-[color:var(--good)] bg-surface p-4" role="status"><x-icon name="check-circle" class="mt-0.5 h-5 w-5 flex-none text-[color:var(--good)]" />{{ session('status') }}</p>
            @endif
            @if ($enquiry->status->acceptsDrawings())
                <form method="POST" action="{{ request()->fullUrl() }}" enctype="multipart/form-data" class="space-y-6" x-data="sendForm" @submit="submit">
                    @csrf
                    <x-form-errors />
                    <div>
                        <label for="f-drawings" class="label">Drawings</label>
                        <input id="f-drawings" type="file" name="drawings[]" multiple required class="input" accept="{{ '.'.implode(',.', \App\Models\FrameEnquiry::DRAWING_TYPES) }}" aria-describedby="f-drawings-hint">
                        <span id="f-drawings-hint" class="hint">Up to {{ \App\Models\FrameEnquiry::MAX_DRAWINGS }} files at a time, 20 MB each: {{ implode(', ', array_map('strtoupper', \App\Models\FrameEnquiry::DRAWING_TYPES)) }}.</span>
                        <x-error for="drawings" />
                        @foreach ($errors->getMessages() as $k => $msgs)
                            @if (str_starts_with($k, 'drawings.'))<p class="error"><x-icon name="alert" class="mt-0.5 h-4 w-4 flex-none" />{{ $msgs[0] }}</p>@break @endif
                        @endforeach
                    </div>
                    <x-submit label="Send drawings" />
                </form>
            @else
                <p class="box p-6">This enquiry is {{ strtolower($enquiry->status->label()) }}, so the link no longer takes drawings. Please email <a class="text-link" href="mailto:{{ gb('dte.email') }}">{{ gb('dte.email') }}</a>.</p>
            @endif
        </div>
        <aside>
            <h2 class="kicker text-ink-3">Already received</h2>
            <ul class="mt-3 divide-y divide-line border-y border-line">
                @forelse ($enquiry->drawings as $d)
                    <li class="flex items-center gap-2 py-3"><x-icon name="file" class="h-4 w-4 flex-none text-ink-3" /><span class="min-w-0 break-words">{{ $d->original_name }}</span><span class="ml-auto text-sm text-ink-3">{{ file_size($d->size) }}</span></li>
                @empty
                    <li class="py-3 text-ink-2">No drawings yet.</li>
                @endforelse
            </ul>
        </aside>
    </section>
</x-layouts.site>
