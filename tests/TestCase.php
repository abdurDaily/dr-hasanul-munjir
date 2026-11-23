<?php

namespace Tests;

use App\Models\AppointmentType;
use App\Models\Treatment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\ScheduleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;

abstract class TestCase extends BaseTestCase
{
    /** Roles, the default chamber hours (10:30–14:00, 17:30–22:00 daily) and appointment types. */
    protected function seedBasics(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, ScheduleSeeder::class]);
    }

    /** Monday 23 Nov 2026, 09:00 Dhaka time. */
    protected function travelToDhaka(string $at = '2026-11-23 09:00:00'): void
    {
        Carbon::setTestNow(Carbon::parse($at, 'Asia/Dhaka'));
    }

    protected function staff(string $role = User::SUPER_ADMIN): User
    {
        return User::factory()->role($role)->create();
    }

    protected function treatment(array $attributes = []): Treatment
    {
        return Treatment::create([
            'name' => 'Root Canal Treatment', 'short_description' => 'Saves your natural tooth.', 'icon' => 'bi-activity', ...$attributes,
        ]);
    }

    protected function bookingPayload(array $overrides = []): array
    {
        return [
            'appointment_type_id' => AppointmentType::query()->value('id'),
            'treatment_id' => Treatment::query()->value('id') ?? $this->treatment()->id,
            'date' => '2026-11-25',
            'time' => '10:30',
            'patient_name' => 'Rahima Akter',
            'patient_phone' => '01711-223344',
            'patient_email' => 'rahima@example.com',
            'message' => 'Tooth pain on the left side.',
            ...$overrides,
        ];
    }
}
