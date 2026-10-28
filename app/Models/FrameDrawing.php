<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A drawing or plan sent with a timber frame enquiry. Private disk only; streamed to DTE staff.
 *
 * @property int $id
 * @property int $frame_enquiry_id
 * @property string $path
 * @property string $original_name
 * @property string|null $mime
 * @property int $size
 * @property string $added_by
 * @property Carbon $created_at
 * @property-read FrameEnquiry $enquiry
 */
class FrameDrawing extends Model
{
    protected $fillable = ['path', 'original_name', 'mime', 'size', 'added_by'];

    /** @return BelongsTo<FrameEnquiry, $this> */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(FrameEnquiry::class, 'frame_enquiry_id');
    }
}
