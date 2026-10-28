<?php

namespace App\Enums;

enum BookingPurpose: string
{
    case Collect = 'collect';
    case Logs = 'logs';
    case Residues = 'residues';

    public function label(): string
    {
        return match ($this) {
            self::Collect => 'Collecting sawn timber or fencing',
            self::Logs => 'Delivering round logs',
            self::Residues => 'Collecting chip, sawdust, bark or shavings',
        };
    }
}
