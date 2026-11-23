<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\ScheduleSession;
use App\Models\Treatment;
use App\Services\Booking\AvailabilityService;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $canAppointments = $user->can('appointments.manage');
        $today = CarbonImmutable::today();

        $data = ['canAppointments' => $canAppointments, 'kpis' => []];

        if ($canAppointments) {
            $byStatus = Appointment::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
            $trend = $this->dailyCounts(Appointment::query(), 'created_at', 14);

            $data['kpis'] = [
                ['Total appointments', $byStatus->sum(), 'bi-calendar2-check', 'teal', $trend, route('admin.appointments.index')],
                ["Today's appointments", Appointment::query()->holdingSlot()->whereDate('appointment_date', $today)->count(), 'bi-calendar-day', 'blue', null, route('admin.appointments.index', ['date' => $today->toDateString()])],
                ['Pending requests', $byStatus[AppointmentStatus::Pending->value] ?? 0, 'bi-hourglass-split', 'orange', null, route('admin.appointments.index', ['status' => 'pending'])],
                ['Confirmed', $byStatus[AppointmentStatus::Confirmed->value] ?? 0, 'bi-check2-circle', 'violet', null, route('admin.appointments.index', ['status' => 'confirmed'])],
                ['Completed', $byStatus[AppointmentStatus::Completed->value] ?? 0, 'bi-clipboard2-check', 'green', null, route('admin.appointments.index', ['status' => 'completed'])],
            ];

            // Weekly schedule (Saturday-first week, like the public calendar)
            $weekStart = CarbonImmutable::parse($request->query('week', $today->toDateString()))->startOfDay();
            $weekStart = $weekStart->subDays(($weekStart->dayOfWeek + 1) % 7);
            $data['week'] = $this->week($weekStart);

            $data['upcoming'] = Appointment::query()->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed])
                ->where(fn ($q) => $q->whereDate('appointment_date', '>', $today)
                    ->orWhere(fn ($q) => $q->whereDate('appointment_date', $today)->where('start_time', '>=', now()->format('H:i'))))
                ->with('treatment:id,name', 'type:id,name,icon')
                ->orderBy('appointment_date')->orderBy('start_time')->limit(6)->get();
        }

        if ($user->can('treatments.manage')) {
            $data['kpis'][] = ['Treatments', Treatment::query()->count(), 'bi-heart-pulse', 'pink', null, route('admin.treatments.index')];
        }
        if ($user->can('blog.manage')) {
            $data['kpis'][] = ['Blog posts', BlogPost::query()->count(), 'bi-journal-richtext', 'slate', null, route('admin.posts.index')];
        }
        if ($user->can('messages.manage')) {
            $data['kpis'][] = ['Contact messages', ContactMessage::query()->count(), 'bi-chat-dots', 'teal', $this->dailyCounts(ContactMessage::query(), 'created_at', 14), route('admin.messages.index')];
            $data['messages'] = ContactMessage::query()->latest()->limit(4)->get();
        }

        return view('admin.dashboard', $data);
    }

    /** @return list<int> counts per day for the last $days days */
    private function dailyCounts($query, string $column, int $days): array
    {
        $from = CarbonImmutable::today()->subDays($days - 1);
        $counts = $query->where($column, '>=', $from)
            ->select(DB::raw("DATE({$column}) as d"), DB::raw('count(*) as c'))
            ->groupBy('d')->pluck('c', 'd');

        return collect(CarbonPeriod::create($from, CarbonImmutable::today()))
            ->map(fn ($day) => (int) ($counts[$day->format('Y-m-d')] ?? 0))->values()->all();
    }

    private function week(CarbonImmutable $start): array
    {
        $end = $start->addDays(6);
        $sessions = ScheduleSession::query()->where('is_active', true)->get();
        $open = $sessions->isEmpty() ? 9 * 60 : intdiv(AvailabilityService::minutes($sessions->min('starts_at')), 60) * 60;
        $close = $sessions->isEmpty() ? 18 * 60 : (int) ceil(AvailabilityService::minutes($sessions->max('ends_at')) / 60) * 60;

        $appointments = Appointment::query()->holdingSlot()
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->with('treatment:id,name')
            ->orderBy('start_time')->get()
            ->groupBy(fn ($a) => $a->appointment_date->toDateString());

        return [
            'start' => $start,
            'days' => collect(CarbonPeriod::create($start, $end))->map(fn ($d) => CarbonImmutable::instance($d)),
            'open' => $open,
            'close' => $close,
            'appointments' => $appointments,
        ];
    }
}
