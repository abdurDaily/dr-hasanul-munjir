@extends('layouts.admin')
@section('title', 'Experience & Credentials')
@section('subtitle', 'Education, registration, experience, certifications, training and achievements')
@section('actions')
<a href="{{ route('admin.credentials.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add</a>
@endsection

@section('content')
<nav class="a-tabs mb-3" aria-label="Filter by type">
  <a href="{{ route('admin.credentials.index') }}" class="{{ request('type') ? '' : 'active' }}">All</a>
  @foreach(\App\Models\Credential::TYPES as $key => $label)
  <a href="{{ route('admin.credentials.index', ['type' => $key]) }}" class="{{ request('type') === $key ? 'active' : '' }}">{{ $label }}</a>
  @endforeach
</nav>
<div class="a-card">
  @if($credentials->isEmpty())
    <div class="empty-state"><i class="bi bi-mortarboard"></i>Nothing here yet.</div>
  @else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>#</th><th>Title</th><th>Type</th><th>Date</th><th>Shown as</th><th></th></tr></thead>
      <tbody>
        @foreach($credentials as $c)
        <tr>
          <td class="text-muted">{{ $c->sort_order }}</td>
          <td><i class="bi {{ $c->icon }} text-primary me-1"></i><strong>{{ $c->title }} {{ $c->title_emphasis }}</strong><br><small class="text-muted">{{ $c->institution }}</small></td>
          <td>{{ $c->displayLabel() }}</td>
          <td class="text-nowrap">{{ $c->completed_on?->format('j M Y') ?? '—' }}</td>
          <td>
            @unless($c->is_active)<span class="badge badge-soft-secondary">Hidden</span>@endunless
            @if($c->is_featured)<span class="badge badge-soft-warning">Featured</span>@endif
            @if($c->show_on_timeline)<span class="badge badge-soft-primary">Timeline</span>@endif
          </td>
          <td class="text-end text-nowrap">
            <a href="{{ route('admin.credentials.edit', $c) }}" class="btn btn-sm btn-soft">Edit</a>
            <x-admin.delete-button :action="route('admin.credentials.destroy', $c)" />
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $credentials->links() }}</div>
  @endif
</div>
@endsection
