<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\Plant;
use App\Http\Controllers\Controller;
use App\Models\DeliveryBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Haulier slot requests at the four mills. */
class BookingController extends Controller
{
    use Concerns;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', DeliveryBooking::class);
        $filters = [
            'status' => BookingStatus::tryFrom(input_string($request->query('status')))?->value,
            'plant' => Plant::tryFrom(input_string($request->query('plant')))?->value,
            'q' => input_string($request->query('q')) ?: null,
        ];
        $counts = DeliveryBooking::query()->filter(array_merge($filters, ['status' => null]))
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.bookings.index', [
            // Upcoming slots in date order, then past ones newest first.
            'bookings' => DeliveryBooking::filter($filters)
                ->orderByRaw('case when slot_date >= ? then 0 else 1 end', [today()->format('Y-m-d')])
                ->orderByRaw('case when slot_date >= ? then slot_date end asc', [today()->format('Y-m-d')])
                ->orderByDesc('slot_date')->orderBy('slot')->paginate(30)->withQueryString(),
            'filters' => $filters,
            'counts' => $counts,
        ]);
    }

    public function show(DeliveryBooking $booking): View
    {
        Gate::authorize('view', $booking);

        return view('admin.bookings.show', ['booking' => $booking->load('notes.user')]);
    }

    public function update(Request $request, DeliveryBooking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);
        $data = $request->validate(['status' => ['required', Rule::enum(BookingStatus::class)]]);
        if ($booking->claimStatus($status = BookingStatus::from($data['status']))) {
            $booking->log('Status changed to '.$status->label().'.', 'status', $this->user($request));
        }

        return back()->with('status', "{$booking->reference} saved.");
    }

    public function note(Request $request, DeliveryBooking $booking): RedirectResponse
    {
        Gate::authorize('update', $booking);
        $data = $request->validate(['body' => ['required', 'string', 'max:3000']]);
        $booking->log($data['body'], 'note', $this->user($request));

        return back()->with('status', 'Note added.')->withFragment('timeline');
    }
}
