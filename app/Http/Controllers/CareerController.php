<?php

namespace App\Http\Controllers;

use App\Enums\JobCategory;
use App\Enums\Plant;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Support\Intake;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(Request $request): View
    {
        $plant = Plant::tryFrom(input_string($request->query('plant')));
        $category = JobCategory::tryFrom(input_string($request->query('family')));
        $open = JobPosting::open();

        return view('pages.careers.index', [
            'jobs' => (clone $open)->when($plant, fn ($q, $p) => $q->where('plant', $p))->when($category, fn ($q, $c) => $q->where('category', $c))
                ->orderBy('plant')->orderBy('title')->get(),
            'plantCounts' => (clone $open)->selectRaw('plant, count(*) as total')->groupBy('plant')->pluck('total', 'plant'),
            'plant' => $plant,
            'category' => $category,
        ]);
    }

    public function show(JobPosting $job): View
    {
        abort_unless($job->isOpen(), 404);

        return view('pages.careers.show', ['job' => $job]);
    }

    public function create(Request $request): View
    {
        $jobs = JobPosting::open()->orderBy('title')->get();

        return view('pages.careers.apply', ['jobs' => $jobs, 'selected' => $jobs->firstWhere('slug', input_string($request->query('job')))]);
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        if (Intake::isBot($request, JobApplication::fakeReference(), 'Job application')) {
            $request->releaseHeldUploads();

            return to_route('thanks');
        }

        $data = $request->safe()->except(['cv', 'consent']);
        if ($file = $request->file('cv')) {
            // Random name on the private disk; the original name is shown to HR only.
            $data += [
                'cv_path' => $file->store('cvs', 'local'),
                'cv_name' => mb_substr($file->getClientOriginalName(), 0, 200),
                'cv_mime' => $file->getMimeType(),
                'cv_size' => (int) $file->getSize(),
            ];
        }
        $application = JobApplication::create($data + ['consented_at' => now()]);

        $request->releaseHeldUploads();
        Intake::complete($application->refresh()->load('jobPosting'), $request);

        return to_route('thanks');
    }
}
