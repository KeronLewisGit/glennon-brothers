<?php

namespace Tests\Feature;

use App\Enums\JobCategory;
use App\Enums\Plant;
use App\Models\Enquiry;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Notifications\NewSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareersAndContactTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function application(array $overrides = []): array
    {
        return array_merge([
            'category' => 'operative', 'plant' => 'longford', 'first_name' => 'Darragh', 'last_name' => 'Walsh', 'phone' => '086 123 4567',
            'experience' => '2to5', 'home_area' => 'Longford', 'right_to_work' => '1', 'consent' => '1',
        ], $overrides);
    }

    public function test_operatives_can_apply_without_a_cv_or_email(): void
    {
        Notification::fake();
        config(['glennon.notify.careers' => 'hr@example.com']);
        $this->post('/careers/apply', $this->application())->assertRedirect('/thanks');
        $a = JobApplication::sole();
        $this->assertSame(sprintf('JA-%s-%05d', now()->format('y'), $a->id), $a->reference);
        $this->assertNull($a->cv_path);
        $this->assertSame(Plant::Longford, $a->plant);
        $this->assertTrue($a->right_to_work);
        Notification::assertSentOnDemand(NewSubmission::class, fn ($n, $c, $to) => $to->routes['mail'] === ['hr@example.com']);
    }

    public function test_maintenance_roles_need_a_cv_stored_privately(): void
    {
        Storage::fake('local');
        $this->post('/careers/apply', $this->application(['category' => 'maintenance']))->assertSessionHasErrors(['cv', 'email']);

        $this->post('/careers/apply', $this->application(['category' => 'maintenance', 'email' => 'e@example.com', 'cv' => UploadedFile::fake()->create('cv.pdf', 300, 'application/pdf')]))
            ->assertRedirect('/thanks');
        $a = JobApplication::sole();
        Storage::disk('local')->assertExists((string) $a->cv_path);
        $this->assertStringStartsWith('cvs/', (string) $a->cv_path);
        $this->post('/careers/apply', $this->application(['cv' => UploadedFile::fake()->create('cv.exe', 10)]))->assertSessionHasErrors('cv');
    }

    public function test_the_job_decides_the_category(): void
    {
        $job = JobPosting::factory()->create(['category' => JobCategory::Maintenance, 'plant' => Plant::Troon]);
        // Claims operative but applies to a fitter's post: the CV rule follows the job.
        $this->post('/careers/apply', $this->application(['job_posting_id' => $job->id, 'category' => 'operative']))->assertSessionHasErrors('cv');
        // The job's plant wins over the one chosen in the form.
        $this->post('/careers/apply', $this->application(['job_posting_id' => $job->id, 'email' => 'f@example.com', 'cv' => UploadedFile::fake()->create('cv.pdf', 30, 'application/pdf')]))->assertRedirect('/thanks');
        $this->assertSame(Plant::Troon, JobApplication::sole()->plant);
        $closed = JobPosting::factory()->create(['closes_on' => today()->subDay()]);
        $this->post('/careers/apply', $this->application(['job_posting_id' => $closed->id]))->assertSessionHasErrors('job_posting_id');
        $this->get('/careers/apply?job='.$job->slug)->assertOk()->assertSee($job->title);
    }

    public function test_drivers_give_their_licences_and_right_to_work_is_asked(): void
    {
        $this->post('/careers/apply', $this->application(['category' => 'driver']))->assertSessionHasErrors('licences');
        $this->post('/careers/apply', $this->application(['right_to_work' => null]))->assertSessionHasErrors('right_to_work');
        $this->post('/careers/apply', $this->application(['plant' => 'any']))->assertRedirect('/thanks');
        $this->assertNull(JobApplication::sole()->plant, 'any plant is stored as no plant');
    }

    public function test_contact_form_creates_an_enquiry(): void
    {
        Notification::fake();
        $this->post('/contact', ['name' => 'Orla Quinn', 'email' => 'O@Example.com', 'topic' => 'stockist', 'plant' => 'fermoy', 'message' => 'We would like to stock Glenfence panels.'])->assertRedirect('/thanks');
        $e = Enquiry::sole();
        $this->assertSame('o@example.com', $e->email);
        $this->assertSame(Plant::Fermoy, $e->plant);
        $this->assertStringStartsWith('EN-', $e->reference);
        $this->post('/contact', ['name' => 'x', 'email' => 'bad', 'topic' => 'nope', 'message' => 'short'])->assertSessionHasErrors(['email', 'topic', 'message']);
    }

    public function test_forms_are_rate_limited(): void
    {
        for ($i = 0; $i < 40; $i++) {
            $this->post('/contact', []);
        }
        $this->post('/contact', [])->assertStatus(429);
    }

    public function test_a_closed_opening_gets_a_plain_message(): void
    {
        $closed = JobPosting::factory()->create(['closes_on' => today()->subDay()]);
        $this->post('/careers/apply', $this->application(['job_posting_id' => $closed->id]))
            ->assertSessionHasErrors(['job_posting_id' => 'That opening has closed. Choose another opening, or send a general application.']);
    }

    public function test_one_email_address_cannot_be_sent_confirmations_in_bulk(): void
    {
        Notification::fake();
        for ($i = 0; $i < 10; $i++) {
            $this->post('/contact', ['name' => 'A', 'email' => 'victim@example.com', 'phone' => '0861234'.str_pad((string) $i, 3, '0', STR_PAD_LEFT), 'topic' => 'other', 'message' => 'Hello there, a message.'])->assertRedirect('/thanks');
        }
        $this->post('/contact', ['name' => 'A', 'email' => 'Victim@Example.com', 'topic' => 'other', 'message' => 'Hello there, a message.'])->assertStatus(429);
        $this->post('/contact', ['name' => 'A', 'email' => 'someone@example.com', 'topic' => 'other', 'message' => 'Hello there, a message.'])->assertRedirect('/thanks');
    }
}
