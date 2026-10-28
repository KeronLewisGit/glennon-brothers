<?php

namespace App\Models;

use App\Contracts\Submission;
use App\Enums\ApplicationStatus;
use App\Enums\Area;
use App\Enums\Experience;
use App\Enums\JobCategory;
use App\Enums\Plant;
use App\Models\Concerns\HasReference;
use App\Models\Concerns\HasTimeline;
use Database\Factories\JobApplicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property int|null $job_posting_id
 * @property Plant|null $plant
 * @property int|null $reviewer_id
 * @property JobCategory $category
 * @property ApplicationStatus $status
 * @property string $first_name
 * @property string $last_name
 * @property string $phone
 * @property string|null $email
 * @property string|null $home_area
 * @property bool $right_to_work
 * @property string|null $licences
 * @property Experience $experience
 * @property string|null $qualifications
 * @property Carbon|null $available_from
 * @property string|null $message
 * @property string|null $cv_path
 * @property string|null $cv_name
 * @property string|null $cv_mime
 * @property int|null $cv_size
 * @property Carbon|null $consented_at
 * @property Carbon|null $status_changed_at
 * @property Carbon $created_at
 * @property-read string $full_name
 * @property-read JobPosting|null $jobPosting
 * @property-read User|null $reviewer
 */
class JobApplication extends Model implements Submission
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory, HasReference, HasTimeline;

    public const array CV_TYPES = ['pdf', 'doc', 'docx'];

    /** New applications older than this many days count as waiting on the HR dashboard. */
    public const int REVIEW_DAYS = 3;

    protected $fillable = [
        'job_posting_id', 'plant', 'category', 'status', 'reviewer_id', 'first_name', 'last_name', 'phone', 'email', 'home_area',
        'right_to_work', 'experience', 'licences', 'qualifications', 'available_from', 'message', 'cv_path', 'cv_name', 'cv_mime', 'cv_size', 'consented_at', 'status_changed_at',
    ];

    public static function referencePrefix(): string
    {
        return 'JA';
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'category' => JobCategory::class,
            'plant' => Plant::class,
            'right_to_work' => 'boolean',
            'status' => ApplicationStatus::class,
            'experience' => Experience::class,
            'available_from' => 'date',
            'consented_at' => 'datetime',
            'status_changed_at' => 'datetime',
        ];
    }

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (self $application): void {
            $application->status ??= ApplicationStatus::New;
        });
        static::saving(function (self $application): void {
            $application->setAttribute('phone_digits', preg_replace('/\D+/', '', $application->phone) ?: null);
        });
    }

    /** @return BelongsTo<JobPosting, $this> */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function plantLabel(): string
    {
        return $this->plant?->label() ?? 'Any plant';
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * Moves to $status with a conditional UPDATE, so a double click logs the change once.
     */
    public function claimStatus(ApplicationStatus $status): bool
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

    public function isWaiting(): bool
    {
        return in_array($this->status, [ApplicationStatus::New, ApplicationStatus::Screening], true)
            && ($this->status_changed_at ?? $this->created_at)->lt(now()->subDays(self::REVIEW_DAYS));
    }

    /** @param Builder<self> $query */
    public function scopeWaiting(Builder $query): void
    {
        // New or in HR screening, with no movement for REVIEW_DAYS.
        $cutoff = now()->subDays(self::REVIEW_DAYS);
        $query->whereIn('status', [ApplicationStatus::New, ApplicationStatus::Screening])
            ->where(fn (Builder $q) => $q->where('status_changed_at', '<', $cutoff)->orWhere(fn (Builder $q) => $q->whereNull('status_changed_at')->where('created_at', '<', $cutoff)));
    }

    /**
     * @param  Builder<self>  $query
     * @param  array<string, mixed>  $f
     */
    public function scopeFilter(Builder $query, array $f): void
    {
        $query
            ->when($f['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($f['category'] ?? null, fn (Builder $q, $v) => $q->where('category', $v))
            ->when($f['plant'] ?? null, fn (Builder $q, $v) => $v === 'any' ? $q->whereNull('plant') : $q->where('plant', $v))
            ->when($f['reviewer'] ?? null, fn (Builder $q, $v) => $q->where('reviewer_id', $v))
            ->when($f['job'] ?? null, fn (Builder $q, $v) => $q->where('job_posting_id', $v))
            ->when($f['waiting'] ?? null, fn (Builder $q) => $q->waiting())
            ->when($f['q'] ?? null, function (Builder $q, $term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $term).'%';
                $digits = preg_replace('/\D+/', '', (string) $term);
                $q->where(function (Builder $w) use ($like, $digits) {
                    $w->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like)
                        ->orWhere('reference', 'like', $like)->orWhere('email', 'like', $like);
                    if (strlen((string) $digits) >= 3) {
                        $w->orWhere('phone_digits', 'like', '%'.$digits.'%');
                    }
                });
            });
    }

    public function position(): string
    {
        return $this->jobPosting->title ?? $this->category->label().' (general application)';
    }

    public function referenceNumber(): string
    {
        return $this->reference;
    }

    public function kindLabel(): string
    {
        return 'Job application';
    }

    public function area(): Area
    {
        return Area::Careers;
    }

    public function senderName(): string
    {
        return $this->full_name;
    }

    public function senderEmail(): ?string
    {
        return $this->email;
    }

    public function summary(): array
    {
        return array_filter([
            'Applicant' => $this->full_name,
            'Position' => $this->position(),
            'Plant' => $this->plantLabel(),
            'Experience' => $this->experience->label(),
            'Licences' => (string) $this->licences,
            'Lives in' => (string) $this->home_area,
            'Right to work' => $this->right_to_work ? 'Yes' : 'Not confirmed',
            'Phone' => format_phone($this->phone),
            'CV attached' => $this->cv_path ? 'Yes' : 'No',
        ]);
    }

    public function adminUrl(): string
    {
        return route('admin.applications.show', $this);
    }

    public function nextSteps(): string
    {
        return 'Our HR team reads every application within three working days and passes suitable ones to the plant manager. If you are a match, we will call you on the number you gave us.';
    }
}
