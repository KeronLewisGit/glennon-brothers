<?php

namespace App\Enums;

enum EnquiryTopic: string
{
    case Sales = 'sales';
    case Stockist = 'stockist';
    case Logs = 'logs';
    case Media = 'media';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Sales => 'Products, prices or an existing order',
            self::Stockist => 'Becoming a stockist of Glennon Brothers products',
            self::Logs => 'Selling round timber or forestry',
            self::Media => 'Media, community or sponsorship',
            self::Other => 'Something else',
        };
    }
}
