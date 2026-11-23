@extends('layouts.admin')
@section('title', 'Dashboard')
@section('subtitle')
Welcome back, {{ auth()->user()->name }} 👋 · {{ now()->format('l, j F Y') }}
@endsection

@section('content')
<div class="row g-3 mb-3">
  @foreach($kpis as [$label, $value, $icon, $color, $trend, $url])
  <div class="col-sm-6 col-xl-3">
    <a href="{{ $url }}" class="a-card kpi kpi--{{ $color }} text-reset">
      <span class="kpi__icon"><i class="bi {{ $icon }}"></i></span>
      <div class="flex-grow-1 min-w-0">
        <div class="kpi__label">{{ $label }}</div>
        <div class="kpi__value">{{ number_format($value) }}</div>
        <div class="kpi__foot">
          <span>@if($trend){{ array_sum($trend) }} in the last 14 days @else View all @endif</span>
          @if($trend)<x-admin.sparkline :values="$trend" />@endif
        </div>
      </div>
    </a>
  </div>
  @endforeach
</div>

@if($canAppointments)
<div class="row g-3">
  <div class="col-xxl-9">
    <section class="a-card h-100">
      <div class="a-card__head flex-wrap">
        <h2>Weekly Schedule</h2>
        <div class="d-flex align-items-center gap-2">
          <a class="btn btn-sm btn-light" href="{{ route('admin.dashboard', ['week' => $week['start']->subWeek()->toDateString()]) }}" aria-label="Previous week"><i class="bi bi-chevron-left"></i></a>
          <a class="btn btn-sm btn-light" href="{{ route('admin.dashboard', ['week' => $week['start']->addWeek()->toDateString()]) }}" aria-label="Next week"><i class="bi bi-chevron-right"></i></a>
          <a class="btn btn-sm btn-light" href="{{ route('admin.dashboard') }}">Today</a>
          <span class="text-muted small ms-1">{{ $week['start']->format('j M') }} – {{ $week['start']->addDays(6)->format('j M Y') }}</span>
        </div>
      </div>
      @php
        $ppm = 1.1; // pixels per minute
        $height = ($week['close'] - $week['open']) * $ppm;
        $nowMin = now()->hour * 60 + now()->minute;
      @endphp
      <div class="week-wrap">
        <div class="week">
          <div></div>
          @foreach($week['days'] as $day)
          <div class="week__day{{ $day->isToday() ? ' is-today' : '' }}"><span>{{ $day->format('D') }}<strong>{{ $day->format('M j') }}</strong></span></div>
          @endforeach

          <div class="week__hours" style="height: {{ $height }}px">
            @for($m = $week['open']; $m <= $week['close']; $m += 60)
            <span class="week__hour" style="top: {{ ($m - $week['open']) * $ppm }}px">{{ \App\Services\Booking\AvailabilityService::label($m) }}</span>
            @endfor
          </div>
          @foreach($week['days'] as $day)
          <div class="week__col" style="height: {{ $height }}px">
            @for($m = $week['open']; $m <= $week['close']; $m += 60)
            <div class="week__line" style="top: {{ ($m - $week['open']) * $ppm }}px"></div>
            @endfor
            @if($day->isToday() && $nowMin > $week['open'] && $nowMin < $week['close'])
            <div class="week__now" style="top: {{ ($nowMin - $week['open']) * $ppm }}px"></div>
            @endif
            @foreach($week['appointments']->get($day->toDateString(), []) as $a)
              @php($s = max($week['open'], \App\Services\Booking\AvailabilityService::minutes($a->start_time)))
              @php($e = \App\Services\Booking\AvailabilityService::minutes($a->end_time))
              <a href="{{ route('admin.appointments.show', $a) }}" class="appt-chip chip--{{ $a->status->value }}" style="top: {{ ($s - $week['open']) * $ppm + 1 }}px; height: {{ max(26, ($e - $s) * $ppm - 2) }}px" title="{{ $a->patient_name }} · {{ $a->timeRange() }} · {{ $a->status->label() }}">
                <strong>{{ \Illuminate\Support\Carbon::parse($a->start_time)->format('g:i') }} {{ $a->patient_name }}</strong>
                <span>{{ $a->treatmentName() }}</span>
              </a>
            @endforeach
          </div>
          @endforeach
        </div>
        <div class="legend mt-3">
          <span><i style="background:#F97316"></i>Pending</span>
          <span><i style="background:#0E7C86"></i>Confirmed</span>
          <span><i style="background:#16A34A"></i>Completed</span>
          <span><i style="background:#475569"></i>No-show</span>
        </div>
      </div>
    </section>
  </div>

  <div class="col-xxl-3">
    <div class="row g-3">
      <div class="col-md-6 col-xxl-12">
        <section class="a-card h-100">
          <div class="a-card__head"><h2>Upcoming Appointments</h2><a href="{{ route('admin.appointments.index', ['scope' => 'upcoming']) }}" class="small">View all</a></div>
          <div class="a-card__body pt-1">
            @if($upcoming->isEmpty())
              <div class="empty-state py-4"><i class="bi bi-calendar2-heart"></i>No upcoming appointments.</div>
            @else
            <ul class="a-list">
              @foreach($upcoming as $a)
              <li>
                <span class="a-avatar">{{ mb_strtoupper(mb_substr($a->patient_name, 0, 1)) }}</span>
                <a href="{{ route('admin.appointments.show', $a) }}" class="a-list__meta text-reset">
                  <strong>{{ $a->patient_name }}</strong>
                  <small>{{ $a->treatmentName() }}</small>
                  <small>{{ $a->appointment_date->format('M j') }}, {{ \Illuminate\Support\Carbon::parse($a->start_time)->format('g:i A') }}</small>
                </a>
                <x-admin.status :status="$a->status" />
              </li>
              @endforeach
            </ul>
            @endif
          </div>
        </section>
      </div>
      <div class="col-md-6 col-xxl-12">
        <div class="a-promo">
          <h3>Keep your clinic running smoothly</h3>
          <p class="mb-3">Block holidays and busy hours so patients only see times you can actually take.</p>
          @can('schedule.manage')<a href="{{ route('admin.schedule.index') }}" class="btn btn-accent btn-sm">Manage availability</a>@endcan
          <i class="bi bi-calendar-heart a-promo__art" aria-hidden="true"></i>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

@isset($messages)
<section class="a-card mt-3">
  <div class="a-card__head"><h2>Latest Messages</h2><a href="{{ route('admin.messages.index') }}" class="small">View all</a></div>
  <div class="a-card__body pt-1">
    @forelse($messages as $m)
      <ul class="a-list"><li>
        <span class="a-avatar">{{ mb_strtoupper(mb_substr($m->name, 0, 1)) }}</span>
        <a href="{{ route('admin.messages.show', $m) }}" class="a-list__meta text-reset">
          <strong>{{ $m->name }} @unless($m->read_at)<span class="badge badge-soft-warning ms-1">New</span>@endunless</strong>
          <small class="text-truncate">{{ \Illuminate\Support\Str::limit($m->message, 120) }}</small>
        </a>
        <small class="text-muted text-nowrap">{{ $m->created_at->diffForHumans() }}</small>
      </li></ul>
    @empty
      <div class="empty-state py-4"><i class="bi bi-inbox"></i>No messages yet.</div>
    @endforelse
  </div>
</section>
@endisset
@endsection
