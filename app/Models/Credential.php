<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Education, registration, experience, certification, training, workshop or achievement. */
class Credential extends Model
{
    use Publishable, SoftDeletes;

    public const TYPES = [
        'education' => 'Education',
        'registration' => 'Registration',
        'experience' => 'Experience',
        'certification' => 'Certification',
        'training' => 'Training',
        'workshop' => 'Workshop',
        'achievement' => 'Achievement',
    ];

    protected $fillable = [
        'type', 'label', 'title', 'title_emphasis', 'institution', 'location', 'description', 'completed_on',
        'date_note', 'certificate_image', 'icon', 'is_featured', 'show_on_timeline', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'completed_on' => 'date',
            'is_featured' => 'boolean',
            'show_on_timeline' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function displayLabel(): string
    {
        return $this->label ?: (self::TYPES[$this->type] ?? ucfirst($this->type));
    }
}
