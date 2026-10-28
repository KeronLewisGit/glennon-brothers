<?php

namespace App\Enums;

enum FrameStatus: string
{
    case New = 'new';
    case DesignReview = 'design_review';
    case Quoted = 'quoted';
    case Won = 'won';
    case Lost = 'lost';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::DesignReview => 'Design review',
            self::Quoted => 'Quote sent',
            self::Won => 'Contract signed',
            self::Lost => 'Lost',
            self::Closed => 'Closed',
        };
    }

    /** The customer can still send drawings through their link. */
    public function acceptsDrawings(): bool
    {
        return in_array($this, [self::New, self::DesignReview, self::Quoted], true);
    }

    public function tone(): string
    {
        return match ($this) {
            self::New => 'attention',
            self::Won => 'good',
            self::Lost, self::Closed => 'muted',
            default => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'dot',
            self::DesignReview => 'ruler',
            self::Quoted => 'mail',
            self::Won => 'check-circle',
            self::Lost, self::Closed => 'x',
        };
    }
}
