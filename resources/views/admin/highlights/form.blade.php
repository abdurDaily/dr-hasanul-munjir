@extends('layouts.admin')
@section('title', $highlight->exists ? 'Edit reason' : 'Add reason')

@section('content')
<form method="post" action="{{ $highlight->exists ? route('admin.highlights.update', $highlight) : route('admin.highlights.store') }}" class="a-card a-card__body" style="max-width: 760px" novalidate>
  @csrf @if($highlight->exists) @method('PUT') @endif
  <x-admin.input name="title" label="Title" :value="$highlight->title" required maxlength="80" />
  <x-admin.textarea name="text" label="Text" :value="$highlight->text" required maxlength="300" rows="2" />
  <x-admin.icon-picker :value="$highlight->icon" />
  <div class="row">
    <x-admin.input class="col-md-4" name="sort_order" label="Display order" type="number" min="0" :value="$highlight->sort_order" required />
    <div class="col-md-8 pt-md-4"><x-admin.toggle name="is_active" label="Show on website" :checked="$highlight->is_active" /></div>
  </div>
  <div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('admin.highlights.index') }}" class="btn btn-light">Cancel</a>
    <button class="btn btn-primary">Save</button>
  </div>
</form>
@endsection
