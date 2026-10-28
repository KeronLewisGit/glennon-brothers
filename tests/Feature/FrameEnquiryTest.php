<?php

namespace Tests\Feature;

use App\Enums\FrameStatus;
use App\Enums\Region;
use App\Enums\Role;
use App\Models\FrameEnquiry;
use App\Models\User;
use App\Notifications\SubmissionReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FrameEnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
    }

    private function pdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj << >> endobj\ntrailer << >>\n%%EOF\n");
    }

    /** @return array<string, mixed> */
    private function form(array $overrides = []): array
    {
        return array_merge([
            'client_type' => 'self_builder', 'project_type' => 'house', 'name' => 'Niamh Kelly', 'email' => 'niamh@example.com', 'phone' => '087 123 4567',
            'site_address' => 'Site at Ballinalee Road', 'county' => 'Longford', 'eircode' => 'n39ax73', 'planning_status' => 'granted',
            'start_month' => now()->addMonths(4)->format('Y-m'), 'floor_area_m2' => '210',
        ], $overrides);
    }

    public function test_an_enquiry_with_drawings_is_stored_privately_and_emails_a_signed_link(): void
    {
        $this->post('/timber-frame/enquire', $this->form(['drawings' => [$this->pdf('plans.pdf'), $this->pdf('elevations.pdf')]]))->assertRedirect('/thanks');
        $e = FrameEnquiry::sole();
        $this->assertSame('N39 AX73', $e->eircode);
        $this->assertStringStartsWith('TF-', $e->reference);
        $this->assertCount(2, $e->drawings);
        foreach ($e->drawings as $d) {
            Storage::disk('local')->assertExists($d->path);
            $this->assertStringStartsWith('frame/'.$e->id.'/', $d->path);
        }
        $this->assertSame(40, strlen($e->access_key));
        Notification::assertSentOnDemand(SubmissionReceived::class, fn ($n) => str_contains($n->submission->nextSteps(), '/timber-frame/drawings/'.$e->public_id));
    }

    public function test_validation_rules(): void
    {
        $this->post('/timber-frame/enquire', $this->form(['project_type' => 'scheme']))->assertSessionHasErrors('units');
        $this->post('/timber-frame/enquire', $this->form(['eircode' => 'ABC']))->assertSessionHasErrors('eircode');
        $this->post('/timber-frame/enquire', $this->form(['county' => 'Atlantis']))->assertSessionHasErrors('county');
        $this->post('/timber-frame/enquire', $this->form(['start_month' => now()->subMonth()->format('Y-m')]))->assertSessionHasErrors('start_month');
        $this->post('/timber-frame/enquire', $this->form(['drawings' => [UploadedFile::fake()->create('x.exe', 5)]]))->assertSessionHasErrors('drawings.0');
        $this->assertSame(0, FrameEnquiry::count());
        // Five tries a minute per phone number: the last one uses another number.
        $this->post('/timber-frame/enquire', $this->form(['project_type' => 'scheme', 'units' => '12', 'eircode' => '', 'phone' => '087 000 1111']))->assertRedirect('/thanks');
    }

    public function test_the_signed_link_takes_more_drawings_and_dies_when_revoked_tampered_or_expired(): void
    {
        $e = FrameEnquiry::factory()->create();
        $url = $e->drawingsUrl();

        $this->get($url)->assertOk()->assertSee($e->reference)->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertHeader('Referrer-Policy', 'no-referrer');
        $this->post($url, ['drawings' => [$this->pdf('rev-b.pdf')]])->assertRedirect();
        $this->assertSame(['rev-b.pdf'], $e->drawings()->pluck('original_name')->all());

        // Unsigned, or with another key: refused.
        $this->get(route('frame.drawings', ['enquiry' => $e->public_id, 'key' => $e->access_key]))->assertForbidden();
        $this->get(str_replace($e->access_key, str_repeat('a', 40), $url))->assertForbidden();

        // Revoked: the old link stops working, a fresh one works.
        $e->revokeLink();
        $this->get($url)->assertNotFound();
        $this->get($e->refresh()->drawingsUrl())->assertOk();

        // Expired.
        $this->travel(FrameEnquiry::LINK_DAYS + 1)->days();
        $this->get($url)->assertForbidden();
    }

    public function test_a_closed_enquiry_stops_taking_drawings(): void
    {
        $e = FrameEnquiry::factory()->create();
        $e->claimStatus(FrameStatus::Lost);
        $url = $e->drawingsUrl();
        $this->get($url)->assertOk()->assertSee('no longer takes drawings');
        $this->post($url, ['drawings' => [$this->pdf('late.pdf')]])->assertStatus(410);
    }

    public function test_drawings_open_only_for_dte_and_admin_and_only_through_their_own_enquiry(): void
    {
        $e = FrameEnquiry::factory()->create();
        $other = FrameEnquiry::factory()->create();
        $this->post($e->drawingsUrl(), ['drawings' => [$this->pdf('plan.pdf')]]);
        $d = $e->drawings()->firstOrFail();

        $this->actingAs(User::factory()->sales(Region::Ireland)->create())->get(route('admin.frame.drawing', [$e, $d]))->assertForbidden();
        $this->actingAs(User::factory()->role(Role::Hr)->create())->get(route('admin.frame.drawing', [$e, $d]))->assertForbidden();
        $frame = User::factory()->role(Role::Frame)->create();
        $this->actingAs($frame)->get(route('admin.frame.drawing', [$other, $d]))->assertForbidden();
        $this->actingAs($frame)->get(route('admin.frame.drawing', [$e, $d]))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->actingAs(User::factory()->role(Role::Admin)->create())->get(route('admin.frame.drawing', [$e, $d]))->assertOk();
        $this->assertTrue($e->notes()->where('body', 'like', 'Opened plan.pdf%')->exists());
    }

    public function test_staff_revoke_from_the_back_office(): void
    {
        $e = FrameEnquiry::factory()->create();
        $url = $e->drawingsUrl();
        $this->actingAs(User::factory()->role(Role::Frame)->create())->post(route('admin.frame.revoke', $e))->assertRedirect();
        $this->get($url)->assertNotFound();
    }
}
