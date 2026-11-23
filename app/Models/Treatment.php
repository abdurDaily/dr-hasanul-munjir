<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use HasFactory, HasSlug, Publishable, SoftDeletes;

    protected $fillable = [
        'treatment_category_id', 'name', 'slug', 'short_description', 'description', 'image', 'image_alt',
        'icon', 'duration', 'sort_order', 'is_featured', 'is_speciality', 'is_active', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_speciality' => 'boolean', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TreatmentCategory::class, 'treatment_category_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(TreatmentImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function smileCases(): HasMany
    {
        return $this->hasMany(SmileCase::class);
    }
}
