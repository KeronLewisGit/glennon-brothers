<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case New = 'new';
    case Pricing = 'pricing';
    case Quoted = 'quoted';
    case Won = 'won';
    case Lost = 'lost';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Pricing => 'Pricing',
            self::Quoted => 'Quote sent',
            self::Won => 'Order won',
            self::Lost => 'Lost',
            self::Declined => 'Not quoted',
        };
    }

    /** Still needs a first response from sales. */
    public function awaitsResponse(): bool
    {
        return in_array($this, [self::New, self::Pricing], true);
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::New, self::Pricing, self::Quoted], true);
    }

    /** @return list<self> */
    public static function open(): array
    {
        return [self::New, self::Pricing, self::Quoted];
    }

    public function tone(): string
    {
        return match ($this) {
            self::New => 'attention',
            self::Won => 'good',
            self::Lost, self::Declined => 'muted',
            default => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'dot',
            self::Pricing => 'calculator',
            self::Quoted => 'mail',
            self::Won => 'check-circle',
            self::Lost, self::Declined => 'x',
        };
    }
}
