<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentImage extends Model
{
    /** Images per treatment page, and per save (keeps uploads and the page reasonable). */
    public const MAX_PER_TREATMENT = 24;

    public const MAX_PER_UPLOAD = 12;

    protected $fillable = ['treatment_id', 'image', 'caption', 'alt', 'sort_order'];

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }
}
