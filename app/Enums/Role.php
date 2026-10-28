<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Sales = 'sales';
    case Hr = 'hr';
    case Frame = 'frame';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Sales => 'Sales',
            self::Hr => 'Human resources',
            self::Frame => 'Timber frame (DTE)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Everything, including staff accounts.',
            self::Sales => 'Trade quote requests for their region, haulier bookings and website enquiries.',
            self::Hr => 'Job postings, applications and CVs for every plant.',
            self::Frame => 'DTE timber frame enquiries and the drawings sent with them.',
        };
    }

    /** Sales staff belong to the Ireland or the UK team; quotes are routed by region. */
    public function hasRegion(): bool
    {
        return $this === self::Sales;
    }

    /**
     * Back-office areas this role works in.
     *
     * @return list<Area>
     */
    public function areas(): array
    {
        return match ($this) {
            self::Admin => Area::cases(),
            self::Sales => [Area::Quotes, Area::Deliveries, Area::Enquiries],
            self::Hr => [Area::Careers],
            self::Frame => [Area::Frame],
        };
    }
}
