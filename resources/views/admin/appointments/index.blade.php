@extends('layouts.admin')
@section('title', 'Appointments')
@section('subtitle', 'Review requests, confirm visits and keep the schedule up to date')

@section('content')
@php($q = fn (array $merge) => route('admin.appointments.index', array_filter(array_merge(request()->except('page'), $merge), fn ($v) => $v !== null && $v !== '')))
<div class="a-card mb-3">
  <div class="a-card__body">
    <form class="row g-2 align-items-end" method="get">
      <div class="col-md-3"><label class="form-label" for="fq">Search</label><input id="fq" class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, phone or reference"></div>
      <div class="col-6 col-md-2"><label class="form-label" for="fdate">Date</label><input id="fdate" type="date" class="form-control" name="date" value="{{ $filters['date'] ?? '' }}"></div>
      <div class="col-6 col-md-2"><label class="form-label" for="fscope">When</label>
        <select id="fscope" class="form-select" name="scope"><option value="">Any time</option>@foreach(['today' => 'Today', 'upcoming' => 'Upcoming', 'past' => 'Past'] as $k => $v)<option value="{{ $k }}" @selected(($filters['scope'] ?? '') === $k)>{{ $v }}</option>@endforeach</select></div>
      <div class="col-6 col-md-2"><label class="form-label" for="ftr">Treatment</label>
        <select id="ftr" class="form-select" name="treatment"><option value="">All</option>@foreach($treatments as $id => $name)<option value="{{ $id }}" @selected((int) ($filters['treatment'] ?? 0) === $id)>{{ $name }}</option>@endforeach</select></div>
      <div class="col-6 col-md-2"><label class="form-label" for="fty">Type</label>
        <select id="fty" class="form-select" name="type"><option value="">All</option>@foreach($types as $id => $name)<option value="{{ $id }}" @selected((int) ($filters['type'] ?? 0) === $id)>{{ $name }}</option>@endforeach</select></div>
      <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
      <div class="col-md-1 d-flex gap-1"><button class="btn btn-primary flex-fill" aria-label="Apply filters"><i class="bi bi-funnel"></i></button>@if(array_filter($filters))<a href="{{ route('admin.appointments.index') }}" class="btn btn-light" aria-label="Clear filters" data-bs-toggle="tooltip" title="Clear"><i class="bi bi-x-lg"></i></a>@endif</div>
    </form>
  </div>
</div>

<nav class="a-tabs mb-3" aria-label="Filter by status">
  <a href="{{ $q(['status' => null]) }}" class="{{ empty($filters['status']) ? 'active' : '' }}">All<span class="count">{{ $counts->sum() }}</span></a>
  @foreach(\App\Enums\AppointmentStatus::cases() as $status)
  <a href="{{ $q(['status' => $status->value]) }}" class="{{ ($filters['status'] ?? '') === $status->value ? 'active' : '' }}">{{ $status->label() }}<span class="count">{{ $counts[$status->value] ?? 0 }}</span></a>
  @endforeach
</nav>

<div class="a-card">
  @if($appointments->isEmpty())
    <div class="empty-state"><i class="bi bi-calendar-x"></i>No appointments match these filters.</div>
  @else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Date &amp; time</th><th>Patient</th><th>Treatment</th><th>Type</th><th>Status</th><th>Reference</th><th></th></tr></thead>
      <tbody>
        @foreach($appointments as $a)
        <tr>
          <td class="text-nowrap"><strong>{{ $a->appointment_date->format('D, j M Y') }}</strong><br><small class="text-muted">{{ $a->timeRange() }}</small></td>
          <td><strong>{{ $a->patient_name }}</strong><br><a href="{{ tel_href($a->patient_phone) }}" class="small">{{ $a->patient_phone }}</a></td>
          <td>{{ $a->treatmentName() }}</td>
          <td class="text-nowrap"><i class="bi {{ $a->type->icon }} text-muted me-1"></i>{{ $a->type->name }}</td>
          <td><x-admin.status :status="$a->status" /></td>
          <td><code class="small">{{ $a->reference }}</code></td>
          <td class="text-end"><a href="{{ route('admin.appointments.show', $a) }}" class="btn btn-sm btn-soft">Open</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $appointments->links() }}</div>
  @endif
</div>
@endsection
