<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Notifications\AppointmentUpdated;
use App\Services\Booking\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasics();
        $this->travelToDhaka();
        Notification::fake();
        $this->appointment = app(BookingService::class)->book([...$this->bookingPayload(), 'patient_phone' => '01711223344']);
    }

    public function test_staff_can_confirm_and_the_patient_is_notified(): void
    {
        $staff = $this->staff('Receptionist');

        $this->actingAs($staff)->patch(route('admin.appointments.status', $this->appointment), ['status' => 'confirmed'])
            ->assertRedirect()->assertSessionHas('success');

        $a = $this->appointment->fresh();
        $this->assertSame(AppointmentStatus::Confirmed, $a->status);
        $this->assertNotNull($a->confirmed_at);
        $this->assertSame($staff->id, $a->handled_by);
        $this->assertDatabaseHas('appointment_activities', ['appointment_id' => $a->id, 'action' => 'confirmed', 'user_id' => $staff->id]);
        Notification::assertSentTo($a, AppointmentUpdated::class, fn ($n) => $n->event === 'confirmed');
    }

    public function test_cancelling_frees_the_slot_for_other_patients(): void
    {
        $this->actingAs($this->staff())->patch(route('admin.appointments.status', $this->appointment), ['status' => 'cancelled'])->assertRedirect();

        $a = $this->appointment->fresh();
        $this->assertSame(AppointmentStatus::Cancelled, $a->status);
        $this->assertNull($a->slot_key);

        $this->postJson('/appointments', $this->bookingPayload(['patient_name' => 'Next Patient']))->assertCreated();
    }

    public function test_invalid_status_transitions_are_refused(): void
    {
        $staff = $this->staff();
        // Pending cannot jump straight to completed
        $this->actingAs($staff)->patch(route('admin.appointments.status', $this->appointment), ['status' => 'completed'])
            ->assertSessionHas('error');
        $this->assertSame(AppointmentStatus::Pending, $this->appointment->fresh()->status);

        $this->patch(route('admin.appointments.status', $this->appointment), ['status' => 'rejected'])->assertSessionHas('success');
        $this->patch(route('admin.appointments.status', $this->appointment), ['status' => 'confirmed'])->assertSessionHas('error');
        $this->assertSame(AppointmentStatus::Rejected, $this->appointment->fresh()->status);
    }

    public function test_reschedule_moves_the_slot_and_cannot_take_a_booked_one(): void
    {
        $other = app(BookingService::class)->book([...$this->bookingPayload(['time' => '11:00']), 'patient_phone' => '01811223344']);
        $staff = $this->staff();

        $this->actingAs($staff)->patch(route('admin.appointments.reschedule', $this->appointment), ['date' => '2026-11-25', 'time' => '11:00'])
            ->assertSessionHasErrors('time', null, 'reschedule');

        $this->patch(route('admin.appointments.reschedule', $this->appointment), ['date' => '2026-11-26', 'time' => '18:00', 'note' => 'Patient asked'])
            ->assertSessionHas('success');

        $a = $this->appointment->fresh();
        $this->assertSame('2026-11-26 18:00', $a->slot_key);
        $this->assertSame('18:30:00', $a->end_time);
        $this->assertSame('2026-11-25 11:00', $other->fresh()->slot_key);
        // the old 10:30 slot is free again
        $this->postJson('/appointments', $this->bookingPayload(['patient_name' => 'New']))->assertCreated();
    }

    public function test_staff_can_add_internal_notes(): void
    {
        $this->actingAs($this->staff('Receptionist'))->post(route('admin.appointments.note', $this->appointment), ['note' => 'Called, no answer'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('appointment_activities', ['appointment_id' => $this->appointment->id, 'action' => 'note', 'note' => 'Called, no answer']);
    }

    public function test_staff_can_add_a_walk_in_and_confirm_it_immediately(): void
    {
        $this->actingAs($this->staff('Receptionist'))->post(route('admin.appointments.store'), [
            ...$this->bookingPayload(['time' => '19:00', 'patient_email' => '']), 'confirm_now' => '1',
        ])->assertRedirect();

        $walkIn = Appointment::query()->where('start_time', '19:00')->sole();
        $this->assertSame('admin', $walkIn->source);
        $this->assertSame(AppointmentStatus::Confirmed, $walkIn->status);
    }

    public function test_listing_filters_by_status_and_searches_by_phone(): void
    {
        $this->actingAs($this->staff('Receptionist'))
            ->get(route('admin.appointments.index', ['status' => 'pending', 'q' => '1711223344']))
            ->assertOk()->assertSee($this->appointment->reference);

        $this->get(route('admin.appointments.index', ['status' => 'confirmed']))->assertOk()->assertDontSee($this->appointment->reference);
    }
}
