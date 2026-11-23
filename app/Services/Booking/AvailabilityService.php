<?php

namespace App\Services\Booking;

use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\ScheduleBreak;
use App\Models\ScheduleSession;
use App\Models\UnavailableDate;
use App\Services\Settings;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Turns the weekly schedule into concrete, bookable time slots.
 *
 * slots = weekly sessions − recurring breaks − holidays/leave − blocked slots − booked appointments
 *         − anything outside the booking window or within the minimum same-day notice.
 *
 * Always computed from the database; never cached, so a booking decision can't use stale data.
 * Times are minutes after midnight, Asia/Dhaka (the application timezone).
 */
class AvailabilityService
{
    public function __construct(private readonly Settings $settings) {}

    public function slotMinutes(): int
    {
        return max(10, (int) $this->settings->get('slot_minutes', '30'));
    }

    public function today(): CarbonImmutable
    {
        return CarbonImmutable::now()->startOfDay();
    }

    public function lastBookableDay(): CarbonImmutable
    {
        return $this->today()->addDays((int) $this->settings->get('booking_window_days', '60'));
    }

    /**
     * Every day in the public booking window.
     *
     * @return array<string, list<array{name:string, slots:list<array{time:string,label:string,available:bool}>}>>
     */
    public function calendar(): array
    {
        return $this->compute($this->today(), $this->lastBookableDay());
    }

    /**
     * Sessions and slots for a single day.
     *
     * @param  bool  $forStaff  staff may book outside the public window / notice period
     */
    public function day(CarbonImmutable $date, ?int $ignoreAppointmentId = null, bool $forStaff = false): array
    {
        return $this->compute($date->startOfDay(), $date->startOfDay(), $ignoreAppointmentId, $forStaff)[$date->toDateString()] ?? [];
    }

    public function isBookable(CarbonImmutable $date, string $time, ?int $ignoreAppointmentId = null, bool $forStaff = false): bool
    {
        $time = substr($time, 0, 5);

        foreach ($this->day($date, $ignoreAppointmentId, $forStaff) as $session) {
            foreach ($session['slots'] as $slot) {
                if ($slot['time'] === $time) {
                    return $slot['available'];
                }
            }
        }

        return false;
    }

    private function compute(CarbonImmutable $from, CarbonImmutable $to, ?int $ignoreAppointmentId = null, bool $forStaff = false): array
    {
        $length = $this->slotMinutes();
        $today = $this->today();
        $last = $this->lastBookableDay();
        $earliest = $forStaff ? -1 : self::minutes(now()->format('H:i')) + (int) $this->settings->get('min_lead_minutes', '30');

        $sessions = ScheduleSession::query()->where('is_active', true)->orderBy('starts_at')->get()->groupBy('weekday');
        $breaks = ScheduleBreak::query()->get();
        $closed = UnavailableDate::query()
            ->whereDate('starts_on', '<=', $to)->whereDate('ends_on', '>=', $from)->get();
        $blocked = BlockedSlot::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])->get()
            ->groupBy(fn ($b) => $b->date->toDateString());
        $booked = Appointment::query()->holdingSlot()
            ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
            ->when($ignoreAppointmentId, fn ($q) => $q->whereKeyNot($ignoreAppointmentId))
            ->get(['appointment_date', 'start_time', 'end_time'])
            ->groupBy(fn ($a) => $a->appointment_date->toDateString());

        $result = [];
        foreach (CarbonPeriod::create($from, $to) as $day) {
            $date = CarbonImmutable::instance($day);
            $key = $date->toDateString();
            $result[$key] = [];

            if (! $forStaff && ($date->lt($today) || $date->gt($last))) {
                continue;
            }
            if ($date->lt($today) || $closed->contains(fn ($c) => $date->betweenIncluded($c->starts_on, $c->ends_on))) {
                continue;
            }

            $busy = $this->ranges($breaks->filter(fn ($b) => $b->weekday === null || $b->weekday === $date->dayOfWeek), 'starts_at', 'ends_at');
            $blockedRanges = $this->ranges($blocked->get($key, collect()), 'starts_at', 'ends_at');
            $bookedRanges = $this->ranges($booked->get($key, collect()), 'start_time', 'end_time');

            foreach ($sessions->get($date->dayOfWeek, collect()) as $session) {
                $slots = [];
                $end = self::minutes($session->ends_at);
                for ($m = self::minutes($session->starts_at); $m + $length <= $end; $m += $length) {
                    if (self::overlapsAny($m, $m + $length, $busy)) {
                        continue; // breaks are not slots at all
                    }
                    $available = ! self::overlapsAny($m, $m + $length, $blockedRanges)
                        && ! self::overlapsAny($m, $m + $length, $bookedRanges)
                        && ! ($date->equalTo($today) && $m < $earliest);

                    $slots[] = ['time' => self::format($m), 'label' => self::label($m), 'available' => $available];
                }
                if ($slots) {
                    $result[$key][] = ['name' => $session->name, 'slots' => $slots];
                }
            }
        }

        return $result;
    }

    /** @return list<array{0:int,1:int}> */
    private function ranges(Collection $items, string $start, string $end): array
    {
        return $items->map(fn ($i) => [self::minutes($i->{$start}), self::minutes($i->{$end})])->values()->all();
    }

    private static function overlapsAny(int $start, int $end, array $ranges): bool
    {
        foreach ($ranges as [$s, $e]) {
            if ($start < $e && $end > $s) {
                return true;
            }
        }

        return false;
    }

    public static function minutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));

        return $h * 60 + $m;
    }

    public static function format(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    public static function label(int $minutes): string
    {
        $h = intdiv($minutes, 60);

        return sprintf('%d:%02d %s', ($h + 11) % 12 + 1, $minutes % 60, $h < 12 ? 'AM' : 'PM');
    }
}
