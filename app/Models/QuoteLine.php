<?php

namespace App\Models;

use App\Enums\QuoteUnit;
use App\Enums\Treatment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of a trade quote request: product, size, treatment, quantity and where it goes.
 *
 * @property int $id
 * @property int $quote_request_id
 * @property int $position
 * @property string $product
 * @property string|null $description
 * @property int|null $thickness_mm
 * @property int|null $width_mm
 * @property string|null $length_m
 * @property Treatment $treatment
 * @property string $quantity
 * @property QuoteUnit $unit
 * @property string|null $delivery_site
 * @property-read QuoteRequest $quoteRequest
 */
class QuoteLine extends Model
{
    protected $fillable = ['position', 'product', 'description', 'thickness_mm', 'width_mm', 'length_m', 'treatment', 'quantity', 'unit', 'delivery_site'];

    #[\Override]
    protected function casts(): array
    {
        return ['treatment' => Treatment::class, 'unit' => QuoteUnit::class, 'thickness_mm' => 'integer', 'width_mm' => 'integer', 'position' => 'integer'];
    }

    /** @return BelongsTo<QuoteRequest, $this> */
    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function productLabel(): string
    {
        return (string) (gb('quote_products.'.$this->product.'.label') ?? $this->product);
    }

    /** "47 × 150 × 4.8 m", or what was given of it. */
    public function size(): string
    {
        $parts = array_filter([$this->thickness_mm, $this->width_mm]);
        $size = implode(' × ', $parts);
        if ($this->length_m !== null) {
            $size .= ($size !== '' ? ' × ' : '').plain_number((float) $this->length_m).' m';
        }

        return $size !== '' ? $size : '–';
    }

    public function quantityLabel(): string
    {
        return plain_number((float) $this->quantity).' '.$this->unit->label();
    }

    /** One line for emails and CSV: "CLS 38 × 140 × 4.8 m, KD, 12 packs". */
    public function oneLine(): string
    {
        return trim($this->productLabel().($this->description ? ' ('.$this->description.')' : '').' '.($this->size() !== '–' ? $this->size() : '')).', '.$this->treatment->short().', '.$this->quantityLabel();
    }
}
