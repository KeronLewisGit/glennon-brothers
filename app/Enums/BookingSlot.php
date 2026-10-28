<?php

namespace App\Enums;

enum BookingSlot: string
{
    case Early = '0600';
    case Morning = '0800';
    case Midday = '1100';
    case Afternoon = '1400';

    public function label(): string
    {
        return match ($this) {
            self::Early => '06:00 – 08:00',
            self::Morning => '08:00 – 11:00',
            self::Midday => '11:00 – 14:00',
            self::Afternoon => '14:00 – 17:00',
        };
    }
}
