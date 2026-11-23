<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleBreak extends Model
{
    protected $fillable = ['weekday', 'starts_at', 'ends_at', 'label'];

    protected function casts(): array
    {
        return ['weekday' => 'integer'];
    }
}
