<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\BlockedSlot;
use App\Models\ScheduleBreak;
use App\Models\UnavailableDate;
use App\Notifications\AppointmentReceived;
use App\Notifications\AppointmentRequested;
use App\Services\Settings;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasics();
        $this->travelToDhaka();
        Notification::fake();
        app(Settings::class)->set(['notify_emails' => 'clinic@example.com']);
    }

    public function test_availability_lists_the_chamber_sessions_as_slots(): void
    {
        $res = $this->getJson('/booking/availability')->assertOk()->assertHeader('Cache-Control', 'no-store, private');

        $day = $res->json('days.2026-11-25');
        $this->assertSame(['Morning', 'Evening'], array_column($day, 'name'));
        $this->assertSame('10:30', $day[0]['slots'][0]['time']);
        $this->assertSame('13:30', end($day[0]['slots'])['time']); // 13:30–14:00 is the last morning slot
        $this->assertSame('21:30', end($day[1]['slots'])['time']);
        $this->assertSame('2026-11-23', $res->json('today'));
        $this->assertSame('2027-01-22', $res->json('last')); // 60-day window
    }

    public function test_a_patient_can_request_an_appointment_and_it_starts_pending(): void
    {
        $res = $this->postJson('/appointments', $this->bookingPayload())->assertCreated();

        $appointment = Appointment::sole();
        $this->assertSame($res->json('reference'), $appointment->reference);
        $this->assertMatchesRegularExpression('/^MDC-\d{6}-[A-Z0-9]{4}$/', $appointment->reference);
        $this->assertSame(AppointmentStatus::Pending, $appointment->status);
        $this->assertSame('01711223344', $appointment->patient_phone); // normalised
        $this->assertSame('11:00:00', $appointment->end_time);
        $this->assertSame('2026-11-25 10:30', $appointment->slot_key);
        $this->assertDatabaseHas('appointment_activities', ['appointment_id' => $appointment->id, 'action' => 'created']);

        Notification::assertSentTo(new AnonymousNotifiable, AppointmentRequested::class);
        Notification::assertSentTo($appointment, AppointmentReceived::class);
    }

    public function test_a_booked_slot_is_no_longer_offered_or_bookable(): void
    {
        $this->postJson('/appointments', $this->bookingPayload())->assertCreated();

        $slots = $this->getJson('/booking/availability')->json('days.2026-11-25.0.slots');
        $this->assertFalse(collect($slots)->firstWhere('time', '10:30')['available']);

        // Even with a different visit type the doctor is busy: one shared slot pool
        $this->postJson('/appointments', $this->bookingPayload(['patient_name' => 'Someone Else', 'appointment_type_id' => AppointmentType::query()->orderByDesc('id')->value('id')]))
            ->assertStatus(409)->assertJsonPath('errors.time.0', fn ($m) => str_contains($m, 'no longer available'));
        $this->assertSame(1, Appointment::count());
    }

    public function test_the_database_rejects_two_appointments_holding_the_same_slot(): void
    {
        $this->postJson('/appointments', $this->bookingPayload())->assertCreated();
        $first = Appointment::sole();

        // Simulates a concurrent request that slipped past the application check
        $this->expectException(UniqueConstraintViolationException::class);
        Appointment::create([...$first->only(['appointment_type_id', 'appointment_date', 'start_time', 'end_time', 'slot_key', 'patient_name', 'patient_phone']), 'reference' => 'MDC-DUP', 'status' => 'pending']);
    }

    public function test_slots_outside_chamber_hours_are_rejected(): void
    {
        $this->postJson('/appointments', $this->bookingPayload(['time' => '15:00']))->assertStatus(409);
        $this->postJson('/appointments', $this->bookingPayload(['time' => '10:45']))->assertStatus(409); // not on the 30-min grid
        $this->assertSame(0, Appointment::count());
    }

    public function test_past_dates_too_soon_and_too_far_ahead_are_rejected(): void
    {
        $this->postJson('/appointments', $this->bookingPayload(['date' => '2026-11-22']))->assertUnprocessable()->assertJsonValidationErrors('date');

        // Now is 09:00; with 30 min notice 10:30 today is fine but nothing earlier exists anyway — move the clock
        $this->travelToDhaka('2026-11-23 10:15:00');
        $this->postJson('/appointments', $this->bookingPayload(['date' => '2026-11-23', 'time' => '10:30']))->assertStatus(409);
        $this->postJson('/appointments', $this->bookingPayload(['date' => '2026-11-23', 'time' => '11:00']))->assertCreated();

        $this->postJson('/appointments', $this->bookingPayload(['date' => '2027-02-01']))->assertStatus(409); // beyond 60 days
    }

    public function test_holidays_breaks_and_blocked_slots_remove_availability(): void
    {
        UnavailableDate::create(['starts_on' => '2026-11-25', 'ends_on' => '2026-11-26', 'type' => 'holiday', 'reason' => 'Test']);
        ScheduleBreak::create(['weekday' => null, 'starts_at' => '12:00', 'ends_at' => '12:30']);
        BlockedSlot::create(['date' => '2026-11-27', 'starts_at' => '18:00', 'ends_at' => '19:00']);

        $days = $this->getJson('/booking/availability')->json('days');
        $this->assertSame([], $days['2026-11-25']);
        $this->assertSame([], $days['2026-11-26']);
        $this->assertNull(collect($days['2026-11-27'][0]['slots'])->firstWhere('time', '12:00')); // break is not a slot
        $this->assertFalse(collect($days['2026-11-27'][1]['slots'])->firstWhere('time', '18:30')['available']);

        $this->postJson('/appointments', $this->bookingPayload(['date' => '2026-11-25']))->assertStatus(409);
        $this->postJson('/appointments', $this->bookingPayload(['date' => '2026-11-27', 'time' => '12:00']))->assertStatus(409);
        $this->postJson('/appointments', $this->bookingPayload(['date' => '2026-11-27', 'time' => '18:00']))->assertStatus(409);
    }

    public function test_patient_input_is_validated(): void
    {
        $this->postJson('/appointments', $this->bookingPayload([
            'patient_name' => '', 'patient_phone' => '12345', 'patient_email' => 'not-an-email', 'treatment_id' => null, 'time' => '9am',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['patient_name', 'patient_phone', 'patient_email', 'treatment_id', 'time']);

        $this->assertSame(0, Appointment::count());
    }

    public function test_email_is_optional_and_consultation_needs_no_treatment(): void
    {
        $this->postJson('/appointments', $this->bookingPayload(['patient_email' => '', 'treatment_id' => 'consultation']))->assertCreated();

        $a = Appointment::sole();
        $this->assertNull($a->patient_email);
        $this->assertNull($a->treatment_id);
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->postJson('/appointments', $this->bookingPayload(['website' => 'http://spam.example']))->assertUnprocessable();
        $this->assertSame(0, Appointment::count());
    }

    public function test_booking_requests_are_rate_limited(): void
    {
        foreach (['10:30', '11:00', '11:30', '12:00', '12:30'] as $time) {
            $this->postJson('/appointments', $this->bookingPayload(['time' => $time]))->assertCreated();
        }
        $this->postJson('/appointments', $this->bookingPayload(['time' => '13:00']))->assertTooManyRequests();
    }
}
