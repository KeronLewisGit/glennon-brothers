<?php

namespace App\Enums;

enum QuoteUnit: string
{
    case CubicMetres = 'm3';
    case Pieces = 'pcs';
    case Packs = 'packs';
    case LinearMetres = 'lm';
    case Tonnes = 't';

    public function label(): string
    {
        return match ($this) {
            self::CubicMetres => 'm³',
            self::Pieces => 'pieces',
            self::Packs => 'packs',
            self::LinearMetres => 'linear metres',
            self::Tonnes => 'tonnes',
        };
    }
}
