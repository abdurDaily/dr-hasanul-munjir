<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** In-clinic / phone / video. Fixed set: editable labels and on/off, no create/delete (appointments reference them). */
class AppointmentTypeController extends Controller
{
    public function index(): View
    {
        return view('admin.appointment-types.index', ['types' => AppointmentType::query()->ordered()->withCount('appointments')->get()]);
    }

    public function edit(AppointmentType $appointmentType): View
    {
        return view('admin.appointment-types.edit', ['type' => $appointmentType]);
    }

    public function update(Request $request, AppointmentType $appointmentType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'short_label' => ['required', 'string', 'max:40'],
            'icon' => ['required', Rule::in(config('icons'))],
            'note' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        if (! $data['is_active'] && AppointmentType::query()->active()->whereKeyNot($appointmentType->id)->doesntExist()) {
            return back()->withInput()->with('error', 'At least one appointment type must stay active.');
        }
        $appointmentType->update($data);

        return redirect()->route('admin.schedule.appointment-types.index')->with('success', 'Appointment type updated.');
    }
}
