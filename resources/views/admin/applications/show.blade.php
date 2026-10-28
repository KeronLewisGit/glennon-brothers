<x-layouts.admin :title="$application->full_name">
    <x-slot:actions>
        <a href="{{ route('admin.applications.index') }}" class="a-btn a-btn-ghost"><x-icon name="arrow-left" class="h-4 w-4" />Applications</a>
        @if ($application->email)<a href="mailto:{{ $application->email }}" class="a-btn a-btn-ghost"><x-icon name="mail" class="h-4 w-4" />Email</a>@endif
        <a href="{{ tel_href($application->phone) }}" class="a-btn a-btn-primary"><x-icon name="phone" class="h-4 w-4" />Call</a>
    </x-slot:actions>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <section class="a-card min-w-0 p-5 md:p-6">
            <x-status :status="$application->status" />
            <h2 class="mt-3 text-xl font-bold">{{ $application->position() }}</h2>
            <dl class="dl-grid mt-5">
                <dt>Reference</dt><dd class="num">{{ $application->reference }}</dd>
                <dt>Plant</dt><dd>{{ $application->plantLabel() }}</dd>
                <dt>Role family</dt><dd>{{ $application->category->label() }}</dd>
                <dt>Experience</dt><dd>{{ $application->experience->label() }}</dd>
                <dt>Phone</dt><dd><a class="text-link underline" href="{{ tel_href($application->phone) }}">{{ format_phone($application->phone) }}</a></dd>
                <dt>Email</dt><dd>{{ $application->email ?? '—' }}</dd>
                <dt>Lives in</dt><dd>{{ $application->home_area ?? '—' }}</dd>
                <dt>Right to work</dt><dd>{{ $application->right_to_work ? 'Yes' : 'Not yet / needs sponsorship' }}</dd>
                @if ($application->licences)<dt>Licences</dt><dd>{{ $application->licences }}</dd>@endif
                <dt>Reviewer</dt><dd>{{ $application->reviewer->name ?? 'Not assigned' }}</dd>
                <dt>Available from</dt><dd>{{ $application->available_from?->format('j F Y') ?? 'Not given' }}</dd>
                <dt>Received</dt><dd>{{ $application->created_at->format('j M Y, g:i a') }}</dd>
            </dl>
            @if ($application->qualifications)<h3 class="mt-6 font-semibold">Tickets, trades and qualifications</h3><p class="measure mt-1 whitespace-pre-line text-ink-2">{{ $application->qualifications }}</p>@endif
            @if ($application->message)<h3 class="mt-5 font-semibold">Message</h3><p class="measure mt-1 whitespace-pre-line text-ink-2">{{ $application->message }}</p>@endif
            <div class="mt-6 border-t border-line pt-5">
                @if ($application->cv_path)
                    <a href="{{ route('admin.applications.cv', $application) }}" class="a-btn a-btn-ghost"><x-icon name="download" class="h-4 w-4" />Download CV ({{ file_size($application->cv_size) }})</a>
                    <p class="mt-2 text-xs text-ink-3"><x-icon name="lock" class="inline h-3.5 w-3.5" /> HR and administrators only; each download is logged.</p>
                @else
                    <p class="text-ink-3">No CV attached.</p>
                @endif
            </div>
        </section>
        <div class="min-w-0 space-y-6">
            <section class="a-card p-5">
                <h2 class="font-semibold">Stage</h2>
                <ol class="stages mt-3" aria-label="Review stages">
                    @foreach (\App\Enums\ApplicationStatus::stages() as $stage)
                        <li @class(['is-done' => $application->status->isOpen() && array_search($stage, \App\Enums\ApplicationStatus::stages(), true) < array_search($application->status, \App\Enums\ApplicationStatus::stages(), true), 'is-current' => $application->status === $stage])>{{ $stage->label() }}</li>
                    @endforeach
                </ol>
                <form method="POST" action="{{ route('admin.applications.update', $application) }}" class="mt-4 space-y-3">
                    @csrf @method('PATCH')
                    <div><label for="a-status" class="mb-1 block text-sm text-ink-2">Move to</label>
                    <select id="a-status" name="status" class="a-input w-full">@foreach (\App\Enums\ApplicationStatus::cases() as $s)<option value="{{ $s->value }}" @selected($application->status === $s)>{{ $s->label() }}</option>@endforeach</select></div>
                    <div><label for="a-rev" class="mb-1 block text-sm text-ink-2">Reviewer</label>
                    <select id="a-rev" name="reviewer_id" class="a-input w-full"><option value="">Not assigned</option>@foreach ($reviewers as $r)<option value="{{ $r->id }}" @selected($application->reviewer_id === $r->id)>{{ $r->name }}</option>@endforeach</select></div>
                    <button class="a-btn a-btn-primary">Save</button>
                </form>
            </section>
            @include('admin.partials.timeline', ['notes' => $application->notes, 'action' => route('admin.applications.note', $application)])
        </div>
    </div>
</x-layouts.admin>
