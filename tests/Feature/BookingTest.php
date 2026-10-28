<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\Region;
use App\Enums\Role;
use App\Models\DeliveryBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function form(array $overrides = []): array
    {
        return array_merge([
            'plant' => 'troon', 'purpose' => 'collect', 'slot_date' => today()->addDays(2)->format('Y-m-d'), 'slot' => '0800',
            'haulier' => 'Sample Haulage', 'email' => 'Dispatch@Example.com', 'phone' => '01292 800600', 'vehicle_reg' => 'sk24 abc', 'vehicle_type' => 'curtainsider',
        ], $overrides);
    }

    public function test_a_haulier_books_a_slot(): void
    {
        Notification::fake();
        $this->post('/haulier-booking', $this->form())->assertRedirect('/thanks');
        $b = DeliveryBooking::sole();
        $this->assertSame('SK24 ABC', $b->vehicle_reg);
        $this->assertSame('dispatch@example.com', $b->email);
        $this->assertSame(BookingStatus::Requested, $b->status);
        $this->assertStringStartsWith('HB-', $b->reference);
    }

    public function test_slot_rules(): void
    {
        $this->post('/haulier-booking', $this->form(['slot_date' => today()->format('Y-m-d')]))->assertSessionHasErrors('slot_date');
        $this->post('/haulier-booking', $this->form(['slot_date' => today()->addDays(DeliveryBooking::MAX_DAYS_AHEAD + 1)->format('Y-m-d')]))->assertSessionHasErrors('slot_date');
        $this->post('/haulier-booking', $this->form(['plant' => 'dte']))->assertSessionHasErrors('plant');
        $this->post('/haulier-booking', $this->form(['slot' => '0300']))->assertSessionHasErrors('slot');
        $this->post('/haulier-booking', $this->form(['vehicle_reg' => '<b>']))->assertSessionHasErrors('vehicle_reg');
        $this->assertSame(0, DeliveryBooking::count());
        $this->get('/haulier-booking?plant=fermoy')->assertOk()->assertSeeHtml('value="fermoy"');
        $this->assertMatchesRegularExpression('/value="fermoy"\s+checked/', (string) $this->get('/haulier-booking?plant=fermoy')->getContent());
    }

    public function test_sales_confirm_a_booking_once(): void
    {
        Notification::fake();
        $this->post('/haulier-booking', $this->form());
        $b = DeliveryBooking::sole();
        $this->actingAs(User::factory()->sales(Region::Uk)->create());
        $this->patch(route('admin.bookings.update', $b), ['status' => 'confirmed'])->assertRedirect();
        $this->patch(route('admin.bookings.update', $b), ['status' => 'confirmed'])->assertRedirect();
        $this->assertSame(BookingStatus::Confirmed, $b->refresh()->status);
        $this->assertSame(1, $b->notes()->where('kind', 'status')->count());
        $this->actingAs(User::factory()->role(Role::Frame)->create())->patch(route('admin.bookings.update', $b), ['status' => 'declined'])->assertForbidden();
    }
}
