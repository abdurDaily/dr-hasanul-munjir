@extends('layouts.admin')
@section('title', 'Why Choose Me')
@section('subtitle', 'Reasons listed beside the photo slider on the homepage')
@section('actions')
<a href="{{ route('admin.highlights.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add</a>
@endsection

@section('content')
<div class="a-card">
  @if($highlights->isEmpty())
    <div class="empty-state"><i class="bi bi-stars"></i>No reasons yet.</div>
  @else
  <ul class="a-list a-card__body">
    @foreach($highlights as $h)
    <li>
      <span class="a-avatar"><i class="bi {{ $h->icon }}"></i></span>
      <span class="a-list__meta"><strong>{{ $h->title }} @unless($h->is_active)<span class="badge badge-soft-secondary">Hidden</span>@endunless</strong><small>{{ $h->text }}</small></span>
      <small class="text-muted">#{{ $h->sort_order }}</small>
      <a href="{{ route('admin.highlights.edit', $h) }}" class="btn btn-sm btn-soft">Edit</a>
      <x-admin.delete-button :action="route('admin.highlights.destroy', $h)" />
    </li>
    @endforeach
  </ul>
  @endif
</div>
@endsection
