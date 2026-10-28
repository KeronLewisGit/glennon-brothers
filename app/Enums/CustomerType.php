<?php

namespace App\Enums;

enum CustomerType: string
{
    case Merchant = 'merchant';
    case Fencing = 'fencing';
    case Pallet = 'pallet';
    case FrameMaker = 'frame_maker';
    case Contractor = 'contractor';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Merchant => 'Builders\' merchant or provider',
            self::Fencing => 'Fencing contractor or landscaper',
            self::Pallet => 'Pallet or packaging manufacturer',
            self::FrameMaker => 'Timber frame or truss manufacturer',
            self::Contractor => 'Building contractor',
            self::Other => 'Other trade buyer',
        };
    }
}
