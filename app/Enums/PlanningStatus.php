<?php

namespace App\Enums;

enum PlanningStatus: string
{
    case NotApplied = 'not_applied';
    case Applied = 'applied';
    case Granted = 'granted';
    case Commenced = 'commenced';

    public function label(): string
    {
        return match ($this) {
            self::NotApplied => 'Not applied for yet',
            self::Applied => 'Applied, waiting for a decision',
            self::Granted => 'Granted',
            self::Commenced => 'Granted, commencement notice lodged',
        };
    }
}
