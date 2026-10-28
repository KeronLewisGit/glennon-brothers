<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'full_time';
    case Shifts = 'shifts';
    case Fixed = 'fixed';
    case Apprenticeship = 'apprenticeship';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Full time, permanent',
            self::Shifts => 'Full time, shift work',
            self::Fixed => 'Fixed term',
            self::Apprenticeship => 'Apprenticeship',
        };
    }

    /** schema.org JobPosting employmentType */
    public function schema(): string
    {
        return match ($this) {
            self::FullTime, self::Shifts => 'FULL_TIME',
            self::Fixed => 'TEMPORARY',
            self::Apprenticeship => 'INTERN',
        };
    }
}
