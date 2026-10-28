<?php

namespace App\Models;

use App\Contracts\Submission;
use App\Enums\Area;
use App\Enums\Country;
use App\Enums\CustomerType;
use App\Enums\QuoteStatus;
use App\Enums\Region;
use App\Enums\Role;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use Database\Factories\QuoteRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A trade or merchant request for prices: line items, a delivery address, and the sales owner
 * for the region the timber is going to (Ireland or UK).
 *
 * @property int $id
 * @property string $reference
 * @property QuoteStatus $status
 * @property CustomerType $customer_type
 * @property string $company
 * @property string|null $account_number
 * @property string $contact_name
 * @property string $email
 * @property string $phone
 * @property Country $country
 * @property Region $region
 * @property string $currency
 * @property string $delivery_address
 * @property string|null $postcode
 * @property Carbon|null $required_by
 * @property string|null $remarks
 * @property string|null $schedule_path
 * @property string|null $schedule_name
 * @property string|null $schedule_mime
 * @property int|null $schedule_size
 * @property int|null $assigned_to
 * @property string|null $quoted_total
 * @property Carbon|null $responded_at
 * @property Carbon|null $status_changed_at
 * @property string|null $source_url
 * @property Carbon $created_at
 * @property-read User|null $assignee
 * @property-read Collection<int, QuoteLine> $lines
 */
class QuoteRequest extends Model implements Submission
{
    /** @use HasFactory<QuoteRequestFactory> */
    use HasFactory, HasReference, HasTimeline;

    /** Sales should send a first answer within this many hours; older ones lead the dashboard. */
    public const int RESPONSE_HOURS = 24;

    public const int MAX_LINES = 12;

    /** A cutting list or schedule the customer already has. */
    public const array SCHEDULE_TYPES = ['pdf', 'xls', 'xlsx', 'csv', 'doc', 'docx'];

    protected $fillable = [
        'status', 'customer_type', 'company', 'account_number', 'contact_name', 'email', 'phone', 'country', 'region', 'currency',
        'delivery_address', 'postcode', 'required_by', 'remarks', 'schedule_path', 'schedule_name', 'schedule_mime', 'schedule_size',
        'assigned_to', 'quoted_total', 'responded_at', 'status_changed_at', 'source_url',
    ];

    public static function referencePrefix(): string
    {
        return 'QR';
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'status' => QuoteStatus::class,
            'customer_type' => CustomerType::class,
            'country' => Country::class,
            'region' => Region::class,
            'required_by' => 'date',
            'responded_at' => 'datetime',
            'status_changed_at' => 'datetime',
        ];
    }

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (self $quote): void {
            $quote->status ??= QuoteStatus::New;
            // The delivery country decides the team and the currency.
            $quote->region ??= $quote->country->region();
            $quote->currency ??= $quote->country->currency();
        });
        static::saving(function (self $quote): void {
            $quote->setAttribute('phone_digits', preg_replace('/\D+/', '', $quote->phone) ?: null);
        });
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return HasMany<QuoteLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(QuoteLine::class)->orderBy('position');
    }

    /**
     * Gives the request to the active salesperson in its region with the fewest open quotes
     * (ties go to whoever joined first). Nobody in the region: it waits unassigned for admin.
     */
    public function routeToRegion(): ?User
    {
        $owner = User::query()->active()->where('role', Role::Sales)->where('region', $this->region)
            ->withCount(['assignedQuotes as open_quotes' => fn (Builder $q) => $q->whereIn('status', QuoteStatus::open())])
            ->orderBy('open_quotes')->orderBy('id')->first();
        if ($owner) {
            $this->forceFill(['assigned_to' => $owner->id])->save();
            $this->log("Routed to {$this->region->team()}: {$owner->name}.", 'assign');
        } else {
            $this->log("No active salesperson in {$this->region->team()}; waiting for an administrator to assign it.", 'assign');
        }

        return $owner;
    }

    /**
     * Moves to $status with a conditional UPDATE, so the same change sent twice at once is
     * applied (and logged) only once.
     */
    public function claimStatus(QuoteStatus $status): bool
    {
        if ($this->status === $status) {
            return false;
        }
        $changes = ['status' => $status->value, 'status_changed_at' => now()];
        if (! $status->awaitsResponse() && $this->responded_at === null) {
            $changes['responded_at'] = now();
        }
        $claimed = static::query()->whereKey($this->getKey())->where('status', $this->getRawOriginal('status'))->update($changes) === 1;
        if ($claimed) {
            $this->forceFill($changes)->syncOriginal();
        }

        return $claimed;
    }

    /** New, with no answer, for longer than RESPONSE_HOURS. */
    public function isWaiting(): bool
    {
        return $this->status->awaitsResponse() && $this->created_at->lt(now()->subHours(self::RESPONSE_HOURS));
    }

    /** "Waiting 2 days", "3 h old" */
    public function ageLabel(): string
    {
        $hours = (int) $this->created_at->diffInHours(now());

        return $hours < 24 ? max(1, $hours).' h old' : 'Waiting '.intdiv($hours, 24).' '.str('day')->plural(intdiv($hours, 24));
    }

    /** @param Builder<self> $query */
    public function scopeWaiting(Builder $query): void
    {
        $query->whereIn('status', [QuoteStatus::New, QuoteStatus::Pricing])->where('created_at', '<', now()->subHours(self::RESPONSE_HOURS));
    }

    /** @param Builder<self> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', QuoteStatus::open());
    }

    /**
     * @param  Builder<self>  $query
     * @param  array<string, mixed>  $f
     */
    public function scopeFilter(Builder $query, array $f): void
    {
        $query
            ->when($f['status'] ?? null, fn (Builder $q, $v) => $v === 'open' ? $q->open() : $q->where('status', $v))
            ->when($f['region'] ?? null, fn (Builder $q, $v) => $q->where('region', $v))
            ->when($f['assigned'] ?? null, fn (Builder $q, $v) => $v === 'none' ? $q->whereNull('assigned_to') : $q->where('assigned_to', $v))
            ->when($f['waiting'] ?? null, fn (Builder $q) => $q->waiting())
            ->when($f['q'] ?? null, function (Builder $q, $term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $term).'%';
                $q->where(fn (Builder $w) => $w->where('company', 'like', $like)->orWhere('contact_name', 'like', $like)
                    ->orWhere('reference', 'like', $like)->orWhere('email', 'like', $like)->orWhere('postcode', 'like', $like));
            });
    }

    public function totalLabel(): string
    {
        return money($this->quoted_total, $this->currency);
    }

    public function referenceNumber(): string
    {
        return $this->reference;
    }

    public function kindLabel(): string
    {
        return 'Trade quote request';
    }

    public function area(): Area
    {
        return Area::Quotes;
    }

    public function senderName(): string
    {
        return $this->contact_name;
    }

    public function senderEmail(): ?string
    {
        return $this->email;
    }

    public function summary(): array
    {
        $summary = [
            'Company' => $this->company.' ('.$this->customer_type->label().')',
            'Contact' => $this->contact_name.' · '.format_phone($this->phone).' · '.$this->email,
            'Deliver to' => $this->delivery_address.($this->postcode ? ', '.$this->postcode : '').', '.$this->country->label(),
            'Sales team' => $this->region->team().($this->assignee ? ' ('.$this->assignee->name.')' : ''),
            'Needed by' => $this->required_by?->format('j F Y') ?? '',
        ];
        foreach ($this->lines as $line) {
            $summary['Line '.$line->position] = $line->oneLine().($line->delivery_site ? ' → '.$line->delivery_site : '');
        }

        return array_filter($summary);
    }

    public function adminUrl(): string
    {
        return route('admin.quotes.show', $this);
    }

    public function nextSteps(): string
    {
        return 'Your request has gone to our '.$this->region->team().' team, who will reply with prices in '.($this->currency === 'EUR' ? 'euro' : 'sterling').', usually within one working day.';
    }
}
