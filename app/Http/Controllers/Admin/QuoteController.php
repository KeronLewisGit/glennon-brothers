<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Area;
use App\Enums\QuoteStatus;
use App\Enums\Region;
use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Trade quote requests: routed by region, priced, sent, won or lost. */
class QuoteController extends Controller
{
    use Concerns;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', QuoteRequest::class);
        $filters = $this->filters($request);
        $counts = QuoteRequest::query()->filter(array_merge($filters, ['status' => null]))
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        // Unanswered first, oldest at the top; the rest newest first.
        $quotes = QuoteRequest::with('assignee')->withCount('lines')->filter($filters)
            ->orderByRaw("case when status in ('new', 'pricing') then 0 else 1 end")
            ->orderByRaw("case when status in ('new', 'pricing') then created_at end asc")
            ->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return view('admin.quotes.index', [
            'quotes' => $quotes,
            'filters' => $filters,
            'counts' => $counts,
            'waitingCount' => QuoteRequest::waiting()->when($filters['region'], fn ($q, $r) => $q->where('region', $r))->count(),
            'staff' => User::working(Area::Quotes)->orderBy('name')->get(['id', 'name', 'region']),
        ]);
    }

    public function show(QuoteRequest $quote): View
    {
        Gate::authorize('view', $quote);

        return view('admin.quotes.show', [
            'quote' => $quote->load(['assignee', 'lines', 'notes.user']),
            'staff' => User::working(Area::Quotes)->orderBy('name')->get(['id', 'name', 'region', 'role']),
        ]);
    }

    public function update(Request $request, QuoteRequest $quote): RedirectResponse
    {
        Gate::authorize('update', $quote);
        $user = $this->user($request);
        $data = $request->validate([
            'status' => ['sometimes', Rule::enum(QuoteStatus::class)],
            'assigned_to' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('active', true)],
            'quoted_total' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999'],
        ]);

        // Check the new owner before changing anything, so a refused assignment leaves nothing half-saved.
        $assignTo = isset($data['assigned_to']) ? (int) $data['assigned_to'] : null;
        $reassign = array_key_exists('assigned_to', $data) && $quote->assigned_to !== $assignTo;
        $assignee = $reassign && $assignTo ? User::find($assignTo) : null;
        if ($assignee && ! $assignee->works(Area::Quotes)) {
            return back()->withErrors(['assigned_to' => "{$assignee->name} works in {$assignee->role->label()} and can't see quotes."]);
        }
        if (($data['status'] ?? null) === QuoteStatus::Quoted->value && ($data['quoted_total'] ?? $quote->quoted_total) === null) {
            return back()->withErrors(['quoted_total' => 'Enter the quoted total before marking the quote as sent.']);
        }

        if (array_key_exists('quoted_total', $data) && (string) $data['quoted_total'] !== (string) $quote->quoted_total) {
            $quote->quoted_total = $data['quoted_total'] === null ? null : (string) $data['quoted_total'];
            $quote->save();
            $quote->log('Quoted total set to '.$quote->totalLabel().'.', 'note', $user);
        }
        if (isset($data['status']) && $quote->claimStatus($status = QuoteStatus::from($data['status']))) {
            $quote->log('Status changed to '.$status->label().'.', 'status', $user);
        }
        if ($reassign) {
            $quote->forceFill(['assigned_to' => $assignee?->id])->save();
            $quote->log($assignee ? "Assigned to {$assignee->name}.".($assignee->region && $assignee->region !== $quote->region ? " (Outside {$quote->region->team()}.)" : '') : 'Unassigned.', 'assign', $user);
        }

        return back()->with('status', "{$quote->reference} saved.");
    }

    public function note(Request $request, QuoteRequest $quote): RedirectResponse
    {
        Gate::authorize('update', $quote);
        $data = $request->validate(['body' => ['required', 'string', 'max:3000']]);
        $quote->log($data['body'], 'note', $this->user($request));

        return back()->with('status', 'Note added.')->withFragment('timeline');
    }

    public function schedule(Request $request, QuoteRequest $quote): StreamedResponse
    {
        Gate::authorize('openSchedule', $quote);
        abort_unless($quote->schedule_path && Storage::disk('local')->exists($quote->schedule_path), 404);
        $quote->log("Opened {$quote->schedule_name}.", 'file', $this->user($request));
        $name = Str::slug($quote->reference.'-'.pathinfo((string) $quote->schedule_name, PATHINFO_FILENAME)).'.'.pathinfo($quote->schedule_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($quote->schedule_path, $name, ['Cache-Control' => 'private, no-store']);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('viewAny', QuoteRequest::class);
        $query = QuoteRequest::with(['assignee', 'lines'])->filter($this->filters($request));

        return $this->csv('glennon-quotes', ['Reference', 'Received', 'Status', 'Region', 'Company', 'Type', 'Contact', 'Email', 'Phone', 'Deliver to', 'Country', 'Needed by', 'Lines', 'Owner', 'Currency', 'Quoted total'],
            $query->lazyByIdDesc(500)->map(fn (QuoteRequest $q) => [
                $q->reference, $q->created_at->format('Y-m-d'), $q->status->label(), $q->region->label(), $q->company, $q->customer_type->label(), $q->contact_name,
                $q->email, format_phone($q->phone), $q->delivery_address.($q->postcode ? ', '.$q->postcode : ''), $q->country->label(), $q->required_by?->format('Y-m-d'),
                $q->lines->map->oneLine()->implode(' | '), $q->assignee->name ?? '', $q->currency, $q->quoted_total,
            ]));
    }

    /** @return array<string, string|null> */
    private function filters(Request $request): array
    {
        $status = input_string($request->query('status'));
        $assigned = input_string($request->query('assigned'));
        $region = input_string($request->query('region'));
        // Sales staff land on their own region; "all" shows both teams.
        $user = $request->user();
        if ($region === '' && $user instanceof User && $user->region) {
            $region = $user->region->value;
        }

        return [
            'status' => $status === 'open' ? 'open' : QuoteStatus::tryFrom($status)?->value,
            'region' => Region::tryFrom($region)?->value,
            'assigned' => match (true) {
                $assigned === 'me' => (string) $request->user()?->getAuthIdentifier(),
                $assigned === 'none', ctype_digit($assigned) => $assigned,
                default => null,
            },
            'waiting' => $request->boolean('waiting') ? '1' : null,
            'q' => input_string($request->query('q')) ?: null,
        ];
    }
}
