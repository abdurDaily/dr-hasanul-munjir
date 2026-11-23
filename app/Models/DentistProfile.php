<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The clinic's dentist. A single row; use DentistProfile::current(). */
class DentistProfile extends Model
{
    protected $fillable = [
        'full_name', 'credentials_line', 'role_line', 'specialty_line', 'bio', 'qualifications_summary',
        'expertise_summary', 'philosophy_quote', 'bmdc_reg_no', 'years_experience', 'specializations',
        'portrait_image', 'hero_image', 'about_image', 'about_image_alt', 'about_image_secondary', 'about_image_secondary_alt',
    ];

    protected function casts(): array
    {
        return ['specializations' => 'array', 'years_experience' => 'integer'];
    }

    public static function current(): self
    {
        return static::query()->first() ?? new static(['full_name' => config('app.name')]);
    }
}
