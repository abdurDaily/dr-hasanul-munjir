<?php

namespace Database\Seeders;

use App\Models\AppointmentType;
use App\Models\ScheduleSession;
use Illuminate\Database\Seeder;

/** Chamber hours from the clinic visiting card: every day, 10:30–14:00 and 17:30–22:00. */
class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['In-Clinic Visit', 'In-Clinic', 'bi-hospital', 'Munjir Dental Care, Bahaddarhat'],
            ['Phone Consultation', 'Phone Call', 'bi-telephone', 'The doctor will call your number'],
            ['Video Consultation', 'Video Call', 'bi-camera-video', 'A video link will be sent to you'],
        ] as $i => [$name, $label, $icon, $note]) {
            AppointmentType::query()->firstOrCreate(['name' => $name], [
                'short_label' => $label, 'icon' => $icon, 'note' => $note, 'sort_order' => $i,
            ]);
        }

        if (ScheduleSession::query()->exists()) {
            return;
        }
        foreach (range(0, 6) as $weekday) {
            ScheduleSession::create(['weekday' => $weekday, 'name' => 'Morning', 'starts_at' => '10:30', 'ends_at' => '14:00']);
            ScheduleSession::create(['weekday' => $weekday, 'name' => 'Evening', 'starts_at' => '17:30', 'ends_at' => '22:00']);
        }
    }
}
