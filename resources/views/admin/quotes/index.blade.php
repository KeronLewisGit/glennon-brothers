<x-layouts.admin title="Trade quotes">
    <x-slot:actions>
        <a href="{{ route('admin.quotes.export', request()->query()) }}" class="a-btn a-btn-ghost"><x-icon name="download" class="h-4 w-4" />Export CSV</a>
    </x-slot:actions>
    @php($extra = ['region' => $filters['region'] ?? 'all', 'assigned' => $filters['assigned'], 'waiting' => $filters['waiting'], 'q' => $filters['q']])
    <nav class="tag-row" aria-label="Filter by status">
        <a href="{{ route('admin.quotes.index', array_filter($extra)) }}" class="tag" @if(! $filters['status']) aria-current="true" @endif>All <span class="count">{{ $counts->sum() }}</span></a>
        <a href="{{ route('admin.quotes.index', array_filter($extra + ['status' => 'open'])) }}" class="tag" @if($filters['status'] === 'open') aria-current="true" @endif>Open <span class="count">{{ collect(\App\Enums\QuoteStatus::open())->sum(fn ($s) => $counts[$s->value] ?? 0) }}</span></a>
        @foreach (\App\Enums\QuoteStatus::cases() as $s)
            <a href="{{ route('admin.quotes.index', array_filter($extra + ['status' => $s->value])) }}" class="tag" @if($filters['status'] === $s->value) aria-current="true" @endif>{{ $s->label() }} <span class="count">{{ $counts[$s->value] ?? 0 }}</span></a>
        @endforeach
    </nav>
    <form method="GET" class="mt-3 flex flex-wrap items-end gap-3">
        <input type="hidden" name="status" value="{{ $filters['status'] }}">
        <div><label for="f-region" class="mb-1 block text-sm text-ink-2">Sales team</label>
            <select id="f-region" name="region" class="a-input"><option value="all">Both teams</option>@foreach (\App\Enums\Region::cases() as $r)<option value="{{ $r->value }}" @selected($filters['region'] === $r->value)>{{ $r->team() }}</option>@endforeach</select></div>
        <div><label for="f-assigned" class="mb-1 block text-sm text-ink-2">Owner</label>
            <select id="f-assigned" name="assigned" class="a-input"><option value="">Anyone</option><option value="me" @selected($filters['assigned'] === (string) auth()->id())>Me</option><option value="none" @selected($filters['assigned'] === 'none')>Unassigned</option>@foreach ($staff as $u)<option value="{{ $u->id }}" @selected($filters['assigned'] === (string) $u->id && $u->id !== auth()->id())>{{ $u->name }}</option>@endforeach</select></div>
        <label class="flex min-h-11 items-center gap-2"><input type="checkbox" name="waiting" value="1" @checked($filters['waiting']) class="h-5 w-5 accent-[var(--primary)]">Waiting over {{ \App\Models\QuoteRequest::RESPONSE_HOURS }} h <span class="chip num" data-tone="attention"><x-icon name="clock" />{{ $waitingCount }}</span></label>
        <div class="min-w-0 flex-1 basis-full sm:basis-56"><label for="f-q" class="mb-1 block text-sm text-ink-2">Search</label><input id="f-q" name="q" value="{{ $filters['q'] }}" class="a-input w-full" placeholder="Company, contact, email or reference"></div>
        <button class="a-btn a-btn-primary">Filter</button>
    </form>
    <div class="a-card mt-5 min-w-0">
        <div class="table-box">
            <table class="data-table">
                <thead><tr><th>Company</th><th>Delivery</th><th class="r">Lines</th><th>Owner</th><th>Status</th><th class="r">Quoted</th><th class="r">Received</th></tr></thead>
                <tbody>
                    @forelse ($quotes as $q)
                        <tr>
                            <td><a href="{{ route('admin.quotes.show', $q) }}" class="font-semibold text-link underline-offset-2 hover:underline">{{ $q->company }}</a><p class="num text-xs text-ink-3">{{ $q->reference }} · {{ $q->contact_name }}</p></td>
                            <td class="whitespace-nowrap">{{ $q->country->label() }}<p class="text-xs text-ink-3">{{ $q->region->team() }} · {{ $q->currency }}</p></td>
                            <td class="r">{{ $q->lines_count }}</td>
                            <td class="whitespace-nowrap">{{ $q->assignee->name ?? '—' }}</td>
                            <td><x-status :status="$q->status" /></td>
                            <td class="r whitespace-nowrap">{{ $q->quoted_total !== null ? $q->totalLabel() : '—' }}</td>
                            <td class="r whitespace-nowrap">@if($q->isWaiting())<x-chip tone="attention" icon="clock">{{ $q->ageLabel() }}</x-chip>@else{{ $q->created_at->format('j M') }}@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-ink-3">No quote requests match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $quotes->links() }}</div>
</x-layouts.admin>
