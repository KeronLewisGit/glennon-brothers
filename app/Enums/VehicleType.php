<?php

namespace App\Enums;

enum VehicleType: string
{
    case Flatbed = 'flatbed';
    case Curtainsider = 'curtainsider';
    case Timber = 'timber';
    case Rigid = 'rigid';
    case Walking = 'walking_floor';

    public function label(): string
    {
        return match ($this) {
            self::Flatbed => 'Artic, flatbed',
            self::Curtainsider => 'Artic, curtainsider',
            self::Timber => 'Timber lorry with crane',
            self::Rigid => 'Rigid lorry',
            self::Walking => 'Walking-floor trailer',
        };
    }
}
