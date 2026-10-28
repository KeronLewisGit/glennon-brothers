<?php

namespace App\Enums;

/** A part of the back office. Role::areas() says who works where; policies and the nav read it. */
enum Area: string
{
    case Quotes = 'quotes';
    case Frame = 'frame';
    case Careers = 'careers';
    case Deliveries = 'deliveries';
    case Enquiries = 'enquiries';

    public function label(): string
    {
        return match ($this) {
            self::Quotes => 'Trade quotes',
            self::Frame => 'Timber frame',
            self::Careers => 'Careers',
            self::Deliveries => 'Haulier bookings',
            self::Enquiries => 'Enquiries',
        };
    }

    /** Which GB_NOTIFY_* inbox hears about new submissions in this area. */
    public function inbox(): string
    {
        return $this->value;
    }
}
