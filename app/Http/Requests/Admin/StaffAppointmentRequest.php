<?php

namespace App\Http\Requests\Admin;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Appointment added by staff (walk-in / phone booking). Authorised by the route's permission. */
class StaffAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('appointments.manage');
    }

    public function rules(): array
    {
        return [
            'appointment_type_id' => ['required', 'integer', Rule::exists('appointment_types', 'id')],
            'treatment_id' => ['nullable', 'integer', Rule::exists('treatments', 'id')->whereNull('deleted_at')],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'patient_name' => ['required', 'string', 'min:2', 'max:120'],
            'patient_phone' => ['required', 'string', 'max:20', new PhoneNumber],
            'patient_email' => ['nullable', 'email:rfc', 'max:190'],
            'message' => ['nullable', 'string', 'max:1000'],
            'confirm_now' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['time.required' => 'Please choose a free time slot.'];
    }

    public function bookingData(): array
    {
        return [
            ...$this->safe()->only(['appointment_type_id', 'treatment_id', 'date', 'time', 'patient_name', 'patient_email', 'message']),
            'patient_phone' => PhoneNumber::normalize($this->validated('patient_phone')),
        ];
    }
}
