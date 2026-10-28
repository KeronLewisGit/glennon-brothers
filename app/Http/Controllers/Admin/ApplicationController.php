<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\Area;
use App\Enums\JobCategory;
use App\Enums\Plant;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    use Concerns;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', JobApplication::class);
        $filters = $this->filters($request);
        $counts = JobApplication::query()->filter(array_merge($filters, ['status' => null]))
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.applications.index', [
            'applications' => JobApplication::with(['jobPosting', 'reviewer'])->filter($filters)
                ->orderByRaw("case when status = 'new' then 0 when status in ('screening', 'manager_review', 'interview', 'offer') then 1 else 2 end")->orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $filters,
            'counts' => $counts,
            'jobs' => JobPosting::orderBy('title')->get(['id', 'title', 'plant']),
            'reviewers' => User::working(Area::Careers)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(JobApplication $application): View
    {
        Gate::authorize('view', $application);

        return view('admin.applications.show', [
            'application' => $application->load(['jobPosting', 'reviewer', 'notes.user']),
            'reviewers' => User::working(Area::Careers)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** Moves the application to its next reviewer stage, and/or hands it to a reviewer. */
    public function update(Request $request, JobApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);
        $user = $this->user($request);
        $data = $request->validate([
            'status' => ['sometimes', Rule::enum(ApplicationStatus::class)],
            'reviewer_id' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('active', true)],
        ]);
        $reviewerId = isset($data['reviewer_id']) ? (int) $data['reviewer_id'] : null;
        $reassign = array_key_exists('reviewer_id', $data) && $application->reviewer_id !== $reviewerId;
        $reviewer = $reassign && $reviewerId ? User::find($reviewerId) : null;
        if ($reviewer && ! $reviewer->works(Area::Careers)) {
            return back()->withErrors(['reviewer_id' => "{$reviewer->name} works in {$reviewer->role->label()} and can't see applications."]);
        }

        if (isset($data['status']) && $application->claimStatus($status = ApplicationStatus::from($data['status']))) {
            $application->log('Moved to '.$status->label().'.', 'status', $user);
        }
        if ($reassign) {
            $application->forceFill(['reviewer_id' => $reviewer?->id])->save();
            $application->log($reviewer ? "Reviewer: {$reviewer->name}." : 'Reviewer removed.', 'assign', $user);
        }

        return back()->with('status', "{$application->reference} saved.");
    }

    public function note(Request $request, JobApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);
        $data = $request->validate(['body' => ['required', 'string', 'max:3000']]);
        $application->log($data['body'], 'note', $this->user($request));

        return back()->with('status', 'Note added.')->withFragment('timeline');
    }

    public function cv(Request $request, JobApplication $application): StreamedResponse
    {
        Gate::authorize('openCv', $application);
        abort_unless($application->cv_path && Storage::disk('local')->exists($application->cv_path), 404);
        $application->log('Opened the CV.', 'file', $this->user($request));
        $name = Str::slug($application->reference.'-'.$application->full_name.'-cv').'.'.pathinfo($application->cv_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($application->cv_path, $name, ['Cache-Control' => 'private, no-store']);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('viewAny', JobApplication::class);
        $query = JobApplication::with(['jobPosting', 'reviewer'])->filter($this->filters($request));

        return $this->csv('glennon-applications', ['Reference', 'Received', 'Stage', 'Reviewer', 'Name', 'Position', 'Plant', 'Role family', 'Experience', 'Licences', 'Lives in', 'Right to work', 'Phone', 'Email', 'Available from', 'CV'],
            $query->lazyByIdDesc(500)->map(fn (JobApplication $a) => [
                $a->reference, $a->created_at->format('Y-m-d'), $a->status->label(), $a->reviewer->name ?? '', $a->full_name, $a->position(), $a->plantLabel(), $a->category->label(), $a->experience->label(),
                $a->licences, $a->home_area, $a->right_to_work ? 'yes' : 'not confirmed', format_phone($a->phone), $a->email, $a->available_from?->format('Y-m-d'), $a->cv_path ? 'yes' : 'no',
            ]));
    }

    /** @return array<string, string|null> */
    private function filters(Request $request): array
    {
        $job = input_string($request->query('job'));
        $plant = input_string($request->query('plant'));
        $reviewer = input_string($request->query('reviewer'));

        return [
            'status' => ApplicationStatus::tryFrom(input_string($request->query('status')))?->value,
            'category' => JobCategory::tryFrom(input_string($request->query('category')))?->value,
            'job' => ctype_digit($job) ? $job : null,
            'plant' => $plant === 'any' ? 'any' : Plant::tryFrom($plant)?->value,
            'reviewer' => match (true) {
                $reviewer === 'me' => (string) $request->user()?->getAuthIdentifier(),
                ctype_digit($reviewer) => $reviewer,
                default => null,
            },
            'waiting' => $request->boolean('waiting') ? '1' : null,
            'q' => input_string($request->query('q')) ?: null,
        ];
    }
}
