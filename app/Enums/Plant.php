<?php

namespace App\Enums;

/** The four sawmills plus DTE, for job postings, applications and haulier bookings. Facts in config('glennon.plants'). */
enum Plant: string
{
    case Longford = 'longford';
    case Fermoy = 'fermoy';
    case Windymains = 'windymains';
    case Troon = 'troon';
    case Dte = 'dte';

    public function label(): string
    {
        return match ($this) {
            self::Dte => 'DTE, Longford',
            default => (string) gb('plants.'.$this->value.'.name'),
        };
    }

    public function region(): Region
    {
        return in_array($this, [self::Windymains, self::Troon], true) ? Region::Uk : Region::Ireland;
    }

    /**
     * Sawmills a haulier can book a slot at (DTE shares the Longford yard).
     *
     * @return list<self>
     */
    public static function mills(): array
    {
        return [self::Longford, self::Fermoy, self::Windymains, self::Troon];
    }
}
