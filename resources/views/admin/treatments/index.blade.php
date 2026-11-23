@extends('layouts.admin')
@section('title', 'Treatments')
@section('subtitle', 'Services shown on the homepage and treatment pages')
@section('actions')
<a href="{{ route('admin.treatments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add</a>
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
  <form class="d-flex gap-2" method="get">
    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search treatments" aria-label="Search">
    <select class="form-select" name="category" aria-label="Category" onchange="this.form.submit()"><option value="">All categories</option>@foreach($categories as $id => $name)<option value="{{ $id }}" @selected((int) request('category') === $id)>{{ $name }}</option>@endforeach</select>
  </form>
  <div class="d-flex gap-2">
    @if(request()->boolean('trashed'))
      <a href="{{ route('admin.treatments.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>All treatments</a>
    @elseif($trashedCount)
      <a href="{{ route('admin.treatments.index', ['trashed' => 1]) }}" class="btn btn-light"><i class="bi bi-trash me-1"></i>Trash ({{ $trashedCount }})</a>
    @endif
    <a href="{{ route('admin.taxonomies.index', 'treatment-categories') }}" class="btn btn-light"><i class="bi bi-tags me-1"></i>Categories</a>
  </div>
</div>

<div class="a-card">
  @if($treatments->isEmpty())
    <div class="empty-state"><i class="bi bi-heart-pulse"></i>No treatments yet. <a href="{{ route('admin.treatments.create') }}">Add the first one</a>.</div>
  @else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>#</th><th>Treatment</th><th>Category</th><th>Homepage</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($treatments as $t)
        <tr>
          <td class="text-muted">{{ $t->sort_order }}</td>
          <td><div class="d-flex align-items-center gap-3">@if($t->image)<img class="thumb" src="{{ image_url($t->image, 'treatment', 640) }}" alt="" loading="lazy">@endif<div><strong><i class="bi {{ $t->icon }} text-primary me-1"></i>{{ $t->name }}</strong>@if($t->is_speciality) <span class="badge badge-soft-info">Speciality</span>@endif<br><small class="text-muted">/treatments/{{ $t->slug }}</small></div></div></td>
          <td>{{ $t->category?->name ?? '—' }}</td>
          <td>{!! $t->is_featured ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<span class="text-muted">—</span>' !!}</td>
          <td><span class="badge {{ $t->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $t->is_active ? 'Active' : 'Hidden' }}</span></td>
          <td class="text-end text-nowrap">
            @if($t->trashed())
            <form method="post" action="{{ route('admin.treatments.restore', $t->id) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-soft"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button></form>
            @else
            @if($t->is_active)<a href="{{ route('treatments.show', $t->slug) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light" aria-label="View on website" data-bs-toggle="tooltip" title="View"><i class="bi bi-box-arrow-up-right"></i></a>@endif
            <a href="{{ route('admin.treatments.edit', $t) }}" class="btn btn-sm btn-soft">Edit</a>
            <x-admin.delete-button :action="route('admin.treatments.destroy', $t)" confirm="Delete {{ $t->name }}?" text="It moves to Trash and disappears from the website. Past appointments keep their treatment name." />
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $treatments->links() }}</div>
  @endif
</div>
@endsection
