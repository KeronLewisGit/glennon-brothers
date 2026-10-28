<x-layouts.admin :title="$booking->haulier.' · '.$booking->plant->label()">
    <x-slot:actions>
        <a href="{{ route('admin.bookings.index') }}" class="a-btn a-btn-ghost"><x-icon name="arrow-left" class="h-4 w-4" />Bookings</a>
        <a href="mailto:{{ $booking->email }}?subject={{ rawurlencode('Your slot booking '.$booking->reference) }}" class="a-btn a-btn-ghost"><x-icon name="mail" class="h-4 w-4" />Email</a>
        <a href="{{ tel_href($booking->phone) }}" class="a-btn a-btn-primary"><x-icon name="phone" class="h-4 w-4" />Call</a>
    </x-slot:actions>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <section class="a-card min-w-0 p-5 md:p-6">
            <x-status :status="$booking->status" />
            <h2 class="mt-3 text-xl font-bold">{{ $booking->slotLabel() }}</h2>
            <dl class="dl-grid mt-5">
                <dt>Reference</dt><dd class="num">{{ $booking->reference }}</dd>
                <dt>Plant</dt><dd>{{ $booking->plant->label() }}</dd>
                <dt>Purpose</dt><dd>{{ $booking->purpose->label() }}</dd>
                <dt>Haulier</dt><dd>{{ $booking->haulier }}</dd>
                <dt>Driver</dt><dd>{{ $booking->driver_name ?: '—' }}</dd>
                <dt>Vehicle</dt><dd class="num">{{ $booking->vehicle_reg }} · {{ $booking->vehicle_type->label() }}</dd>
                <dt>Order reference</dt><dd>{{ $booking->order_reference ?: '—' }}</dd>
                <dt>Phone</dt><dd><a class="text-link underline" href="{{ tel_href($booking->phone) }}">{{ format_phone($booking->phone) }}</a></dd>
                <dt>Email</dt><dd>{{ $booking->email }}</dd>
                <dt>Received</dt><dd>{{ $booking->created_at->format('j M Y, H:i') }}</dd>
            </dl>
            @if ($booking->remarks)<h3 class="mt-6 font-semibold">Notes for the yard</h3><p class="measure mt-1 whitespace-pre-line text-ink-2">{{ $booking->remarks }}</p>@endif
        </section>
        <div class="min-w-0 space-y-6">
            <section class="a-card p-5">
                <h2 class="font-semibold">Answer</h2>
                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="mt-3 flex flex-wrap gap-2">
                    @csrf @method('PATCH')
                    <label for="b-status" class="sr-only">Status</label>
                    <select id="b-status" name="status" class="a-input min-w-0 flex-1">@foreach (\App\Enums\BookingStatus::cases() as $s)<option value="{{ $s->value }}" @selected($booking->status === $s)>{{ $s->label() }}</option>@endforeach</select>
                    <button class="a-btn a-btn-primary">Save</button>
                </form>
                <p class="mt-2 text-xs text-ink-3">Then email or ring the haulier to confirm, or to offer another slot.</p>
            </section>
            @include('admin.partials.timeline', ['notes' => $booking->notes, 'action' => route('admin.bookings.note', $booking)])
        </div>
    </div>
</x-layouts.admin>
