<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Before/after case. Only shown publicly when active AND patient consent is recorded. */
class SmileCase extends Model
{
    use Publishable, SoftDeletes;

    protected $fillable = [
        'treatment_id', 'title', 'description', 'before_image', 'after_image', 'duration',
        'consent_confirmed', 'consent_date', 'consent_note', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'consent_confirmed' => 'boolean',
            'consent_date' => 'date',
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
