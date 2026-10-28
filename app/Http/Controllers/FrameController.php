<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFrameEnquiryRequest;
use App\Models\FrameEnquiry;
use App\Support\Intake;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

/** DTE timber frame and roof truss enquiries, and the customer's signed link for more drawings. */
class FrameController extends Controller
{
    public function create(): View
    {
        return view('pages.frame.enquire');
    }

    public function store(StoreFrameEnquiryRequest $request): RedirectResponse
    {
        if (Intake::isBot($request, FrameEnquiry::fakeReference(), 'Timber frame enquiry')) {
            $request->releaseHeldUploads();

            return to_route('thanks');
        }

        $enquiry = FrameEnquiry::create($request->safe()->except(['drawings']) + ['source_url' => Intake::source($request)]);
        /** @var list<UploadedFile> $files */
        $files = $request->file('drawings', []);
        $this->saveDrawings($enquiry, $files, 'customer');

        $request->releaseHeldUploads();
        Intake::complete($enquiry->refresh()->load('drawings'), $request);

        return to_route('thanks');
    }

    /** The signed page where the customer adds drawings (linked in their confirmation email). */
    public function drawings(string $enquiry, string $key): View
    {
        return view('pages.frame.drawings', ['enquiry' => $this->fromLink($enquiry, $key)]);
    }

    public function storeDrawings(Request $request, string $enquiry, string $key): RedirectResponse
    {
        $model = $this->fromLink($enquiry, $key);
        abort_unless($model->status->acceptsDrawings(), 410);
        $room = FrameEnquiry::MAX_DRAWINGS * 3 - $model->drawings()->count();
        $request->validate([
            'drawings' => ['required', 'array', 'min:1', 'max:'.max(1, min(FrameEnquiry::MAX_DRAWINGS, $room))],
            'drawings.*' => ['file', 'max:'.FrameEnquiry::MAX_DRAWING_KB, 'mimes:'.implode(',', FrameEnquiry::DRAWING_TYPES)],
        ], [
            'drawings.required' => 'Choose at least one drawing to send.',
            'drawings.max' => 'Send up to '.FrameEnquiry::MAX_DRAWINGS.' drawings at a time.',
            'drawings.*.max' => 'Each drawing must be 20 MB or smaller.',
            'drawings.*.mimes' => 'Drawings can be PDF, DWG, DXF, JPG, PNG or ZIP files.',
        ]);
        abort_if($room <= 0, 422, 'This enquiry already has the most drawings we can take online. Please email them to the DTE team.');

        /** @var list<UploadedFile> $files */
        $files = $request->file('drawings', []);
        $this->saveDrawings($model, $files, 'customer');
        $model->log(count($files).' '.str('drawing')->plural(count($files)).' added by the customer through their link.', 'file');

        return back()->with('status', count($files).' '.str('drawing')->plural(count($files)).' received. Thank you.');
    }

    /** @param list<UploadedFile> $files */
    private function saveDrawings(FrameEnquiry $enquiry, array $files, string $by): void
    {
        foreach ($files as $file) {
            $enquiry->drawings()->create([
                // Random name on the private disk, one folder per enquiry.
                'path' => $file->store('frame/'.$enquiry->id, 'local'),
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 200),
                'mime' => $file->getMimeType(),
                'size' => (int) $file->getSize(),
                'added_by' => $by,
            ]);
        }
    }

    private function fromLink(string $publicId, string $key): FrameEnquiry
    {
        $enquiry = FrameEnquiry::where('public_id', $publicId)->first();
        abort_unless($enquiry && hash_equals($enquiry->access_key, $key), 404);

        return $enquiry;
    }
}
