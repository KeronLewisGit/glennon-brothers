<?php

namespace App\Enums;

/** How the timber is supplied, in the group's own terms (products and processes pages). */
enum Treatment: string
{
    case Green = 'green';
    case KilnDried = 'kd';
    case Treated = 'treated';
    case HeatTreated = 'ht';
    case Any = 'any';

    public function label(): string
    {
        return match ($this) {
            self::Green => 'Green (unseasoned)',
            self::KilnDried => 'Kiln dried',
            self::Treated => 'Pressure treated',
            self::HeatTreated => 'Heat treated (ISPM 15)',
            self::Any => 'Not sure / advise me',
        };
    }

    public function short(): string
    {
        return match ($this) {
            self::Green => 'Green',
            self::KilnDried => 'KD',
            self::Treated => 'Treated',
            self::HeatTreated => 'HT',
            self::Any => 'Advise',
        };
    }
}
