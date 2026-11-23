<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqCategory extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }
}
