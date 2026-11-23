<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Alerts clinic staff that a new booking request is waiting for confirmation. */
class AppointmentRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $a = $this->appointment->loadMissing('type', 'treatment');

        return (new MailMessage)
            ->subject("New appointment request {$a->reference}")
            ->greeting('New appointment request')
            ->line("**{$a->patient_name}** ({$a->patient_phone}) requested an appointment.")
            ->line("**When:** {$a->appointment_date->format('l, j M Y')}, {$a->timeRange()}")
            ->line("**Type:** {$a->type->name} · **Treatment:** {$a->treatmentName()}")
            ->action('Review request', route('admin.appointments.show', $a))
            ->line('The request stays pending until a staff member confirms it.');
    }
}
