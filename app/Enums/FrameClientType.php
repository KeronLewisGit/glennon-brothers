<?php

namespace App\Enums;

enum FrameClientType: string
{
    case SelfBuilder = 'self_builder';
    case Developer = 'developer';
    case Contractor = 'contractor';
    case Architect = 'architect';

    public function label(): string
    {
        return match ($this) {
            self::SelfBuilder => 'Self-builder (my own home)',
            self::Developer => 'Developer',
            self::Contractor => 'Building contractor',
            self::Architect => 'Architect or engineer',
        };
    }
}
