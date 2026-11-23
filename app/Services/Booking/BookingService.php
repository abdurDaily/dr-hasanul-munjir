<?php

namespace App\Services\Booking;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentReceived;
use App\Notifications\AppointmentRequested;
use App\Notifications\AppointmentUpdated;
use App\Services\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

/**
 * The only code that creates or changes appointments.
 *
 * Double-booking protection, in layers:
 *  1. Availability is re-checked on the server inside the transaction.
 *  2. The day's appointment rows (and the index gap) are locked FOR UPDATE, so concurrent
 *     bookings for the same date are serialised.
 *  3. A unique `slot_key` index makes a duplicate start time impossible at the database level.
 */
class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly Settings $settings,
    ) {}

    /**
     * @param  array{appointment_type_id:int, treatment_id:?int, date:string, time:string, patient_name:string,
     *               patient_phone:string, patient_email:?string, message:?string, ip_address?:?string}  $data
     */
    public function book(array $data, ?User $staff = null): Appointment
    {
        $date = CarbonImmutable::parse($data['date'])->startOfDay();
        $time = substr($data['time'], 0, 5);

        $appointment = $this->withSlotLock($date, function () use ($data, $date, $time, $staff) {
            if (! $this->availability->isBookable($date, $time, null, $staff !== null)) {
                throw new SlotUnavailableException;
            }

            $appointment = Appointment::create([
                'reference' => $this->newReference(),
                'appointment_type_id' => $data['appointment_type_id'],
                'treatment_id' => $data['treatment_id'] ?? null,
                'appointment_date' => $date->toDateString(),
                'start_time' => $time,
                'end_time' => $this->endTime($time),
                'slot_key' => Appointment::slotKey($date->toDateString(), $time),
                'status' => AppointmentStatus::Pending,
                'patient_name' => $data['patient_name'],
                'patient_phone' => $data['patient_phone'],
                'patient_email' => $data['patient_email'] ?? null,
                'message' => $data['message'] ?? null,
                'source' => $staff ? 'admin' : 'website',
                'ip_address' => $data['ip_address'] ?? null,
                'handled_by' => $staff?->id,
            ]);
            $this->log($appointment, $staff, 'created', null, AppointmentStatus::Pending, $staff ? 'Added by staff' : 'Requested online');

            return $appointment;
        });

        $this->notify(function () use ($appointment) {
            if ($emails = $this->clinicEmails()) {
                Notification::route('mail', $emails)->notify(new AppointmentRequested($appointment));
            }
            if ($appointment->patient_email) {
                $appointment->notify(new AppointmentReceived($appointment));
            }
        });

        return $appointment;
    }

    public function transition(Appointment $appointment, AppointmentStatus $to, User $staff, ?string $note = null): Appointment
    {
        $appointment = DB::transaction(function () use ($appointment, $to, $staff, $note) {
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
            $from = $appointment->status;
            if (! $from->canTransitionTo($to)) {
                throw new InvalidArgumentException("An appointment that is {$from->label()} cannot be marked {$to->label()}.");
            }

            $appointment->status = $to;
            $appointment->handled_by = $staff->id;
            if (! $to->holdsSlot()) {
                $appointment->slot_key = null; // frees the slot for other patients
            }
            match ($to) {
                AppointmentStatus::Confirmed => $appointment->confirmed_at = now(),
                AppointmentStatus::Completed => $appointment->completed_at = now(),
                AppointmentStatus::Cancelled, AppointmentStatus::Rejected => $appointment->cancelled_at = now(),
                default => null,
            };
            $appointment->save();
            $this->log($appointment, $staff, $to->value, $from, $to, $note);

            return $appointment;
        });

        if (in_array($to, [AppointmentStatus::Confirmed, AppointmentStatus::Cancelled, AppointmentStatus::Rejected], true)) {
            $this->notifyPatient($appointment, $to->value, $note);
        }

        return $appointment;
    }

    public function reschedule(Appointment $appointment, string $date, string $time, User $staff, ?string $note = null): Appointment
    {
        if (! $appointment->status->isOpen()) {
            throw new InvalidArgumentException('Only pending or confirmed appointments can be rescheduled.');
        }
        $newDate = CarbonImmutable::parse($date)->startOfDay();
        $time = substr($time, 0, 5);
        $old = ['date' => $appointment->appointment_date->toDateString(), 'time' => substr($appointment->start_time, 0, 5)];

        $appointment = $this->withSlotLock($newDate, function () use ($appointment, $newDate, $time, $staff, $note, $old) {
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
            if (! $this->availability->isBookable($newDate, $time, $appointment->id, true)) {
                throw new SlotUnavailableException;
            }
            $appointment->update([
                'appointment_date' => $newDate->toDateString(),
                'start_time' => $time,
                'end_time' => $this->endTime($time),
                'slot_key' => Appointment::slotKey($newDate->toDateString(), $time),
                'handled_by' => $staff->id,
            ]);
            $this->log($appointment, $staff, 'rescheduled', $appointment->status, $appointment->status, $note, [
                'from' => $old, 'to' => ['date' => $newDate->toDateString(), 'time' => $time],
            ]);

            return $appointment;
        });

        $this->notifyPatient($appointment, 'rescheduled', $note);

        return $appointment;
    }

    public function addNote(Appointment $appointment, User $staff, string $note): void
    {
        $this->log($appointment, $staff, 'note', null, null, $note);
    }

    /** Runs $callback in a transaction holding a lock on the date's appointments. */
    private function withSlotLock(CarbonImmutable $date, callable $callback): Appointment
    {
        try {
            return DB::transaction(function () use ($date, $callback) {
                // Locks existing rows for the date and, on InnoDB, the index gap — concurrent inserts for this
                // date wait here until we commit, then see our booking when they re-check availability.
                Appointment::query()->whereDate('appointment_date', $date)->lockForUpdate()->get(['id']);

                return $callback();
            }, attempts: 3);
        } catch (UniqueConstraintViolationException) {
            throw new SlotUnavailableException;
        }
    }

    private function endTime(string $time): string
    {
        return AvailabilityService::format(AvailabilityService::minutes($time) + $this->availability->slotMinutes());
    }

    private function newReference(): string
    {
        do {
            $suffix = strtr(Str::upper(Str::random(4)), ['0' => '7', 'O' => 'Q', '1' => '8', 'I' => 'K', 'L' => 'M']); // no look-alikes
            $ref = 'MDC-'.now()->format('ymd').'-'.$suffix;
        } while (Appointment::query()->where('reference', $ref)->exists());

        return $ref;
    }

    private function log(Appointment $appointment, ?User $user, string $action, ?AppointmentStatus $from, ?AppointmentStatus $to, ?string $note = null, ?array $meta = null): void
    {
        $appointment->activities()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'from_status' => $from?->value,
            'to_status' => $to?->value,
            'note' => $note,
            'meta' => $meta,
        ]);
    }

    private function notifyPatient(Appointment $appointment, string $event, ?string $note): void
    {
        if ($appointment->patient_email) {
            $this->notify(fn () => $appointment->notify(new AppointmentUpdated($appointment, $event, $note)));
        }
    }

    /** A notification failure must never undo or hide a booking that has been saved. */
    private function notify(callable $send): void
    {
        try {
            $send();
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function clinicEmails(): array
    {
        $list = $this->settings->get('notify_emails') ?: config('services.clinic.notify_emails');

        return array_values(array_filter(array_map('trim', explode(',', (string) $list)), fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
    }
}
