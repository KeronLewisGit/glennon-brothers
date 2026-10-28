<x-layouts.admin title="Haulier bookings">
    @include('admin.partials.status-tabs', ['cases' => \App\Enums\BookingStatus::cases(), 'counts' => $counts, 'current' => $filters['status'], 'route' => 'admin.bookings.index', 'extra' => ['plant' => $filters['plant'], 'q' => $filters['q']]])
    <form method="GET" class="mt-3 flex flex-wrap items-end gap-3">
        <input type="hidden" name="status" value="{{ $filters['status'] }}">
        <div><label for="f-plant" class="mb-1 block text-sm text-ink-2">Plant</label>
            <select id="f-plant" name="plant" class="a-input"><option value="">All plants</option>@foreach (\App\Enums\Plant::mills() as $p)<option value="{{ $p->value }}" @selected($filters['plant'] === $p->value)>{{ $p->label() }}</option>@endforeach</select></div>
        <div class="min-w-0 flex-1 basis-full sm:basis-56"><label for="f-q" class="mb-1 block text-sm text-ink-2">Search</label><input id="f-q" name="q" value="{{ $filters['q'] }}" class="a-input w-full" placeholder="Haulier, registration or reference"></div>
        <button class="a-btn a-btn-primary">Filter</button>
    </form>
    <div class="a-card mt-5 min-w-0">
        <div class="table-box">
            <table class="data-table">
                <thead><tr><th>Slot</th><th>Plant</th><th>Haulier</th><th>Vehicle</th><th>Purpose</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($bookings as $b)
                        <tr>
                            <td class="whitespace-nowrap"><a href="{{ route('admin.bookings.show', $b) }}" class="font-semibold text-link underline-offset-2 hover:underline">{{ $b->slotLabel() }}</a><p class="num text-xs text-ink-3">{{ $b->reference }}</p></td>
                            <td>{{ $b->plant->label() }}</td>
                            <td class="min-w-[10rem]">{{ $b->haulier }}@if($b->driver_name)<p class="text-xs text-ink-3">{{ $b->driver_name }}</p>@endif</td>
                            <td class="num whitespace-nowrap">{{ $b->vehicle_reg }}<p class="text-xs text-ink-3">{{ $b->vehicle_type->label() }}</p></td>
                            <td>{{ $b->purpose->label() }}</td>
                            <td><x-status :status="$b->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-ink-3">No bookings match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $bookings->links() }}</div>
</x-layouts.admin>
