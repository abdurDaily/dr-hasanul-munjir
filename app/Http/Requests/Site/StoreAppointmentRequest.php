<?php

namespace App\Http\Requests\Site;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'patient_name' => trim((string) $this->input('patient_name')),
            'patient_email' => trim((string) $this->input('patient_email')) ?: null,
            'message' => trim((string) $this->input('message')) ?: null,
            // "Not sure — consultation" is stored as no specific treatment
            'treatment_id' => $this->input('treatment_id') === 'consultation' ? null : $this->input('treatment_id'),
            'is_consultation' => $this->input('treatment_id') === 'consultation',
        ]);
    }

    public function rules(): array
    {
        return [
            'appointment_type_id' => ['required', 'integer', Rule::exists('appointment_types', 'id')->where('is_active', true)],
            'treatment_id' => ['nullable', 'required_unless:is_consultation,true', 'integer', Rule::exists('treatments', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'patient_name' => ['required', 'string', 'min:2', 'max:120'],
            'patient_phone' => ['required', 'string', 'max:20', new PhoneNumber],
            'patient_email' => ['nullable', 'email:rfc', 'max:190'],
            'message' => ['nullable', 'string', 'max:1000'],
            'website' => ['prohibited'], // honeypot
        ];
    }

    public function messages(): array
    {
        return [
            'treatment_id.required_unless' => 'Please select a treatment.',
            'date.*' => 'Please select a valid date.',
            'time.*' => 'Please select an available time slot.',
            'website.prohibited' => 'Your request could not be sent.',
        ];
    }

    /** Data ready for BookingService::book(). */
    public function bookingData(): array
    {
        return [
            ...$this->safe()->only(['appointment_type_id', 'treatment_id', 'date', 'time', 'patient_name', 'patient_email', 'message']),
            'patient_phone' => PhoneNumber::normalize($this->validated('patient_phone')),
            'ip_address' => $this->ip(),
        ];
    }
}
