<?php

namespace App\Http\Controllers;

use App\Enums\BookingPurpose;
use App\Enums\BookingSlot;
use App\Enums\Plant;
use App\Enums\VehicleType;
use App\Models\DeliveryBooking;
use App\Support\Intake;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Hauliers ask for a loading or tipping slot at a mill; sales confirm it in the back office. */
class BookingController extends Controller
{
    public function create(Request $request): View
    {
        return view('pages.booking', ['selected' => Plant::tryFrom(input_string($request->query('plant')))]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (Intake::isBot($request, DeliveryBooking::fakeReference(), 'Haulier booking')) {
            return to_route('thanks');
        }

        $data = $request->validate([
            'plant' => ['required', Rule::in(array_map(fn (Plant $p) => $p->value, Plant::mills()))],
            'purpose' => ['required', Rule::enum(BookingPurpose::class)],
            'slot_date' => ['required', 'date_format:Y-m-d', 'after:today', 'before_or_equal:'.today()->addDays(DeliveryBooking::MAX_DAYS_AHEAD)->format('Y-m-d')],
            'slot' => ['required', Rule::enum(BookingSlot::class)],
            'haulier' => ['required', 'string', 'max:160'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\D*\d){7,15}\D*$/'],
            'vehicle_reg' => ['required', 'string', 'max:16', 'regex:/^[A-Za-z0-9 -]{2,16}$/'],
            'vehicle_type' => ['required', Rule::enum(VehicleType::class)],
            'order_reference' => ['nullable', 'string', 'max:60'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'slot_date.after' => 'Slots can be booked from tomorrow. For today, ring the plant.',
            'slot_date.before_or_equal' => 'Slots can be booked up to '.DeliveryBooking::MAX_DAYS_AHEAD.' days ahead.',
            'phone.regex' => 'Enter a phone number with its area code, for example 086 123 4567 or 07700 900123.',
            'vehicle_reg.regex' => 'Enter the registration as it appears on the plate, for example 231-D-12345 or SK24 ABC.',
            'plant.required' => 'Choose the plant you are going to.',
        ], ['slot_date' => 'date', 'vehicle_reg' => 'registration', 'haulier' => 'haulier or company']);

        $booking = DeliveryBooking::create(['email' => strtolower($data['email'])] + $data + ['source_url' => Intake::source($request)]);
        Intake::complete($booking->refresh(), $request);

        return to_route('thanks');
    }
}
