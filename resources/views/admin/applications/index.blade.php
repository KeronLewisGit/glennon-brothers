<x-layouts.admin title="Applications">
    <x-slot:actions>
        <a href="{{ route('admin.applications.export', request()->query()) }}" class="a-btn a-btn-ghost"><x-icon name="download" class="h-4 w-4" />Export CSV</a>
        <a href="{{ route('admin.jobs.index') }}" class="a-btn a-btn-ghost"><x-icon name="briefcase" class="h-4 w-4" />Job postings</a>
    </x-slot:actions>
    @include('admin.partials.status-tabs', ['cases' => \App\Enums\ApplicationStatus::cases(), 'counts' => $counts, 'current' => $filters['status'], 'route' => 'admin.applications.index', 'extra' => ['category' => $filters['category'], 'plant' => $filters['plant'], 'reviewer' => $filters['reviewer'], 'job' => $filters['job'], 'q' => $filters['q']]])
    <form method="GET" class="mt-3 flex flex-wrap items-end gap-3">
        <input type="hidden" name="status" value="{{ $filters['status'] }}">
        <div><label for="f-plant" class="mb-1 block text-sm text-ink-2">Plant</label>
            <select id="f-plant" name="plant" class="a-input"><option value="">All</option>@foreach (\App\Enums\Plant::cases() as $p)<option value="{{ $p->value }}" @selected($filters['plant'] === $p->value)>{{ $p->label() }}</option>@endforeach<option value="any" @selected($filters['plant'] === 'any')>Any plant (open)</option></select></div>
        <div><label for="f-rev" class="mb-1 block text-sm text-ink-2">Reviewer</label>
            <select id="f-rev" name="reviewer" class="a-input"><option value="">Anyone</option>@foreach ($reviewers as $r)<option value="{{ $r->id }}" @selected($filters['reviewer'] === (string) $r->id)>{{ $r->name }}</option>@endforeach</select></div>
        <div><label for="f-cat" class="mb-1 block text-sm text-ink-2">Role family</label>
            <select id="f-cat" name="category" class="a-input"><option value="">All</option>@foreach (\App\Enums\JobCategory::cases() as $c)<option value="{{ $c->value }}" @selected($filters['category'] === $c->value)>{{ $c->label() }}</option>@endforeach</select></div>
        <div><label for="f-job" class="mb-1 block text-sm text-ink-2">Posting</label>
            <select id="f-job" name="job" class="a-input max-w-[16rem]"><option value="">All</option>@foreach ($jobs as $j)<option value="{{ $j->id }}" @selected($filters['job'] === (string) $j->id)>{{ $j->title }}</option>@endforeach</select></div>
        <label class="flex min-h-11 items-center gap-2"><input type="checkbox" name="waiting" value="1" @checked($filters['waiting']) class="h-5 w-5 accent-[var(--primary)]">Waiting over {{ \App\Models\JobApplication::REVIEW_DAYS }} days</label>
        <div class="min-w-0 flex-1 basis-full sm:basis-56"><label for="f-q" class="mb-1 block text-sm text-ink-2">Search</label><input id="f-q" name="q" value="{{ $filters['q'] }}" class="a-input w-full" placeholder="Name, phone or reference"></div>
        <button class="a-btn a-btn-primary">Filter</button>
    </form>
    <div class="a-card mt-5 min-w-0">
        <div class="table-box">
            <table class="data-table">
                <thead><tr><th>Applicant</th><th>Position</th><th>Plant</th><th>Experience</th><th>Stage</th><th>Reviewer</th><th>CV</th><th class="r">Received</th></tr></thead>
                <tbody>
                    @forelse ($applications as $a)
                        <tr>
                            <td><a href="{{ route('admin.applications.show', $a) }}" class="font-semibold text-link underline-offset-2 hover:underline">{{ $a->full_name }}</a><p class="num text-xs text-ink-3">{{ $a->reference }}</p></td>
                            <td class="min-w-[12rem]">{{ $a->position() }}</td>
                            <td class="whitespace-nowrap">{{ $a->plantLabel() }}</td>
                            <td class="whitespace-nowrap">{{ $a->experience->label() }}</td>
                            <td><x-status :status="$a->status" /></td>
                            <td class="whitespace-nowrap">{{ $a->reviewer->name ?? '—' }}</td>
                            <td>@if($a->cv_path)<x-icon name="file" class="h-4 w-4" /><span class="sr-only">CV attached</span>@else<span class="text-ink-3">—</span>@endif</td>
                            <td class="r whitespace-nowrap">@if($a->isWaiting())<x-chip tone="attention" icon="clock">{{ $a->created_at->diffForHumans(null, true, true) }}</x-chip>@else{{ $a->created_at->format('j M') }}@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-10 text-center text-ink-3">No applications match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $applications->links() }}</div>
</x-layouts.admin>
