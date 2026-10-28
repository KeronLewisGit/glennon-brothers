<?php

namespace App\Enums;

/** Reviewer stages: HR screens, the plant manager reviews, then interview and offer. */
enum ApplicationStatus: string
{
    case New = 'new';
    case Screening = 'screening';
    case ManagerReview = 'manager_review';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Screening => 'HR screening',
            self::ManagerReview => 'Plant manager review',
            self::Interview => 'Interview',
            self::Offer => 'Offer made',
            self::Hired => 'Hired',
            self::Declined => 'Not this time',
        };
    }

    /** Stages where somebody has to act; these have a reviewer. */
    public function isOpen(): bool
    {
        return ! in_array($this, [self::Hired, self::Declined], true);
    }

    /** @return list<self> */
    public static function stages(): array
    {
        return [self::New, self::Screening, self::ManagerReview, self::Interview, self::Offer];
    }

    public function tone(): string
    {
        return match ($this) {
            self::New => 'attention',
            self::Hired, self::Offer => 'good',
            self::Declined => 'muted',
            default => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'dot',
            self::Screening => 'eye',
            self::ManagerReview => 'user',
            self::Interview => 'calendar',
            self::Offer => 'mail',
            self::Hired => 'check-circle',
            self::Declined => 'x',
        };
    }
}
