@extends('layouts.admin')
@section('title', 'Clinic Gallery')
@section('subtitle', 'Photos for "The Clinic" section (first 3) and the "Why choose me" slider')
@section('actions')
<a href="{{ route('admin.clinic-photos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add photo</a>
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
  <form method="get"><select class="form-select" name="category" aria-label="Category" onchange="this.form.submit()"><option value="">All categories</option>@foreach($categories as $id => $name)<option value="{{ $id }}" @selected((int) request('category') === $id)>{{ $name }}</option>@endforeach</select></form>
  <a href="{{ route('admin.taxonomies.index', 'gallery-categories') }}" class="btn btn-light"><i class="bi bi-tags me-1"></i>Categories</a>
</div>
@if($photos->isEmpty())
  <div class="a-card empty-state"><i class="bi bi-images"></i>No photos yet.</div>
@else
<div class="row g-3">
  @foreach($photos as $p)
  <div class="col-sm-6 col-lg-4 col-xxl-3">
    <div class="a-card h-100 overflow-hidden">
      <img src="{{ image_url($p->image, 'photo', 600) }}" alt="{{ $p->alt }}" class="w-100" style="aspect-ratio: 4/3; object-fit: cover" loading="lazy">
      <div class="a-card__body">
        <strong class="d-block">{{ $p->title }}</strong>
        <small class="text-muted d-block mb-2">{{ $p->category?->name ?? 'Uncategorised' }} · order {{ $p->sort_order }}</small>
        <div class="d-flex flex-wrap gap-1 mb-3">
          @unless($p->is_active)<span class="badge badge-soft-secondary">Hidden</span>@endunless
          @if($p->show_in_clinic)<span class="badge badge-soft-primary">Clinic section</span>@endif
          @if($p->show_in_slider)<span class="badge badge-soft-info">Slider</span>@endif
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('admin.clinic-photos.edit', $p) }}" class="btn btn-sm btn-soft flex-fill">Edit</a>
          <x-admin.delete-button :action="route('admin.clinic-photos.destroy', $p)" confirm="Delete this photo?" />
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
<div class="mt-3">{{ $photos->links() }}</div>
@endif
@endsection
