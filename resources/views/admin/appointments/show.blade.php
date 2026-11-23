@extends('layouts.admin')
@section('title', 'Appointment '.$appointment->reference)
@section('subtitle')
Requested {{ $appointment->created_at->format('j M Y, g:i A') }} via {{ $appointment->source === 'admin' ? 'staff' : 'website' }}
@endsection

@php
    use App\Enums\AppointmentStatus as S;
    $actions = [
        [S::Confirmed, 'Confirm', 'btn-primary', 'bi-check2-circle', 'Confirm this appointment?', 'The patient will be notified if they gave an email.'],
        [S::Completed, 'Mark completed', 'btn-success', 'bi-clipboard2-check', 'Mark as completed?', ''],
        [S::NoShow, 'No-show', 'btn-light', 'bi-person-x', 'Mark as no-show?', ''],
        [S::Rejected, 'Reject', 'btn-light text-danger', 'bi-x-circle', 'Reject this request?', 'The time slot becomes free again.'],
        [S::Cancelled, 'Cancel', 'btn-light text-danger', 'bi-slash-circle', 'Cancel this appointment?', 'The time slot becomes free again.'],
    ];
@endphp

@section('content')
<div class="row g-3">
  <div class="col-xl-8">
    <section class="a-card mb-3">
      <div class="a-card__head flex-wrap">
        <h2 class="d-flex align-items-center gap-2">{{ $appointment->patient_name }} <x-admin.status :status="$appointment->status" /></h2>
        <div class="d-flex flex-wrap gap-2">
          @foreach($actions as [$to, $label, $class, $icon, $confirm, $text])
            @if($appointment->status->canTransitionTo($to))
            <form method="post" action="{{ route('admin.appointments.status', $appointment) }}" data-confirm="{{ $confirm }}" data-confirm-text="{{ $text }}" @if(! $to->holdsSlot()) data-confirm-danger @endif>
              @csrf @method('PATCH')
              <input type="hidden" name="status" value="{{ $to->value }}">
              <button class="btn btn-sm {{ $class }}"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</button>
            </form>
            @endif
          @endforeach
        </div>
      </div>
      <div class="a-card__body">
        <dl class="detail-list">
          <dt>Date</dt><dd>{{ $appointment->appointment_date->format('l, j F Y') }}</dd>
          <dt>Time</dt><dd>{{ $appointment->timeRange() }} <small class="text-muted fw-normal">(Bangladesh time)</small></dd>
          <dt>Visit type</dt><dd><i class="bi {{ $appointment->type->icon }} me-1"></i>{{ $appointment->type->name }}</dd>
          <dt>Treatment</dt><dd>{{ $appointment->treatmentName() }}</dd>
          <dt>Phone</dt><dd><a href="{{ tel_href($appointment->patient_phone) }}">{{ $appointment->patient_phone }}</a> · <a href="https://wa.me/{{ substr(tel_href($appointment->patient_phone), 5) }}" target="_blank" rel="noopener" class="small"><i class="bi bi-whatsapp"></i> WhatsApp</a></dd>
          <dt>Email</dt><dd>@if($appointment->patient_email)<a href="mailto:{{ $appointment->patient_email }}">{{ $appointment->patient_email }}</a>@else<span class="text-muted fw-normal">Not given</span>@endif</dd>
          <dt>Message</dt><dd class="fw-normal">{!! $appointment->message ? nl2br(e($appointment->message)) : '<span class="text-muted">—</span>' !!}</dd>
          <dt>Reference</dt><dd><code>{{ $appointment->reference }}</code></dd>
          @if($appointment->handler)<dt>Handled by</dt><dd>{{ $appointment->handler->name }}</dd>@endif
        </dl>
      </div>
    </section>

    @if($appointment->status->isOpen())
    <section class="a-card mb-3">
      <div class="a-card__head"><h2>Reschedule</h2></div>
      <form class="a-card__body" method="post" action="{{ route('admin.appointments.reschedule', $appointment) }}" data-confirm="Move this appointment?" data-confirm-text="The patient will be notified if they gave an email.">
        @csrf @method('PATCH')
        <div data-slot-picker="{{ route('admin.appointments.slots') }}" data-ignore="{{ $appointment->id }}">
          <div class="row">
            <div class="col-md-5 mb-3">
              <label class="form-label" for="r-date">New date</label>
              <input type="date" id="r-date" name="date" class="form-control" min="{{ now()->toDateString() }}" value="{{ old('date', $appointment->appointment_date->toDateString()) }}" required>
            </div>
            <div class="col-md-7 mb-3">
              <label class="form-label" for="r-note">Reason (internal)</label>
              <input id="r-note" name="note" class="form-control" maxlength="1000" placeholder="e.g. Patient asked for an evening slot">
            </div>
          </div>
          <div class="slot-pick mb-3" role="radiogroup" aria-label="Available times"></div>
          @error('time', 'reschedule')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-primary"><i class="bi bi-calendar2-range me-1"></i>Reschedule</button>
      </form>
    </section>
    @endif

    @if($history->isNotEmpty())
    <section class="a-card">
      <div class="a-card__head"><h2>Other visits from this phone number</h2></div>
      <div class="table-responsive">
        <table class="table">
          <tbody>
            @foreach($history as $h)
            <tr><td>{{ $h->appointment_date->format('j M Y') }}</td><td>{{ $h->treatmentName() }}</td><td><x-admin.status :status="$h->status" /></td><td class="text-end"><a href="{{ route('admin.appointments.show', $h) }}" class="btn btn-sm btn-light">Open</a></td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
    @endif
  </div>

  <div class="col-xl-4">
    <section class="a-card">
      <div class="a-card__head"><h2>Internal notes &amp; activity</h2></div>
      <div class="a-card__body">
        <form method="post" action="{{ route('admin.appointments.note', $appointment) }}" class="mb-4">
          @csrf
          <label class="visually-hidden" for="note">Add a note</label>
          <textarea id="note" name="note" class="form-control mb-2 @error('note') is-invalid @enderror" rows="3" maxlength="2000" placeholder="Add an internal note (never shown to the patient)" required></textarea>
          @error('note')<div class="invalid-feedback mb-2">{{ $message }}</div>@enderror
          <button class="btn btn-sm btn-soft">Add note</button>
        </form>
        <ul class="timeline">
          @foreach($appointment->activities as $act)
          <li>
            <strong class="d-block">{{ match($act->action) { 'note' => 'Note', 'created' => 'Created', 'rescheduled' => 'Rescheduled', default => ucfirst(str_replace('_', '-', $act->action)) } }}</strong>
            @if($act->action === 'rescheduled' && $act->meta)
              <small class="d-block">{{ \Illuminate\Support\Carbon::parse($act->meta['from']['date'].' '.$act->meta['from']['time'])->format('j M, g:i A') }} → {{ \Illuminate\Support\Carbon::parse($act->meta['to']['date'].' '.$act->meta['to']['time'])->format('j M, g:i A') }}</small>
            @endif
            @if($act->note)<div class="small">{!! nl2br(e($act->note)) !!}</div>@endif
            <small class="text-muted">{{ $act->user?->name ?? 'Patient' }} · {{ $act->created_at->format('j M Y, g:i A') }}</small>
          </li>
          @endforeach
        </ul>
      </div>
    </section>
  </div>
</div>
@endsection
