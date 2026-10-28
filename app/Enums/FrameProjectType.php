<?php

namespace App\Enums;

enum FrameProjectType: string
{
    case House = 'house';
    case Scheme = 'scheme';
    case Extension = 'extension';
    case Trusses = 'trusses';
    case Commercial = 'commercial';

    public function label(): string
    {
        return match ($this) {
            self::House => 'One house: timber frame',
            self::Scheme => 'Several homes: timber frame',
            self::Extension => 'Extension',
            self::Trusses => 'Roof trusses only',
            self::Commercial => 'Commercial building',
        };
    }

    public function hasUnits(): bool
    {
        return $this === self::Scheme;
    }
}
