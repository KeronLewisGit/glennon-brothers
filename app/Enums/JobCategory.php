<?php

namespace App\Enums;

/** Role families. */
enum JobCategory: string
{
    case Operative = 'operative';
    case Maintenance = 'maintenance';
    case Driver = 'driver';
    case Frame = 'frame';
    case Office = 'office';

    public function label(): string
    {
        return match ($this) {
            self::Operative => 'Mill and yard operatives',
            self::Maintenance => 'Maintenance and electrical',
            self::Driver => 'Drivers',
            self::Frame => 'Timber frame erectors and factory',
            self::Office => 'Sales, office and management',
        };
    }

    public function short(): string
    {
        return match ($this) {
            self::Operative => 'Operatives',
            self::Maintenance => 'Maintenance',
            self::Driver => 'Drivers',
            self::Frame => 'Timber frame',
            self::Office => 'Office',
        };
    }

    /** A CV matters for trade and office roles; operatives, drivers and erectors can apply without one. */
    public function needsCv(): bool
    {
        return in_array($this, [self::Maintenance, self::Office], true);
    }

    /** Drivers tell us their licence categories. */
    public function asksLicence(): bool
    {
        return $this === self::Driver;
    }
}
