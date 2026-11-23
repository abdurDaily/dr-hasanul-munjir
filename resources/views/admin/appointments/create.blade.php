@extends('layouts.admin')
@section('title', 'New Appointment')
@section('subtitle', 'Add a walk-in or phone booking — the same double-booking checks apply')

@section('content')
<form method="post" action="{{ route('admin.appointments.store') }}" novalidate>
  @csrf
  <div class="row g-3">
    <div class="col-lg-7">
      <div class="a-card a-card__body">
        <div class="form-section">
          <h3>Visit</h3>
          <div class="row">
            <x-admin.select class="col-md-6" name="appointment_type_id" label="Visit type" :options="$types" required />
            <x-admin.select class="col-md-6" name="treatment_id" label="Treatment" :options="$treatments" placeholder="Not sure — consultation" />
          </div>
          <div data-slot-picker="{{ route('admin.appointments.slots') }}">
            <x-admin.input name="date" label="Date" type="date" :value="now()->toDateString()" :min="now()->toDateString()" required />
            <label class="form-label">Time <span class="text-danger">*</span></label>
            <div class="slot-pick" role="radiogroup" aria-label="Available times"></div>
            @error('time')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="a-card a-card__body">
        <div class="form-section">
          <h3>Patient</h3>
          <x-admin.input name="patient_name" label="Full name" required maxlength="120" />
          <x-admin.input name="patient_phone" label="Phone" type="tel" required placeholder="01XXXXXXXXX" />
          <x-admin.input name="patient_email" label="Email" type="email" help="Optional. The patient is emailed about confirmations and changes." />
          <x-admin.textarea name="message" label="Notes from patient" />
          <x-admin.toggle name="confirm_now" label="Confirm immediately" :checked="true" />
        </div>
      </div>
    </div>
  </div>
  <div class="sticky-actions">
    <a href="{{ route('admin.appointments.index') }}" class="btn btn-light">Cancel</a>
    <button class="btn btn-accent"><i class="bi bi-check2 me-1"></i> Save appointment</button>
  </div>
</form>
@endsection
