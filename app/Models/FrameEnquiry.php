<?php

namespace App\Models;

use App\Contracts\Submission;
use App\Enums\Area;
use App\Enums\FrameClientType;
use App\Enums\FrameProjectType;
use App\Enums\FrameStatus;
use App\Enums\PlanningStatus;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use Database\Factories\FrameEnquiryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * A timber frame or roof truss enquiry for DTE: site, planning, timeline and drawings. The
 * customer gets a signed link (drawingsUrl) to send revised or extra drawings later.
 *
 * @property int $id
 * @property string $reference
 * @property string $public_id
 * @property string $access_key
 * @property FrameStatus $status
 * @property FrameClientType $client_type
 * @property FrameProjectType $project_type
 * @property string $name
 * @property string|null $company
 * @property string $email
 * @property string $phone
 * @property string $site_address
 * @property string $county
 * @property string|null $eircode
 * @property PlanningStatus $planning_status
 * @property string $start_month
 * @property int|null $floor_area_m2
 * @property int|null $units
 * @property string|null $message
 * @property int|null $assigned_to
 * @property string|null $quoted_total
 * @property Carbon|null $status_changed_at
 * @property string|null $source_url
 * @property Carbon $created_at
 * @property-read User|null $assignee
 * @property-read Collection<int, FrameDrawing> $drawings
 */
class FrameEnquiry extends Model implements Submission
{
    /** @use HasFactory<FrameEnquiryFactory> */
    use HasFactory, HasReference, HasTimeline, HasUlids;

    /** New enquiries untouched for longer than this many days lead the DTE dashboard. */
    public const int REVIEW_DAYS = 2;

    /** How long the customer's drawings link works. */
    public const int LINK_DAYS = 30;

    public const array DRAWING_TYPES = ['pdf', 'dwg', 'dxf', 'jpg', 'jpeg', 'png', 'zip'];

    public const int MAX_DRAWINGS = 8;

    /** Kilobytes per drawing. */
    public const int MAX_DRAWING_KB = 20480;

    protected $fillable = [
        'status', 'client_type', 'project_type', 'name', 'company', 'email', 'phone', 'site_address', 'county', 'eircode',
        'planning_status', 'start_month', 'floor_area_m2', 'units', 'message', 'assigned_to', 'quoted_total', 'status_changed_at', 'source_url',
    ];

    public static function referencePrefix(): string
    {
        return 'TF';
    }

    /** @return list<string> */
    #[\Override]
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'status' => FrameStatus::class,
            'client_type' => FrameClientType::class,
            'project_type' => FrameProjectType::class,
            'planning_status' => PlanningStatus::class,
            'floor_area_m2' => 'integer',
            'units' => 'integer',
            'status_changed_at' => 'datetime',
        ];
    }

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (self $enquiry): void {
            $enquiry->status ??= FrameStatus::New;
            $enquiry->access_key ??= Str::random(40);
        });
        static::saving(function (self $enquiry): void {
            $enquiry->setAttribute('phone_digits', preg_replace('/\D+/', '', $enquiry->phone) ?: null);
        });
    }

    /** The id column stays the route key in the back office; the customer link uses public_id. */
    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return HasMany<FrameDrawing, $this> */
    public function drawings(): HasMany
    {
        return $this->hasMany(FrameDrawing::class)->latest('id');
    }

    /** Signed, expiring link the customer uses to send more drawings. */
    public function drawingsUrl(): string
    {
        return URL::temporarySignedRoute('frame.drawings', now()->addDays(self::LINK_DAYS), ['enquiry' => $this->public_id, 'key' => $this->access_key]);
    }

    /** Rotates the key, so every link already sent stops working. */
    public function revokeLink(): void
    {
        $this->forceFill(['access_key' => Str::random(40)])->save();
    }

    public function claimStatus(FrameStatus $status): bool
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

    public function startMonth(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->start_month.'-01') ?: now();
    }

    public function startLabel(): string
    {
        return $this->startMonth()->format('F Y');
    }

    public function isWaiting(): bool
    {
        return $this->status === FrameStatus::New && $this->created_at->lt(now()->subDays(self::REVIEW_DAYS));
    }

    /** @param Builder<self> $query */
    public function scopeWaiting(Builder $query): void
    {
        $query->where('status', FrameStatus::New)->where('created_at', '<', now()->subDays(self::REVIEW_DAYS));
    }

    /**
     * @param  Builder<self>  $query
     * @param  array<string, mixed>  $f
     */
    public function scopeFilter(Builder $query, array $f): void
    {
        $query
            ->when($f['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($f['type'] ?? null, fn (Builder $q, $v) => $q->where('project_type', $v))
            ->when($f['waiting'] ?? null, fn (Builder $q) => $q->waiting())
            ->when($f['q'] ?? null, function (Builder $q, $term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $term).'%';
                $q->where(fn (Builder $w) => $w->where('name', 'like', $like)->orWhere('company', 'like', $like)
                    ->orWhere('reference', 'like', $like)->orWhere('county', 'like', $like)->orWhere('eircode', 'like', $like));
            });
    }

    public function referenceNumber(): string
    {
        return $this->reference;
    }

    public function kindLabel(): string
    {
        return 'Timber frame enquiry';
    }

    public function area(): Area
    {
        return Area::Frame;
    }

    public function senderName(): string
    {
        return $this->name;
    }

    public function senderEmail(): ?string
    {
        return $this->email;
    }

    public function summary(): array
    {
        return array_filter([
            'Client' => $this->name.($this->company ? ', '.$this->company : '').' ('.$this->client_type->label().')',
            'Project' => $this->project_type->label().($this->units ? ', '.$this->units.' units' : ''),
            'Site' => $this->site_address.', Co. '.$this->county.($this->eircode ? ', '.$this->eircode : ''),
            'Planning' => $this->planning_status->label(),
            'Hoping to start' => $this->startLabel(),
            'Floor area' => $this->floor_area_m2 ? $this->floor_area_m2.' m²' : '',
            'Drawings' => $this->drawings->count().' attached',
            'Contact' => format_phone($this->phone).' · '.$this->email,
        ]);
    }

    public function adminUrl(): string
    {
        return route('admin.frame.show', $this);
    }

    public function nextSteps(): string
    {
        return 'The DTE design team will look at your drawings and call you to talk through the frame and the timeline. To send more drawings later, use this private link (it works for '.self::LINK_DAYS.' days): '.$this->drawingsUrl();
    }
}
