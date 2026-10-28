<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Requested = 'requested';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Confirmed => 'Confirmed',
            self::Declined => 'Slot refused',
            self::Completed => 'Completed',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Requested => 'attention',
            self::Confirmed, self::Completed => 'good',
            self::Declined => 'muted',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Requested => 'dot',
            self::Confirmed => 'check-circle',
            self::Completed => 'check',
            self::Declined => 'x',
        };
    }
}
