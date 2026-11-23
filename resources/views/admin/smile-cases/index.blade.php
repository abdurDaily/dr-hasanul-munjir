@extends('layouts.admin')
@section('title', 'Smile Gallery')
@section('subtitle', 'Before & after cases — published only with the patient\'s written consent')
@section('actions')
<a href="{{ route('admin.smile-cases.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add case</a>
@endsection

@section('content')
<div class="a-card">
  @if($cases->isEmpty())
    <div class="empty-state"><i class="bi bi-emoji-smile"></i>No cases yet. The Smile Gallery section stays hidden on the website until a consented case is published.</div>
  @else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Before / after</th><th>Case</th><th>Consent</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($cases as $c)
        <tr>
          <td class="text-nowrap"><img class="thumb" src="{{ image_url($c->before_image, 'compare', 420) }}" alt="Before"> <img class="thumb" src="{{ image_url($c->after_image, 'compare', 420) }}" alt="After"></td>
          <td><strong>{{ $c->title }}</strong><br><small class="text-muted">{{ $c->treatment?->name ?? 'No treatment' }}@if($c->duration) · {{ $c->duration }}@endif</small></td>
          <td>@if($c->consent_confirmed)<span class="badge badge-soft-success"><i class="bi bi-shield-check me-1"></i>{{ $c->consent_date?->format('j M Y') }}</span>@else<span class="badge badge-soft-warning">Missing</span>@endif</td>
          <td><span class="badge {{ $c->is_active && $c->consent_confirmed ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $c->is_active && $c->consent_confirmed ? 'Published' : 'Hidden' }}</span></td>
          <td class="text-end text-nowrap">
            <a href="{{ route('admin.smile-cases.edit', $c) }}" class="btn btn-sm btn-soft">Edit</a>
            <x-admin.delete-button :action="route('admin.smile-cases.destroy', $c)" confirm="Delete this case?" text="Both photos are permanently deleted." />
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $cases->links() }}</div>
  @endif
</div>
@endsection
