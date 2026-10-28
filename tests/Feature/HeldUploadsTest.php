<?php

namespace Tests\Feature;

use App\Models\FrameEnquiry;
use App\Models\JobApplication;
use App\Models\QuoteRequest;
use App\Support\HeldUploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** A form that comes back with an error keeps the files that were fine; nobody attaches them twice. */
class HeldUploadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Notification::fake();
    }

    /** A small file with real PDF bytes, so its type is recognised from its content. */
    private function pdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj << >> endobj\ntrailer << >>\n%%EOF\n");
    }

    /** @return array<string, mixed> */
    private function frame(array $overrides = []): array
    {
        return array_merge([
            'client_type' => 'developer', 'project_type' => 'house', 'name' => 'Eoin Nolan', 'email' => 'e@example.com', 'phone' => '087 765 4321',
            'site_address' => 'Lands at Moate Road', 'county' => 'Westmeath', 'planning_status' => 'applied', 'start_month' => now()->addMonths(6)->format('Y-m'),
        ], $overrides);
    }

    public function test_frame_drawings_survive_a_validation_error(): void
    {
        $files = [$this->pdf('plans.pdf'), $this->pdf('sections.pdf')];
        $this->from('/timber-frame/enquire')->post('/timber-frame/enquire', $this->frame(['name' => '', 'drawings' => $files]))->assertSessionHasErrors('name');
        $this->assertCount(2, Storage::disk('local')->allFiles(HeldUploads::DIR));
        $this->get('/timber-frame/enquire')->assertSee('Attached:')->assertSee('plans.pdf, sections.pdf');

        // Sent again with the input left empty: the held files go with it, then are cleared.
        $this->post('/timber-frame/enquire', $this->frame())->assertRedirect('/thanks');
        $e = FrameEnquiry::sole();
        $this->assertSame(['plans.pdf', 'sections.pdf'], $e->drawings->pluck('original_name')->sort()->values()->all());
        foreach ($e->drawings as $d) {
            Storage::disk('local')->assertExists($d->path);
            $this->assertStringStartsWith('frame/'.$e->id.'/', $d->path);
        }
        $this->assertSame([], Storage::disk('local')->allFiles(HeldUploads::DIR));
        $this->assertNull(session('held_uploads.frame'));
    }

    public function test_a_rejected_file_is_not_held_and_a_new_choice_replaces_the_held_one(): void
    {
        $this->post('/timber-frame/enquire', $this->frame(['name' => '', 'drawings' => [UploadedFile::fake()->create('virus.exe', 10)]]))->assertSessionHasErrors(['name', 'drawings.0']);
        $this->assertSame([], Storage::disk('local')->allFiles(HeldUploads::DIR));

        $this->post('/timber-frame/enquire', $this->frame(['name' => '', 'drawings' => [$this->pdf('old.pdf')]]));
        $this->post('/timber-frame/enquire', $this->frame(['name' => '', 'drawings' => [$this->pdf('new.pdf')]]));
        $this->assertSame(['new.pdf'], HeldUploads::names('frame', 'drawings'));
        $this->assertCount(1, Storage::disk('local')->allFiles(HeldUploads::DIR));
    }

    public function test_a_cutting_list_survives_a_validation_error(): void
    {
        $form = [
            'customer_type' => 'pallet', 'company' => 'Sample Pallets', 'contact_name' => 'Callum Stewart', 'email' => 'c@example.com', 'phone' => '01294 123456',
            'country' => 'SCT', 'delivery_address' => 'Irvine', 'lines' => [['product' => 'pallet', 'treatment' => 'ht', 'quantity' => '40', 'unit' => 'm3']],
        ];
        $this->post('/trade-quote', array_merge($form, ['company' => '', 'schedule' => $this->pdf('cutting-list.pdf')]))->assertSessionHasErrors('company');
        $this->get('/trade-quote')->assertSee('cutting-list.pdf');
        $this->post('/trade-quote', $form)->assertRedirect('/thanks');
        $this->assertSame('cutting-list.pdf', QuoteRequest::sole()->schedule_name);
    }

    public function test_a_cv_survives_a_validation_error(): void
    {
        $form = ['category' => 'maintenance', 'first_name' => 'Fraser', 'last_name' => 'MacLeod', 'phone' => '07700 900142', 'email' => 'f@example.com', 'experience' => '5to10', 'right_to_work' => '1', 'consent' => '1'];
        $this->post('/careers/apply', array_merge($form, ['first_name' => '', 'cv' => $this->pdf('cv.pdf')]))->assertSessionHasErrors('first_name');
        $this->post('/careers/apply', $form)->assertRedirect('/thanks');
        $this->assertSame('cv.pdf', JobApplication::sole()->cv_name);
    }

    public function test_held_files_are_pruned_after_a_day(): void
    {
        $this->post('/careers/apply', ['category' => 'maintenance', 'cv' => $this->pdf('cv.pdf')]);
        $this->assertCount(1, Storage::disk('local')->allFiles(HeldUploads::DIR));

        $this->artisan('app:prune-held-uploads')->assertSuccessful();
        $this->assertCount(1, Storage::disk('local')->allFiles(HeldUploads::DIR), 'a fresh file is kept');

        touch(Storage::disk('local')->path(Storage::disk('local')->allFiles(HeldUploads::DIR)[0]), now()->subHours(25)->getTimestamp());
        $this->artisan('app:prune-held-uploads')->assertSuccessful();
        $this->assertSame([], Storage::disk('local')->allFiles(HeldUploads::DIR));
        $this->assertSame([], HeldUploads::names('application', 'cv'));
    }
}
