<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    use HasFactory, Notifiable;

    /** Only BookingService writes appointments; nothing here is mass-assigned from requests directly. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'status' => AppointmentStatus::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class, 'appointment_type_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(AppointmentActivity::class)->latest('id');
    }

    public function scopeHoldingSlot(Builder $query): Builder
    {
        return $query->whereIn('status', AppointmentStatus::slotHoldingValues());
    }

    public function startsAt(): Carbon
    {
        return Carbon::parse($this->appointment_date->format('Y-m-d').' '.$this->start_time);
    }

    public function timeRange(): string
    {
        return Carbon::parse($this->start_time)->format('g:i A').' – '.Carbon::parse($this->end_time)->format('g:i A');
    }

    public function treatmentName(): string
    {
        return $this->treatment?->name ?? 'Consultation (not sure)';
    }

    /** Mail notifications go to the patient's email, if they gave one. */
    public function routeNotificationForMail(): ?string
    {
        return $this->patient_email;
    }

    public static function slotKey(string $date, string $time): string
    {
        return $date.' '.substr($time, 0, 5);
    }
}
