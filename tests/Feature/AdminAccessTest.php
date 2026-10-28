<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\FrameStatus;
use App\Enums\QuoteStatus;
use App\Enums\Region;
use App\Enums\Role;
use App\Models\DeliveryBooking;
use App\Models\Enquiry;
use App\Models\FrameEnquiry;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        foreach (['/admin', '/admin/quotes', '/admin/timber-frame', '/admin/applications', '/admin/bookings', '/admin/users'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_login_and_inactive_accounts(): void
    {
        $user = User::factory()->create(['email' => 'sales@example.com']);
        $this->post('/login', ['email' => 'SALES@example.com', 'password' => 'password'])->assertRedirect('/admin');
        $user->update(['active' => false]);
        $this->app['auth']->forgetGuards(); // the next request loads the user afresh, as a real one would
        $this->get('/admin')->assertRedirect('/login');
        $this->assertGuest();
        $this->post('/login', ['email' => 'sales@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
    }

    /** @return array<string, array{Role, array<string, int>}> */
    public static function matrix(): array
    {
        $all = fn (int $q, int $f, int $c, int $b, int $e, int $u) => ['/admin/quotes' => $q, '/admin/quotes/export' => $q, '/admin/timber-frame' => $f, '/admin/timber-frame/export' => $f,
            '/admin/applications' => $c, '/admin/jobs' => $c, '/admin/applications/export' => $c, '/admin/bookings' => $b, '/admin/enquiries' => $e, '/admin/users' => $u];

        return [
            'admin' => [Role::Admin, $all(200, 200, 200, 200, 200, 200)],
            'sales' => [Role::Sales, $all(200, 403, 403, 200, 200, 403)],
            'hr' => [Role::Hr, $all(403, 403, 200, 403, 403, 403)],
            'frame' => [Role::Frame, $all(403, 200, 403, 403, 403, 403)],
        ];
    }

    /** @param array<string, int> $expect */
    #[DataProvider('matrix')]
    public function test_each_role_sees_only_its_areas(Role $role, array $expect): void
    {
        $this->actingAs($this->staff($role));
        $this->get('/admin')->assertOk();
        foreach ($expect as $url => $status) {
            $this->get($url)->assertStatus($status);
        }
    }

    public function test_record_pages_follow_the_same_boundaries(): void
    {
        $quote = QuoteRequest::factory()->create();
        $frame = FrameEnquiry::factory()->create();
        $app = JobApplication::factory()->create();
        $booking = DeliveryBooking::create(['plant' => 'troon', 'purpose' => 'collect', 'slot_date' => today()->addDay(), 'slot' => '0800', 'haulier' => 'H', 'email' => 'h@example.com', 'phone' => '01292 800600', 'vehicle_reg' => 'SK24 ABC', 'vehicle_type' => 'flatbed']);
        $pages = [
            'quote' => route('admin.quotes.show', $quote), 'frame' => route('admin.frame.show', $frame),
            'app' => route('admin.applications.show', $app), 'booking' => route('admin.bookings.show', $booking),
        ];
        $can = [Role::Sales->value => ['quote', 'booking'], Role::Hr->value => ['app'], Role::Frame->value => ['frame'], Role::Admin->value => array_keys($pages)];
        foreach (Role::cases() as $role) {
            $this->actingAs($this->staff($role));
            foreach ($pages as $key => $url) {
                $this->get($url)->assertStatus(in_array($key, $can[$role->value], true) ? 200 : 403);
            }
        }
        // Writes too, not only reads.
        $this->actingAs($this->staff(Role::Hr))->patch(route('admin.quotes.update', $quote), ['status' => 'won'])->assertForbidden();
        $this->actingAs($this->staff(Role::Sales))->patch(route('admin.frame.update', $frame), ['status' => 'won'])->assertForbidden();
        $this->actingAs($this->staff(Role::Frame))->patch(route('admin.applications.update', $app), ['status' => 'hired'])->assertForbidden();
        $this->actingAs($this->staff(Role::Sales))->post(route('admin.frame.revoke', $frame))->assertForbidden();
    }

    public function test_dashboard_leads_with_the_number_for_the_role(): void
    {
        $ie = User::factory()->sales(Region::Ireland)->create();
        $uk = User::factory()->sales(Region::Uk)->create();
        QuoteRequest::factory()->count(2)->create(['created_at' => now()->subHours(30)]);
        QuoteRequest::factory()->uk()->create(['created_at' => now()->subHours(50)]);
        QuoteRequest::factory()->create(['created_at' => now()->subHours(2)]);
        $this->actingAs($ie)->get('/admin')->assertSee('quote requests have waited over 24 hours')->assertSee('>02<', false);
        $this->actingAs($uk)->get('/admin')->assertSee('quote request has waited over 24 hours')->assertSee('>01<', false);
        $this->actingAs($this->staff(Role::Admin))->get('/admin')->assertSee('>03<', false);

        FrameEnquiry::factory()->create(['created_at' => now()->subDays(4)]);
        $this->actingAs($this->staff(Role::Frame))->get('/admin')->assertSee('timber frame enquiry has waited over 2 days')->assertSee('>01<', false);

        JobApplication::factory()->create(['created_at' => now()->subDays(5)]);
        JobApplication::factory()->create(['status' => ApplicationStatus::Interview, 'created_at' => now()->subDays(9)]);
        $this->actingAs($this->staff(Role::Hr))->get('/admin')->assertSee('application has sat in new or HR screening for over 3 days')->assertSee('>01<', false);
    }

    public function test_cvs_open_only_for_hr_and_admin(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('cvs/a.pdf', '%PDF-1.4');
        $a = JobApplication::factory()->create(['cv_path' => 'cvs/a.pdf', 'cv_name' => 'cv.pdf', 'cv_mime' => 'application/pdf', 'cv_size' => 8]);
        $b = JobApplication::factory()->create();

        $this->actingAs($this->staff(Role::Sales))->get(route('admin.applications.cv', $a))->assertForbidden();
        $this->actingAs($this->staff(Role::Frame))->get(route('admin.applications.cv', $a))->assertForbidden();
        $this->actingAs($this->staff(Role::Hr))->get(route('admin.applications.cv', $a))->assertOk();
        $this->actingAs($this->staff(Role::Hr))->get(route('admin.applications.cv', $b))->assertForbidden();
        $this->actingAs($this->staff(Role::Admin))->get(route('admin.applications.cv', $a))->assertOk();
    }

    public function test_hr_moves_applications_through_stages_and_hands_them_to_a_reviewer(): void
    {
        $hr = $this->staff(Role::Hr);
        $other = $this->staff(Role::Hr);
        $sales = $this->staff(Role::Sales);
        $a = JobApplication::factory()->create();
        $this->actingAs($hr);
        $this->patch(route('admin.applications.update', $a), ['status' => 'screening', 'reviewer_id' => $other->id])->assertRedirect();
        $a->refresh();
        $this->assertSame(ApplicationStatus::Screening, $a->status);
        $this->assertSame($other->id, $a->reviewer_id);
        $this->patch(route('admin.applications.update', $a), ['reviewer_id' => $sales->id, 'status' => 'interview'])->assertSessionHasErrors('reviewer_id');
        $this->assertSame(ApplicationStatus::Screening, $a->refresh()->status, 'a refused hand-over saves nothing');
        $this->get(route('admin.applications.index', ['reviewer' => 'me']))->assertOk()->assertDontSee($a->full_name);
        $this->get(route('admin.applications.index', ['reviewer' => $other->id]))->assertOk()->assertSee($a->full_name);
    }

    public function test_csv_exports_are_formula_safe(): void
    {
        JobApplication::factory()->create(['first_name' => '+cmd', 'home_area' => '=1+1']);
        $csv = $this->actingAs($this->staff(Role::Hr))->get(route('admin.applications.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString("'+cmd", $csv);
        $this->assertStringContainsString("'=1+1", $csv);
        $this->assertStringContainsString('Right to work', $csv);
    }

    public function test_every_admin_screen_renders_with_data(): void
    {
        $this->seed(DemoSeeder::class);
        $admin = User::where('email', 'admin@glennonbrothers.test')->firstOrFail();
        $this->actingAs($admin);
        $urls = ['/admin', '/admin/quotes', '/admin/quotes?status=open&region=all', '/admin/quotes?waiting=1', '/admin/quotes?assigned=none', '/admin/timber-frame', '/admin/timber-frame?waiting=1',
            '/admin/applications', '/admin/applications?plant=any', '/admin/jobs', '/admin/jobs/create', '/admin/bookings', '/admin/bookings?plant=troon', '/admin/enquiries', '/admin/users'];
        $urls[] = route('admin.quotes.show', QuoteRequest::firstOrFail());
        $urls[] = route('admin.frame.show', FrameEnquiry::firstOrFail());
        $urls[] = route('admin.applications.show', JobApplication::firstOrFail());
        $urls[] = route('admin.jobs.edit', JobPosting::firstOrFail());
        $urls[] = route('admin.bookings.show', DeliveryBooking::firstOrFail());
        $urls[] = route('admin.enquiries.show', Enquiry::firstOrFail());
        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['sales', 'sales.uk', 'hr', 'frame'] as $login) {
            $this->actingAs(User::where('email', $login.'@glennonbrothers.test')->firstOrFail())->get('/admin')->assertOk();
        }
        $this->assertSame(0, QuoteRequest::whereNull('assigned_to')->count(), 'demo quotes are all routed');
    }

    public function test_job_postings_crud(): void
    {
        $this->actingAs($this->staff(Role::Hr));
        $form = ['title' => 'Kiln Operator', 'plant' => 'fermoy', 'category' => 'operative', 'employment_type' => 'shifts', 'status' => 'open', 'hours' => 'Rotating shifts', 'openings' => 2, 'summary' => 'Load and run the kilns.'];
        $this->post(route('admin.jobs.store'), $form)->assertRedirect(route('admin.jobs.index'));
        $job = JobPosting::sole();
        $this->assertSame('kiln-operator', $job->slug);
        $this->get('/plants/fermoy')->assertSee('Kiln Operator');
        $this->put(route('admin.jobs.update', $job), ['status' => 'closed'] + $form)->assertRedirect();
        $this->get('/careers/kiln-operator')->assertNotFound();
    }

    public function test_staff_management_is_admin_only_and_sales_need_a_region(): void
    {
        $admin = $this->staff(Role::Admin);
        $this->actingAs($admin)->post(route('admin.users.store'), ['name' => 'New Rep', 'email' => 'REP@x.com', 'role' => 'sales', 'password' => 'long-password'])->assertSessionHasErrors('region');
        $this->post(route('admin.users.store'), ['name' => 'New Rep', 'email' => 'REP@x.com', 'role' => 'sales', 'region' => 'uk', 'password' => 'long-password'])->assertRedirect();
        $rep = User::where('email', 'rep@x.com')->firstOrFail();
        $this->assertSame(Region::Uk, $rep->region);
        $this->patch(route('admin.users.update', $rep), ['role' => 'hr', 'region' => 'uk', 'active' => '1'])->assertRedirect();
        $this->assertNull($rep->refresh()->region, 'only sales belong to a region');
        $this->patch(route('admin.users.update', $admin), ['role' => 'hr', 'active' => '1'])->assertSessionHasErrors('role');
        $this->actingAs($this->staff(Role::Hr))->post(route('admin.users.store'), [])->assertForbidden();
    }

    public function test_a_status_change_sent_twice_at_once_is_applied_once(): void
    {
        $quote = QuoteRequest::factory()->create();
        $first = QuoteRequest::findOrFail($quote->id);
        $second = QuoteRequest::findOrFail($quote->id);
        $this->assertTrue($first->claimStatus(QuoteStatus::Pricing));
        $this->assertFalse($second->claimStatus(QuoteStatus::Pricing));

        $frame = FrameEnquiry::factory()->create();
        $a = FrameEnquiry::findOrFail($frame->id);
        $b = FrameEnquiry::findOrFail($frame->id);
        $this->assertTrue($a->claimStatus(FrameStatus::DesignReview));
        $this->assertFalse($b->claimStatus(FrameStatus::DesignReview));
    }
}
