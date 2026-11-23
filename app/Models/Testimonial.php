<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A genuine patient review. Shown publicly only when approved (active) with consent to publish. */
class Testimonial extends Model
{
    use Publishable, SoftDeletes;

    public const SOURCES = ['google' => 'Google', 'facebook' => 'Facebook', 'clinic' => 'In clinic'];

    protected $fillable = [
        'treatment_id', 'display_name', 'avatar', 'body', 'rating', 'reviewed_on', 'source',
        'consent_confirmed', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'float',
            'reviewed_on' => 'date',
            'consent_confirmed' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function scopePublished($query)
    {
        return $query->active()->where('consent_confirmed', true);
    }
}
