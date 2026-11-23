<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedSlot;
use App\Models\ScheduleBreak;
use App\Models\ScheduleSession;
use App\Models\UnavailableDate;
use App\View\Composers\SiteComposer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    private const MODELS = ['breaks' => ScheduleBreak::class, 'closures' => UnavailableDate::class, 'blocks' => BlockedSlot::class];

    public function index(): View
    {
        return view('admin.schedule.index', [
            'sessions' => ScheduleSession::query()->orderBy('starts_at')->get()->groupBy('weekday'),
            'breaks' => ScheduleBreak::query()->orderBy('weekday')->orderBy('starts_at')->get(),
            'closures' => UnavailableDate::query()->whereDate('ends_on', '>=', today())->orderBy('starts_on')->get(),
            'blocks' => BlockedSlot::query()->whereDate('date', '>=', today())->orderBy('date')->orderBy('starts_at')->get(),
            'bookingFields' => config('settings.booking.fields'),
        ]);
    }

    /** Replaces the whole weekly timetable in one transaction. */
    public function sessions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sessions' => ['array'],
            'sessions.*.weekday' => ['required', 'integer', 'between:0,6'],
            'sessions.*.name' => ['required', 'string', 'max:40'],
            'sessions.*.starts_at' => ['required', 'date_format:H:i'],
            'sessions.*.ends_at' => ['required', 'date_format:H:i', 'after:sessions.*.starts_at'],
            'sessions.*.is_active' => ['nullable', 'boolean'],
        ], ['sessions.*.ends_at.after' => 'Each session must end after it starts.']);

        $rows = collect($data['sessions'] ?? []);
        // Sessions on the same day must not overlap
        foreach ($rows->groupBy('weekday') as $day) {
            $sorted = $day->sortBy('starts_at')->values();
            for ($i = 1; $i < $sorted->count(); $i++) {
                if ($sorted[$i]['starts_at'] < $sorted[$i - 1]['ends_at']) {
                    return back()->withInput()->with('error', ScheduleSession::WEEKDAYS[$sorted[$i]['weekday']].' has overlapping sessions.');
                }
            }
        }

        DB::transaction(function () use ($rows) {
            ScheduleSession::query()->delete();
            foreach ($rows as $row) {
                ScheduleSession::create([...$row, 'is_active' => (bool) ($row['is_active'] ?? false)]); // unchecked = inactive
            }
        });
        SiteComposer::flush();

        return back()->with('success', 'Chamber hours saved. Existing appointments are not changed.');
    }

    public function store(Request $request, string $kind): RedirectResponse
    {
        $rules = match ($kind) {
            'breaks' => [
                'weekday' => ['nullable', 'integer', 'between:0,6'],
                'starts_at' => ['required', 'date_format:H:i'],
                'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
                'label' => ['nullable', 'string', 'max:80'],
            ],
            'closures' => [
                'starts_on' => ['required', 'date_format:Y-m-d'],
                'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
                'type' => ['required', Rule::in(array_keys(UnavailableDate::TYPES))],
                'reason' => ['nullable', 'string', 'max:255'],
            ],
            'blocks' => [
                'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
                'starts_at' => ['required', 'date_format:H:i'],
                'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
                'reason' => ['nullable', 'string', 'max:255'],
            ],
        };
        $data = $request->validateWithBag($kind, $rules);
        if ($kind === 'blocks') {
            $data['created_by'] = $request->user()->id;
        }

        self::MODELS[$kind]::create($data);

        $note = $kind === 'breaks' ? '' : ' Existing appointments in this period are kept — review them in Appointments.';

        return back()->with('success', 'Saved.'.$note);
    }

    public function destroy(string $kind, int $id): RedirectResponse
    {
        self::MODELS[$kind]::query()->findOrFail($id)->delete();

        return back()->with('success', 'Removed.');
    }
}
