<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Tells the patient their request was received and is awaiting confirmation. */
class AppointmentReceived extends Notification implements ShouldQueue
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
            ->subject("We received your appointment request ({$a->reference})")
            ->greeting("Hello {$a->patient_name},")
            ->line('Thank you for your appointment request. It is **not confirmed yet** — the clinic will contact you to confirm.')
            ->line("**Reference:** {$a->reference}")
            ->line("**Requested time:** {$a->appointment_date->format('l, j M Y')}, {$a->timeRange()} (Bangladesh time)")
            ->line("**Visit type:** {$a->type->name}")
            ->line("**Treatment:** {$a->treatmentName()}")
            ->line('If you need to change anything, please call '.setting('phone_primary').'.');
    }
}
