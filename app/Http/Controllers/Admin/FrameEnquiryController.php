<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Area;
use App\Enums\FrameProjectType;
use App\Enums\FrameStatus;
use App\Http\Controllers\Controller;
use App\Models\FrameDrawing;
use App\Models\FrameEnquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** DTE timber frame enquiries: drawings, design review, quote, contract. */
class FrameEnquiryController extends Controller
{
    use Concerns;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', FrameEnquiry::class);
        $filters = $this->filters($request);
        $counts = FrameEnquiry::query()->filter(array_merge($filters, ['status' => null]))
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.frame.index', [
            'enquiries' => FrameEnquiry::with('assignee')->withCount('drawings')->filter($filters)
                ->orderByRaw("case when status = 'new' then 0 when status = 'design_review' then 1 else 2 end")
                ->orderBy('start_month')->orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $filters,
            'counts' => $counts,
        ]);
    }

    public function show(FrameEnquiry $enquiry): View
    {
        Gate::authorize('view', $enquiry);

        return view('admin.frame.show', [
            'enquiry' => $enquiry->load(['assignee', 'drawings', 'notes.user']),
            'staff' => User::working(Area::Frame)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, FrameEnquiry $enquiry): RedirectResponse
    {
        Gate::authorize('update', $enquiry);
        $user = $this->user($request);
        $data = $request->validate([
            'status' => ['sometimes', Rule::enum(FrameStatus::class)],
            'assigned_to' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('active', true)],
            'quoted_total' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);
        $assignTo = isset($data['assigned_to']) ? (int) $data['assigned_to'] : null;
        $reassign = array_key_exists('assigned_to', $data) && $enquiry->assigned_to !== $assignTo;
        $assignee = $reassign && $assignTo ? User::find($assignTo) : null;
        if ($assignee && ! $assignee->works(Area::Frame)) {
            return back()->withErrors(['assigned_to' => "{$assignee->name} works in {$assignee->role->label()} and can't see timber frame enquiries."]);
        }

        if (array_key_exists('quoted_total', $data) && (string) $data['quoted_total'] !== (string) $enquiry->quoted_total) {
            $enquiry->forceFill(['quoted_total' => $data['quoted_total'] === null ? null : (string) $data['quoted_total']])->save();
            $enquiry->log('Quoted total set to '.money($enquiry->quoted_total, 'EUR').'.', 'note', $user);
        }
        if (isset($data['status']) && $enquiry->claimStatus($status = FrameStatus::from($data['status']))) {
            $enquiry->log('Status changed to '.$status->label().'.', 'status', $user);
        }
        if ($reassign) {
            $enquiry->forceFill(['assigned_to' => $assignee?->id])->save();
            $enquiry->log($assignee ? "Assigned to {$assignee->name}." : 'Unassigned.', 'assign', $user);
        }

        return back()->with('status', "{$enquiry->reference} saved.");
    }

    public function note(Request $request, FrameEnquiry $enquiry): RedirectResponse
    {
        Gate::authorize('update', $enquiry);
        $data = $request->validate(['body' => ['required', 'string', 'max:3000']]);
        $enquiry->log($data['body'], 'note', $this->user($request));

        return back()->with('status', 'Note added.')->withFragment('timeline');
    }

    public function drawing(Request $request, FrameEnquiry $enquiry, FrameDrawing $drawing): StreamedResponse
    {
        Gate::authorize('openDrawing', [$enquiry, $drawing]);
        abort_unless(Storage::disk('local')->exists($drawing->path), 404);
        $enquiry->log("Opened {$drawing->original_name}.", 'file', $this->user($request));
        $name = Str::slug($enquiry->reference.'-'.pathinfo($drawing->original_name, PATHINFO_FILENAME)).'.'.pathinfo($drawing->path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($drawing->path, $name, ['Cache-Control' => 'private, no-store']);
    }

    /** Rotates the access key: every drawings link already emailed stops working. */
    public function revoke(Request $request, FrameEnquiry $enquiry): RedirectResponse
    {
        Gate::authorize('update', $enquiry);
        $enquiry->revokeLink();
        $enquiry->log('Customer drawings link revoked; earlier links no longer work.', 'system', $this->user($request));

        return back()->with('status', 'Link revoked. Copy the new link below if the customer still needs one.');
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('viewAny', FrameEnquiry::class);
        $query = FrameEnquiry::with('assignee')->withCount('drawings')->filter($this->filters($request));

        return $this->csv('dte-enquiries', ['Reference', 'Received', 'Status', 'Client', 'Client type', 'Project', 'Units', 'County', 'Eircode', 'Planning', 'Start', 'Floor area m2', 'Drawings', 'Owner', 'Phone', 'Email'],
            $query->lazyByIdDesc(500)->map(fn (FrameEnquiry $e) => [
                $e->reference, $e->created_at->format('Y-m-d'), $e->status->label(), $e->name.($e->company ? ', '.$e->company : ''), $e->client_type->label(), $e->project_type->label(),
                $e->units, $e->county, $e->eircode, $e->planning_status->label(), $e->start_month, $e->floor_area_m2, $e->drawings_count, $e->assignee->name ?? '', format_phone($e->phone), $e->email,
            ]));
    }

    /** @return array<string, string|null> */
    private function filters(Request $request): array
    {
        return [
            'status' => FrameStatus::tryFrom(input_string($request->query('status')))?->value,
            'type' => FrameProjectType::tryFrom(input_string($request->query('type')))?->value,
            'waiting' => $request->boolean('waiting') ? '1' : null,
            'q' => input_string($request->query('q')) ?: null,
        ];
    }
}
