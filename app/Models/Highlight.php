<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

/** "Why choose me" reasons on the homepage. */
class Highlight extends Model
{
    use Publishable;

    protected $fillable = ['icon', 'title', 'text', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
