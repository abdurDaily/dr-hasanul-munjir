@extends('layouts.admin')
@section('title', 'Availability')
@section('subtitle', 'Chamber hours, breaks, holidays and blocked times — patients only see slots left free here')

@php
    use App\Models\ScheduleSession;
    $days = ScheduleSession::WEEKDAYS;
    $hm = fn ($t) => substr((string) $t, 0, 5);
    $rows = old('sessions') ?? $sessions->flatten()->map(fn ($s) => ['weekday' => $s->weekday, 'name' => $s->name, 'starts_at' => $hm($s->starts_at), 'ends_at' => $hm($s->ends_at), 'is_active' => $s->is_active])->all();
    $byDay = collect($rows)->groupBy('weekday');
@endphp

@section('content')
<div class="row g-3">
  <div class="col-xl-8">
    <form class="a-card" method="post" action="{{ route('admin.schedule.sessions') }}" id="sessionsForm">
      @csrf @method('PUT')
      <div class="a-card__head flex-wrap">
        <h2>Weekly chamber hours</h2>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-light" id="copySat" data-bs-toggle="tooltip" title="Copy Saturday's sessions to every day"><i class="bi bi-files me-1"></i>Copy Saturday to all days</button>
          <button class="btn btn-sm btn-primary"><i class="bi bi-check2 me-1"></i>Save hours</button>
        </div>
      </div>
      <div class="a-card__body">
        @if($errors->any() && ! $errors->hasBag('breaks') && ! $errors->hasBag('closures') && ! $errors->hasBag('blocks'))
          <div class="alert alert-danger small">{{ $errors->first() }}</div>
        @endif
        @foreach($days as $weekday => $dayName)
        <div class="row g-2 align-items-start py-2 border-bottom" data-day="{{ $weekday }}">
          <div class="col-md-2 pt-2"><strong>{{ $dayName }}</strong></div>
          <div class="col-md-10">
            <div class="d-flex flex-column gap-2 js-sessions">
              @forelse($byDay->get($weekday, []) as $row)
                @include('admin.schedule._session', ['weekday' => $weekday, 'row' => $row])
              @empty
                <span class="text-muted small pt-2 js-closed">Closed</span>
              @endforelse
            </div>
            <button type="button" class="btn btn-sm btn-link px-0 js-add-session"><i class="bi bi-plus-circle me-1"></i>Add session</button>
          </div>
        </div>
        @endforeach
        <p class="form-text mt-3 mb-0">A break between sessions (e.g. 2:00–5:30 PM) is simply the gap between them. Changing hours never moves existing appointments.</p>
      </div>
    </form>
    <template id="sessionTpl">@include('admin.schedule._session', ['weekday' => '__D__', 'row' => ['name' => '', 'starts_at' => '', 'ends_at' => '', 'is_active' => true]])</template>

    <div class="row g-3 mt-0">
      <div class="col-md-6">
        <section class="a-card h-100">
          <div class="a-card__head"><h2>Holidays &amp; closures</h2></div>
          <div class="a-card__body">
            <form method="post" action="{{ route('admin.schedule.store', 'closures') }}" class="row g-2 mb-3">
              @csrf
              <div class="col-6"><label class="form-label" for="c-from">From</label><input type="date" id="c-from" name="starts_on" class="form-control" required min="{{ now()->toDateString() }}"></div>
              <div class="col-6"><label class="form-label" for="c-to">To</label><input type="date" id="c-to" name="ends_on" class="form-control" required min="{{ now()->toDateString() }}"></div>
              <div class="col-5"><select name="type" class="form-select" aria-label="Type">@foreach(\App\Models\UnavailableDate::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
              <div class="col-7"><input name="reason" class="form-control" placeholder="Reason (e.g. Eid-ul-Fitr)" maxlength="255"></div>
              @if($errors->closures->any())<div class="col-12 text-danger small">{{ $errors->closures->first() }}</div>@endif
              <div class="col-12"><button class="btn btn-sm btn-soft"><i class="bi bi-plus-lg me-1"></i>Add closure</button></div>
            </form>
            <ul class="a-list">
              @forelse($closures as $c)
              <li><span class="a-list__meta"><strong>{{ $c->starts_on->format('j M Y') }}@unless($c->starts_on->eq($c->ends_on)) – {{ $c->ends_on->format('j M Y') }}@endunless</strong><small>{{ \App\Models\UnavailableDate::TYPES[$c->type] ?? $c->type }}@if($c->reason) · {{ $c->reason }}@endif</small></span><x-admin.delete-button :action="route('admin.schedule.destroy', ['closures', $c->id])" confirm="Remove this closure?" text="Patients will be able to book on these days again." /></li>
              @empty
              <li class="text-muted small">No upcoming holidays or closures.</li>
              @endforelse
            </ul>
          </div>
        </section>
      </div>
      <div class="col-md-6">
        <section class="a-card h-100">
          <div class="a-card__head"><h2>Blocked time slots</h2></div>
          <div class="a-card__body">
            <form method="post" action="{{ route('admin.schedule.store', 'blocks') }}" class="row g-2 mb-3">
              @csrf
              <div class="col-12"><label class="form-label" for="b-date">Date</label><input type="date" id="b-date" name="date" class="form-control" required min="{{ now()->toDateString() }}"></div>
              <div class="col-6"><label class="form-label" for="b-from">From</label><input type="time" id="b-from" name="starts_at" class="form-control" required></div>
              <div class="col-6"><label class="form-label" for="b-to">To</label><input type="time" id="b-to" name="ends_at" class="form-control" required></div>
              <div class="col-12"><input name="reason" class="form-control" placeholder="Reason (internal)" maxlength="255"></div>
              @if($errors->blocks->any())<div class="col-12 text-danger small">{{ $errors->blocks->first() }}</div>@endif
              <div class="col-12"><button class="btn btn-sm btn-soft"><i class="bi bi-plus-lg me-1"></i>Block time</button></div>
            </form>
            <ul class="a-list">
              @forelse($blocks as $b)
              <li><span class="a-list__meta"><strong>{{ $b->date->format('D, j M') }} · {{ \Illuminate\Support\Carbon::parse($b->starts_at)->format('g:i A') }} – {{ \Illuminate\Support\Carbon::parse($b->ends_at)->format('g:i A') }}</strong>@if($b->reason)<small>{{ $b->reason }}</small>@endif</span><x-admin.delete-button :action="route('admin.schedule.destroy', ['blocks', $b->id])" confirm="Unblock this time?" text="These slots become bookable again." /></li>
              @empty
              <li class="text-muted small">No blocked times.</li>
              @endforelse
            </ul>
          </div>
        </section>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    @can('schedule.manage')
    <form class="a-card mb-3" method="post" action="{{ route('admin.settings.update', 'booking') }}">
      @csrf @method('PUT')
      <div class="a-card__head"><h2>Booking rules</h2></div>
      <div class="a-card__body">
        @include('admin.settings._fields', ['fields' => $bookingFields])
        <button class="btn btn-primary w-100">Save rules</button>
      </div>
    </form>
    @endcan

    <section class="a-card mb-3">
      <div class="a-card__head"><h2>Recurring breaks</h2></div>
      <div class="a-card__body">
        <form method="post" action="{{ route('admin.schedule.store', 'breaks') }}" class="row g-2 mb-3">
          @csrf
          <div class="col-12"><select name="weekday" class="form-select" aria-label="Day"><option value="">Every day</option>@foreach($days as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
          <div class="col-6"><input type="time" name="starts_at" class="form-control" required aria-label="Break starts"></div>
          <div class="col-6"><input type="time" name="ends_at" class="form-control" required aria-label="Break ends"></div>
          <div class="col-12"><input name="label" class="form-control" placeholder="Label (e.g. Prayer break)" maxlength="80"></div>
          @if($errors->breaks->any())<div class="col-12 text-danger small">{{ $errors->breaks->first() }}</div>@endif
          <div class="col-12"><button class="btn btn-sm btn-soft"><i class="bi bi-plus-lg me-1"></i>Add break</button></div>
        </form>
        <ul class="a-list">
          @forelse($breaks as $br)
          <li><span class="a-list__meta"><strong>{{ $br->weekday === null ? 'Every day' : $days[$br->weekday] }} · {{ \Illuminate\Support\Carbon::parse($br->starts_at)->format('g:i A') }} – {{ \Illuminate\Support\Carbon::parse($br->ends_at)->format('g:i A') }}</strong>@if($br->label)<small>{{ $br->label }}</small>@endif</span><x-admin.delete-button :action="route('admin.schedule.destroy', ['breaks', $br->id])" confirm="Remove this break?" /></li>
          @empty
          <li class="text-muted small">No recurring breaks.</li>
          @endforelse
        </ul>
      </div>
    </section>

    <a href="{{ route('admin.schedule.appointment-types.index') }}" class="a-card a-card__body d-flex align-items-center gap-3 text-reset">
      <i class="bi bi-ui-radios-grid fs-4 text-primary"></i><span><strong class="d-block">Appointment types</strong><small class="text-muted">In-clinic, phone and video consultations</small></span><i class="bi bi-chevron-right ms-auto"></i>
    </a>
  </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const form = document.getElementById('sessionsForm');
  const tpl = document.getElementById('sessionTpl').innerHTML;
  let n = 1000;
  const renumber = () => form.querySelectorAll('.js-session').forEach((row, i) => row.querySelectorAll('[data-name]').forEach((el) => { el.name = `sessions[${i}][${el.dataset.name}]`; }));
  const add = (dayRow, values = {}) => {
    dayRow.querySelector('.js-closed')?.remove();
    const wrap = document.createElement('div');
    wrap.innerHTML = tpl.replaceAll('__D__', dayRow.dataset.day).trim();
    const row = wrap.firstElementChild;
    Object.entries(values).forEach(([k, v]) => { const el = row.querySelector(`[data-name="${k}"]`); if (el && el.type !== 'checkbox') el.value = v; else if (el) el.checked = v; });
    dayRow.querySelector('.js-sessions').appendChild(row);
    renumber();
  };
  form.addEventListener('click', (e) => {
    if (e.target.closest('.js-add-session')) add(e.target.closest('[data-day]'));
    if (e.target.closest('.js-remove-session')) { e.target.closest('.js-session').remove(); renumber(); }
  });
  document.getElementById('copySat').addEventListener('click', () => {
    const sat = [...form.querySelectorAll('[data-day="6"] .js-session')].map((r) => Object.fromEntries([...r.querySelectorAll('[data-name]')].map((el) => [el.dataset.name, el.type === 'checkbox' ? el.checked : el.value])));
    form.querySelectorAll('[data-day]:not([data-day="6"])').forEach((day) => {
      day.querySelectorAll('.js-session').forEach((r) => r.remove());
      sat.forEach((v) => add(day, { name: v.name, starts_at: v.starts_at, ends_at: v.ends_at, is_active: v.is_active }));
    });
    window.adminToast.fire({ icon: 'info', title: 'Copied — remember to save.' });
  });
  renumber();
})();
</script>
@endpush
