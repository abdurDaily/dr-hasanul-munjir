<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnavailableDate extends Model
{
    public const TYPES = ['holiday' => 'Holiday', 'leave' => 'Leave', 'closed' => 'Clinic closed'];

    protected $fillable = ['starts_on', 'ends_on', 'type', 'reason'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date'];
    }
}
