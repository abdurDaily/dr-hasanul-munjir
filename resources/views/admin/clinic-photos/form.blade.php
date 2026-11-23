@extends('layouts.admin')
@section('title', $photo->exists ? 'Edit photo' : 'Add clinic photo')

@section('content')
<form method="post" action="{{ $photo->exists ? route('admin.clinic-photos.update', $photo) : route('admin.clinic-photos.store') }}" enctype="multipart/form-data" class="a-card a-card__body" style="max-width: 900px" novalidate>
  @csrf @if($photo->exists) @method('PUT') @endif
  <x-admin.image name="image" label="Photo" :path="$photo->image" preset="photo" :required="true" />
  <div class="row">
    <x-admin.input class="col-md-6" name="title" label="Caption" :value="$photo->title" required maxlength="80" placeholder="Modern clinic" />
    <x-admin.input class="col-md-6" name="subtitle" label="Caption line 2 (slider)" :value="$photo->subtitle" maxlength="120" placeholder="Clean, calm & sterilised" />
  </div>
  <x-admin.input name="alt" label="Image description (alt text)" :value="$photo->alt" required maxlength="255" />
  <div class="row">
    <x-admin.select class="col-md-8" name="gallery_category_id" label="Category (label in the Clinic section)" :options="$categories" :value="$photo->gallery_category_id" placeholder="— None —" />
    <x-admin.input class="col-md-4" name="sort_order" label="Display order" type="number" min="0" :value="$photo->sort_order" required />
  </div>
  <x-admin.toggle name="is_active" label="Show on website" :checked="$photo->is_active" />
  <x-admin.toggle name="show_in_clinic" label="Show in “The Clinic” section" :checked="$photo->show_in_clinic" />
  <x-admin.toggle name="show_in_slider" label="Show in the “Why choose me” slider" :checked="$photo->show_in_slider" />
  <div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('admin.clinic-photos.index') }}" class="btn btn-light">Cancel</a>
    <button class="btn btn-primary">Save</button>
  </div>
</form>
@endsection
