<x-layouts.admin title="Timber frame enquiries">
    <x-slot:actions>
        <a href="{{ route('admin.frame.export', request()->query()) }}" class="a-btn a-btn-ghost"><x-icon name="download" class="h-4 w-4" />Export CSV</a>
    </x-slot:actions>
    @include('admin.partials.status-tabs', ['cases' => \App\Enums\FrameStatus::cases(), 'counts' => $counts, 'current' => $filters['status'], 'route' => 'admin.frame.index', 'extra' => ['type' => $filters['type'], 'waiting' => $filters['waiting'], 'q' => $filters['q']]])
    <form method="GET" class="mt-3 flex flex-wrap items-end gap-3">
        <input type="hidden" name="status" value="{{ $filters['status'] }}">
        <div><label for="f-type" class="mb-1 block text-sm text-ink-2">Project</label>
            <select id="f-type" name="type" class="a-input"><option value="">All</option>@foreach (\App\Enums\FrameProjectType::cases() as $t)<option value="{{ $t->value }}" @selected($filters['type'] === $t->value)>{{ $t->label() }}</option>@endforeach</select></div>
        <label class="flex min-h-11 items-center gap-2"><input type="checkbox" name="waiting" value="1" @checked($filters['waiting']) class="h-5 w-5 accent-[var(--primary)]">Waiting over {{ \App\Models\FrameEnquiry::REVIEW_DAYS }} days</label>
        <div class="min-w-0 flex-1 basis-full sm:basis-56"><label for="f-q" class="mb-1 block text-sm text-ink-2">Search</label><input id="f-q" name="q" value="{{ $filters['q'] }}" class="a-input w-full" placeholder="Name, site, county or reference"></div>
        <button class="a-btn a-btn-primary">Filter</button>
    </form>
    <div class="a-card mt-5 min-w-0">
        <div class="table-box">
            <table class="data-table">
                <thead><tr><th>Client</th><th>Project</th><th>Site</th><th>Start</th><th class="r">Drawings</th><th>Status</th><th class="r">Received</th></tr></thead>
                <tbody>
                    @forelse ($enquiries as $e)
                        <tr>
                            <td><a href="{{ route('admin.frame.show', $e) }}" class="font-semibold text-link underline-offset-2 hover:underline">{{ $e->name }}</a><p class="num text-xs text-ink-3">{{ $e->reference }} · {{ $e->client_type->label() }}</p></td>
                            <td class="whitespace-nowrap">{{ $e->project_type->label() }}@if($e->units)<p class="text-xs text-ink-3">{{ $e->units }} homes</p>@endif</td>
                            <td class="min-w-[10rem]">Co. {{ $e->county }}<p class="text-xs text-ink-3">{{ $e->planning_status->label() }}</p></td>
                            <td class="whitespace-nowrap">{{ $e->startLabel() }}</td>
                            <td class="r">{{ $e->drawings_count }}</td>
                            <td><x-status :status="$e->status" /></td>
                            <td class="r whitespace-nowrap">@if($e->isWaiting())<x-chip tone="attention" icon="clock">{{ $e->created_at->diffForHumans(null, true, true) }}</x-chip>@else{{ $e->created_at->format('j M') }}@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-ink-3">No enquiries match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $enquiries->links() }}</div>
</x-layouts.admin>
