<?php

namespace App\Enums;

/** The two sales teams. Quotes for Ireland (north and south) go to Ireland; Great Britain to the UK team. */
enum Region: string
{
    case Ireland = 'ireland';
    case Uk = 'uk';

    public function label(): string
    {
        return match ($this) {
            self::Ireland => 'Ireland',
            self::Uk => 'UK',
        };
    }

    public function team(): string
    {
        return match ($this) {
            self::Ireland => 'Ireland sales',
            self::Uk => 'UK sales',
        };
    }
}
