<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Patient email when staff confirm, reschedule, cancel or reject an appointment. */
class AppointmentUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    private const SUBJECTS = [
        'confirmed' => 'Your appointment is confirmed',
        'rescheduled' => 'Your appointment has been rescheduled',
        'cancelled' => 'Your appointment has been cancelled',
        'rejected' => 'We could not accept your appointment request',
    ];

    public function __construct(public Appointment $appointment, public string $event, public ?string $note = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $a = $this->appointment->loadMissing('type');
        $mail = (new MailMessage)
            ->subject(self::SUBJECTS[$this->event]." ({$a->reference})")
            ->greeting("Hello {$a->patient_name},")
            ->line(self::SUBJECTS[$this->event].'.');

        if (in_array($this->event, ['confirmed', 'rescheduled'], true)) {
            $mail->line("**When:** {$a->appointment_date->format('l, j M Y')}, {$a->timeRange()} (Bangladesh time)")
                ->line("**Visit type:** {$a->type->name}");
        }
        if ($this->event === 'rejected' || $this->event === 'cancelled') {
            $mail->line('You are welcome to request another time on our website or call '.setting('phone_primary').'.');
        }

        return $mail->line("Reference: {$a->reference}");
    }
}
