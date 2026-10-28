<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Models\QuoteRequest;
use App\Support\Intake;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Trade and merchant price requests: line items, delivery address, routed to the regional sales team. */
class QuoteController extends Controller
{
    public function create(): View
    {
        return view('pages.quote');
    }

    public function store(StoreQuoteRequest $request): RedirectResponse
    {
        if (Intake::isBot($request, QuoteRequest::fakeReference(), 'Trade quote request')) {
            $request->releaseHeldUploads();

            return to_route('thanks');
        }

        $data = $request->safe()->except(['lines', 'schedule']);
        if ($file = $request->file('schedule')) {
            $data += [
                'schedule_path' => $file->store('quotes', 'local'),
                'schedule_name' => mb_substr($file->getClientOriginalName(), 0, 200),
                'schedule_mime' => $file->getMimeType(),
                'schedule_size' => (int) $file->getSize(),
            ];
        }

        /** @var list<array<string, mixed>> $lines */
        $lines = $request->validated('lines');
        $quote = DB::transaction(function () use ($data, $lines, $request) {
            $quote = QuoteRequest::create($data + ['source_url' => Intake::source($request)]);
            foreach ($lines as $i => $line) {
                $quote->lines()->create(['position' => $i + 1] + $line);
            }

            return $quote;
        });

        $request->releaseHeldUploads();
        $quote->refresh()->routeToRegion();
        Intake::complete($quote->refresh()->load(['lines', 'assignee']), $request);

        return to_route('thanks');
    }
}
