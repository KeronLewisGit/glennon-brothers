<?php

namespace App\Enums;

/** Where a trade order is delivered. Sets the sales region and the quote currency. */
enum Country: string
{
    case Ireland = 'IE';
    case NorthernIreland = 'NI';
    case Scotland = 'SCT';
    case England = 'ENG';
    case Wales = 'WLS';

    public function label(): string
    {
        return match ($this) {
            self::Ireland => 'Republic of Ireland',
            self::NorthernIreland => 'Northern Ireland',
            self::Scotland => 'Scotland',
            self::England => 'England',
            self::Wales => 'Wales',
        };
    }

    public function region(): Region
    {
        return in_array($this, [self::Ireland, self::NorthernIreland], true) ? Region::Ireland : Region::Uk;
    }

    /** Euro for the Republic, sterling everywhere else (Northern Ireland included). */
    public function currency(): string
    {
        return $this === self::Ireland ? 'EUR' : 'GBP';
    }

    /** "Eircode" in the Republic, "Postcode" elsewhere. */
    public function postcodeLabel(): string
    {
        return $this === self::Ireland ? 'Eircode' : 'Postcode';
    }
}
