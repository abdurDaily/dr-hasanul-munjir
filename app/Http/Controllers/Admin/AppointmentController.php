<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffAppointmentRequest;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Treatment;
use App\Services\Booking\AvailabilityService;
use App\Services\Booking\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

class AppointmentController extends Controller
{
    public function __construct(private readonly BookingService $booking) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'scope' => ['nullable', Rule::in(['today', 'upcoming', 'past'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'q' => ['nullable', 'string', 'max:100'],
            'treatment' => ['nullable', 'integer'],
            'type' => ['nullable', 'integer'],
        ]);
        $today = now()->toDateString();

        $base = Appointment::query()
            ->when($filters['scope'] ?? null, fn ($q, $scope) => match ($scope) {
                'today' => $q->whereDate('appointment_date', $today),
                'upcoming' => $q->whereDate('appointment_date', '>=', $today),
                'past' => $q->whereDate('appointment_date', '<', $today),
            })
            ->when($filters['date'] ?? null, fn ($q, $d) => $q->whereDate('appointment_date', $d))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $digits = preg_replace('/\D/', '', $term);
                $q->where(fn ($w) => $w->where('patient_name', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->when(strlen($digits) >= 4, fn ($w) => $w->orWhere('patient_phone', 'like', '%'.substr($digits, -10).'%')));
            })
            ->when($filters['treatment'] ?? null, fn ($q, $id) => $q->where('treatment_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $id) => $q->where('appointment_type_id', $id));

        $counts = (clone $base)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        $appointments = (clone $base)
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->with(['treatment:id,name', 'type:id,name,icon'])
            ->when(($filters['scope'] ?? null) === 'past', fn ($q) => $q->orderByDesc('appointment_date')->orderByDesc('start_time'),
                fn ($q) => $q->orderBy('appointment_date')->orderBy('start_time'))
            ->paginate(20)->withQueryString();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'counts' => $counts,
            'filters' => $filters,
            'treatments' => Treatment::query()->ordered()->pluck('name', 'id'),
            'types' => AppointmentType::query()->ordered()->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.appointments.create', [
            'types' => AppointmentType::query()->active()->ordered()->pluck('name', 'id'),
            'treatments' => Treatment::query()->active()->ordered()->pluck('name', 'id'),
        ]);
    }

    public function store(StaffAppointmentRequest $request): RedirectResponse
    {
        try {
            $appointment = $this->booking->book($request->bookingData(), $request->user());
        } catch (SlotUnavailableException $e) {
            return back()->withInput()->withErrors(['time' => $e->getMessage()]);
        }

        if ($request->boolean('confirm_now')) {
            $this->booking->transition($appointment, AppointmentStatus::Confirmed, $request->user(), 'Confirmed when added by staff');
        }

        return redirect()->route('admin.appointments.show', $appointment)->with('success', "Appointment {$appointment->reference} saved.");
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['type', 'treatment', 'handler:id,name', 'activities.user:id,name']);

        $history = Appointment::query()->where('patient_phone', $appointment->patient_phone)->whereKeyNot($appointment->id)
            ->with('treatment:id,name')->latest('appointment_date')->limit(5)->get();

        return view('admin.appointments.show', compact('appointment', 'history'));
    }

    public function status(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $to = AppointmentStatus::from($data['status']);

        try {
            $this->booking->transition($appointment, $to, $request->user(), $data['note'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Appointment marked {$to->label()}.");
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validateWithBag('reschedule', [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], ['time.required' => 'Please choose a free time slot.']);

        try {
            $this->booking->reschedule($appointment, $data['date'], $data['time'], $request->user(), $data['note'] ?? null);
        } catch (SlotUnavailableException|InvalidArgumentException $e) {
            return back()->withErrors(['time' => $e->getMessage()], 'reschedule');
        }

        return back()->with('success', 'Appointment rescheduled.');
    }

    public function note(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:2000']]);
        $this->booking->addNote($appointment, $request->user(), $data['note']);

        return back()->with('success', 'Note added.');
    }

    /** Free slots for one day, for staff (no booking-window / notice limits). */
    public function slots(Request $request, AvailabilityService $availability): JsonResponse
    {
        $data = $request->validate(['date' => ['required', 'date_format:Y-m-d'], 'ignore' => ['nullable', 'integer']]);

        return response()->json($availability->day(CarbonImmutable::parse($data['date']), $data['ignore'] ?? null, true));
    }
}
