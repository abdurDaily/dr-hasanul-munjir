<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentActivity extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['appointment_id', 'user_id', 'action', 'from_status', 'to_status', 'note', 'meta'];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
