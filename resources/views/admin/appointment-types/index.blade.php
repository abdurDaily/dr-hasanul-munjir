@extends('layouts.admin')
@section('title', 'Appointment Types')
@section('subtitle', 'How patients can meet the doctor — all types share the same time slots')

@section('content')
<div class="a-card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Type</th><th>Button label</th><th>Summary note</th><th>Bookings</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($types as $type)
        <tr>
          <td><i class="bi {{ $type->icon }} me-2 text-primary"></i><strong>{{ $type->name }}</strong></td>
          <td>{{ $type->short_label }}</td>
          <td class="text-muted">{{ $type->note }}</td>
          <td>{{ $type->appointments_count }}</td>
          <td><span class="badge {{ $type->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $type->is_active ? 'Active' : 'Hidden' }}</span></td>
          <td class="text-end"><a href="{{ route('admin.schedule.appointment-types.edit', $type) }}" class="btn btn-sm btn-soft">Edit</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
