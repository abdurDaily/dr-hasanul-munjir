<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicPhoto extends Model
{
    use Publishable, SoftDeletes;

    protected $fillable = [
        'gallery_category_id', 'image', 'title', 'subtitle', 'alt',
        'show_in_clinic', 'show_in_slider', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['show_in_clinic' => 'boolean', 'show_in_slider' => 'boolean', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }
}
