<?php

namespace Tests\Feature;

use App\Enums\QuoteStatus;
use App\Enums\Region;
use App\Enums\Role;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Notifications\NewSubmission;
use App\Notifications\SubmissionReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuoteRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
    }

    /** @return array<string, mixed> */
    private function form(array $overrides = []): array
    {
        return array_merge([
            'customer_type' => 'merchant', 'company' => 'Sample Builders Providers', 'contact_name' => 'Aoife Byrne', 'email' => 'Aoife@Example.com',
            'phone' => '090 123 4567', 'country' => 'IE', 'delivery_address' => 'Unit 4, Athlone Business Park', 'postcode' => 'n37 x2y4',
            'lines' => [
                ['product' => 'carcassing', 'description' => 'C16', 'thickness_mm' => '47', 'width_mm' => '150', 'length_m' => '4.8', 'treatment' => 'kd', 'quantity' => '6', 'unit' => 'packs'],
                ['product' => '', 'description' => '', 'thickness_mm' => '', 'width_mm' => '', 'length_m' => '', 'treatment' => 'any', 'quantity' => '', 'unit' => 'm3'],
                ['product' => 'fence_post', 'thickness_mm' => '100', 'width_mm' => '100', 'length_m' => '2.4', 'treatment' => 'treated', 'quantity' => '400', 'unit' => 'pcs'],
            ],
        ], $overrides);
    }

    public function test_a_quote_is_stored_with_its_lines_and_routed_to_the_irish_team(): void
    {
        $busy = User::factory()->sales(Region::Ireland)->create();
        $free = User::factory()->sales(Region::Ireland)->create();
        $uk = User::factory()->sales(Region::Uk)->create();
        QuoteRequest::factory()->create(['assigned_to' => $busy->id]);
        config(['glennon.notify.quotes' => 'sales@example.com']);

        $this->post('/trade-quote', $this->form())->assertRedirect('/thanks');
        $q = QuoteRequest::latest('id')->firstOrFail();
        $this->assertSame('aoife@example.com', $q->email);
        $this->assertSame('N37 X2Y4', $q->postcode);
        $this->assertSame(Region::Ireland, $q->region);
        $this->assertSame('EUR', $q->currency);
        $this->assertSame($free->id, $q->assigned_to, 'goes to the salesperson in the region with fewest open quotes');
        $this->assertNotSame($uk->id, $q->assigned_to);
        $this->assertSame(['carcassing', 'fence_post'], $q->lines()->orderBy('position')->pluck('product')->all(), 'the blank line is dropped');
        $this->assertStringStartsWith('QR-', $q->reference);
        $this->assertTrue($q->notes()->where('body', 'like', 'Routed to%')->exists());
        Notification::assertSentOnDemand(NewSubmission::class, fn ($n, $c, $to) => $to->routes['mail'] === ['sales@example.com']);
        Notification::assertSentOnDemand(SubmissionReceived::class);
        $this->get('/thanks')->assertSee($q->reference);
    }

    public function test_scottish_deliveries_go_to_the_uk_team_in_sterling(): void
    {
        $uk = User::factory()->sales(Region::Uk)->create();
        User::factory()->sales(Region::Ireland)->create();
        $this->post('/trade-quote', $this->form(['country' => 'SCT', 'postcode' => 'ka10 6dn']))->assertRedirect('/thanks');
        $q = QuoteRequest::sole();
        $this->assertSame(Region::Uk, $q->region);
        $this->assertSame('GBP', $q->currency);
        $this->assertSame($uk->id, $q->assigned_to);
    }

    public function test_with_nobody_in_the_region_the_quote_waits_unassigned(): void
    {
        $this->post('/trade-quote', $this->form(['country' => 'NI', 'postcode' => 'BT36 4PX']))->assertRedirect('/thanks');
        $q = QuoteRequest::sole();
        $this->assertSame(Region::Ireland, $q->region, 'Northern Ireland is served by the Ireland team');
        $this->assertSame('GBP', $q->currency);
        $this->assertNull($q->assigned_to);
    }

    public function test_postcodes_are_checked_against_the_country(): void
    {
        $this->post('/trade-quote', $this->form(['postcode' => 'KA10 6DN']))->assertSessionHasErrors('postcode');
        $this->post('/trade-quote', $this->form(['country' => 'SCT', 'postcode' => 'N39 AX73']))->assertSessionHasErrors('postcode');
        $this->post('/trade-quote', $this->form(['postcode' => '']))->assertRedirect('/thanks');
    }

    public function test_lines_are_required_and_validated(): void
    {
        $blank = ['product' => '', 'treatment' => 'any', 'unit' => 'm3', 'quantity' => ''];
        $this->post('/trade-quote', $this->form(['lines' => [$blank, $blank]]))->assertSessionHasErrors('lines');
        $this->post('/trade-quote', $this->form(['lines' => [['product' => 'plywood', 'treatment' => 'kd', 'quantity' => '1', 'unit' => 'm3']]]))->assertSessionHasErrors('lines.0.product');
        $this->post('/trade-quote', $this->form(['lines' => [['product' => 'cls', 'treatment' => 'kd', 'quantity' => '0', 'unit' => 'm3']]]))->assertSessionHasErrors('lines.0.quantity');
        $this->post('/trade-quote', $this->form(['lines' => array_fill(0, QuoteRequest::MAX_LINES + 1, ['product' => 'cls', 'treatment' => 'kd', 'quantity' => '1', 'unit' => 'm3'])]))->assertSessionHasErrors('lines');
        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_the_cutting_list_is_stored_privately_and_opens_for_sales_only(): void
    {
        $file = UploadedFile::fake()->createWithContent('list.csv', "product,qty\ncls,10\n");
        $this->post('/trade-quote', $this->form(['schedule' => $file]))->assertRedirect('/thanks');
        $q = QuoteRequest::sole();
        Storage::disk('local')->assertExists((string) $q->schedule_path);
        $this->assertStringStartsWith('quotes/', (string) $q->schedule_path);
        $this->assertFileDoesNotExist(public_path($q->schedule_path));

        $this->actingAs(User::factory()->role(Role::Hr)->create())->get(route('admin.quotes.schedule', $q))->assertForbidden();
        $this->actingAs(User::factory()->role(Role::Frame)->create())->get(route('admin.quotes.schedule', $q))->assertForbidden();
        $this->actingAs(User::factory()->sales(Region::Uk)->create())->get(route('admin.quotes.schedule', $q))->assertOk();
        $this->post('/trade-quote', $this->form(['schedule' => UploadedFile::fake()->create('x.exe', 5)]))->assertSessionHasErrors('schedule');
    }

    public function test_the_honeypot_drops_bots_quietly(): void
    {
        $this->post('/trade-quote', $this->form(['website' => 'http://spam.example']))->assertRedirect('/thanks');
        $this->assertSame(0, QuoteRequest::count());
        Notification::assertNothingSent();
    }

    public function test_sales_can_price_and_reassign_and_a_double_submit_applies_once(): void
    {
        $me = User::factory()->sales(Region::Ireland)->create();
        $colleague = User::factory()->sales(Region::Uk)->create();
        $hr = User::factory()->role(Role::Hr)->create();
        $q = QuoteRequest::factory()->create(['assigned_to' => $me->id]);
        $this->actingAs($me);

        $this->patch(route('admin.quotes.update', $q), ['status' => 'quoted'])->assertSessionHasErrors('quoted_total');
        $this->patch(route('admin.quotes.update', $q), ['status' => 'quoted', 'quoted_total' => '1250.50'])->assertRedirect();
        $this->patch(route('admin.quotes.update', $q), ['status' => 'quoted', 'quoted_total' => '1250.50'])->assertRedirect();
        $q->refresh();
        $this->assertSame(QuoteStatus::Quoted, $q->status);
        $this->assertNotNull($q->responded_at);
        $this->assertSame(1, $q->notes()->where('kind', 'status')->count());

        $this->patch(route('admin.quotes.update', $q), ['assigned_to' => $hr->id, 'status' => 'won'])->assertSessionHasErrors('assigned_to');
        $this->assertSame(QuoteStatus::Quoted, $q->refresh()->status, 'a refused assignment saves nothing');
        $this->patch(route('admin.quotes.update', $q), ['assigned_to' => $colleague->id])->assertRedirect();
        $this->assertSame($colleague->id, $q->refresh()->assigned_to);
    }

    public function test_quote_export_is_formula_safe(): void
    {
        QuoteRequest::factory()->create(['company' => '=HYPERLINK("x")']);
        $csv = $this->actingAs(User::factory()->role(Role::Admin)->create())->get(route('admin.quotes.export', ['region' => 'all']))->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }
}
