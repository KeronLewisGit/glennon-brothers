<?php

namespace App\Models;

use App\Contracts\Submission;
use App\Enums\Area;
use App\Enums\BookingPurpose;
use App\Enums\BookingSlot;
use App\Enums\BookingStatus;
use App\Enums\Plant;
use App\Enums\VehicleType;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A haulier asking for a loading or tipping slot at one of the mills.
 *
 * @property int $id
 * @property string $reference
 * @property BookingStatus $status
 * @property Plant $plant
 * @property BookingPurpose $purpose
 * @property Carbon $slot_date
 * @property BookingSlot $slot
 * @property string $haulier
 * @property string|null $driver_name
 * @property string $email
 * @property string $phone
 * @property string $vehicle_reg
 * @property VehicleType $vehicle_type
 * @property string|null $order_reference
 * @property string|null $remarks
 * @property Carbon|null $status_changed_at
 * @property Carbon $created_at
 */
class DeliveryBooking extends Model implements Submission
{
    use HasReference, HasTimeline;

    /** Slots can be asked for up to this many days ahead. */
    public const int MAX_DAYS_AHEAD = 28;

    protected $fillable = [
        'status', 'plant', 'purpose', 'slot_date', 'slot', 'haulier', 'driver_name', 'email', 'phone', 'vehicle_reg',
        'vehicle_type', 'order_reference', 'remarks', 'status_changed_at', 'source_url',
    ];

    public static function referencePrefix(): string
    {
        return 'HB';
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'plant' => Plant::class,
            'purpose' => BookingPurpose::class,
            'slot' => BookingSlot::class,
            'vehicle_type' => VehicleType::class,
            'slot_date' => 'date',
            'status_changed_at' => 'datetime',
        ];
    }

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (self $booking): void {
            $booking->status ??= BookingStatus::Requested;
        });
        static::saving(function (self $booking): void {
            $booking->vehicle_reg = strtoupper(trim((string) preg_replace('/\s+/', ' ', $booking->vehicle_reg)));
        });
    }

    public function claimStatus(BookingStatus $status): bool
    {
        if ($this->status === $status) {
            return false;
        }
        $changes = ['status' => $status->value, 'status_changed_at' => now()];
        $claimed = static::query()->whereKey($this->getKey())->where('status', $this->getRawOriginal('status'))->update($changes) === 1;
        if ($claimed) {
            $this->forceFill($changes)->syncOriginal();
        }

        return $claimed;
    }

    public function slotLabel(): string
    {
        return $this->slot_date->format('D j M').', '.$this->slot->label();
    }

    /**
     * Requested slots in the next two days that nobody has confirmed yet.
     *
     * @param  Builder<self>  $query
     */
    public function scopeNeedsAnswer(Builder $query): void
    {
        $query->where('status', BookingStatus::Requested)->whereDate('slot_date', '<=', today()->addDays(2));
    }

    /**
     * @param  Builder<self>  $query
     * @param  array<string, mixed>  $f
     */
    public function scopeFilter(Builder $query, array $f): void
    {
        $query
            ->when($f['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($f['plant'] ?? null, fn (Builder $q, $v) => $q->where('plant', $v))
            ->when($f['q'] ?? null, function (Builder $q, $term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $term).'%';
                $q->where(fn (Builder $w) => $w->where('haulier', 'like', $like)->orWhere('vehicle_reg', 'like', $like)
                    ->orWhere('reference', 'like', $like)->orWhere('order_reference', 'like', $like));
            });
    }

    public function referenceNumber(): string
    {
        return $this->reference;
    }

    public function kindLabel(): string
    {
        return 'Haulier booking';
    }

    public function area(): Area
    {
        return Area::Deliveries;
    }

    public function senderName(): string
    {
        return $this->driver_name ?: $this->haulier;
    }

    public function senderEmail(): ?string
    {
        return $this->email;
    }

    public function summary(): array
    {
        return array_filter([
            'Haulier' => $this->haulier,
            'Plant' => $this->plant->label(),
            'Slot' => $this->slotLabel(),
            'Purpose' => $this->purpose->label(),
            'Vehicle' => $this->vehicle_reg.' · '.$this->vehicle_type->label(),
            'Order reference' => (string) $this->order_reference,
            'Contact' => ($this->driver_name ? $this->driver_name.' · ' : '').format_phone($this->phone),
        ]);
    }

    public function adminUrl(): string
    {
        return route('admin.bookings.show', $this);
    }

    public function nextSteps(): string
    {
        return 'This is a request, not yet a confirmed slot. The yard at '.$this->plant->label().' will confirm or offer another time by email or phone. Please do not travel until it is confirmed.';
    }
}
