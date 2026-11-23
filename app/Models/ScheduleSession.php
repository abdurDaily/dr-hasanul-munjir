<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleSession extends Model
{
    /** Saturday-first, matching the public calendar. Keys are Carbon dayOfWeek numbers. */
    public const WEEKDAYS = [6 => 'Saturday', 0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];

    protected $fillable = ['weekday', 'name', 'starts_at', 'ends_at', 'is_active'];

    protected function casts(): array
    {
        return ['weekday' => 'integer', 'is_active' => 'boolean'];
    }
}
