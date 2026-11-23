@extends('layouts.admin')
@section('title', $credential->exists ? 'Edit credential' : 'Add credential')

@section('content')
<form method="post" action="{{ $credential->exists ? route('admin.credentials.update', $credential) : route('admin.credentials.store') }}" enctype="multipart/form-data" novalidate>
  @csrf @if($credential->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-xl-8">
      <div class="a-card a-card__body">
        <div class="row">
          <x-admin.select class="col-md-6" name="type" label="Type" :options="\App\Models\Credential::TYPES" :value="$credential->type" required />
          <x-admin.input class="col-md-6" name="label" label="Timeline label" :value="$credential->label" placeholder="e.g. Post-graduate" help="Optional — defaults to the type." />
        </div>
        <div class="row">
          <x-admin.input class="col-md-6" name="title" label="Title" :value="$credential->title" required />
          <x-admin.input class="col-md-6" name="title_emphasis" label="Title — italic part" :value="$credential->title_emphasis" help="Shown in the accent font after the title (featured item)." />
        </div>
        <div class="row">
          <x-admin.input class="col-md-6" name="institution" label="Institution / venue" :value="$credential->institution" />
          <x-admin.input class="col-md-6" name="location" label="Location" :value="$credential->location" />
        </div>
        <x-admin.textarea name="description" label="Description" :value="$credential->description" maxlength="1000" />
        <div class="row">
          <x-admin.input class="col-md-6" name="completed_on" label="Completion date" type="date" :value="$credential->completed_on?->toDateString()" />
          <x-admin.input class="col-md-6" name="date_note" label="Date note" :value="$credential->date_note" placeholder="Exclusive workshop" />
        </div>
        <x-admin.image name="certificate_image" label="Certificate / photo" :path="$credential->certificate_image" preset="certificate" />
      </div>
    </div>
    <div class="col-xl-4">
      <div class="a-card a-card__body">
        <x-admin.toggle name="is_active" label="Show on website" :checked="$credential->is_active" />
        <x-admin.toggle name="show_on_timeline" label="Show on the journey timeline" :checked="$credential->show_on_timeline" />
        <x-admin.toggle name="is_featured" label="Feature as “Latest” highlight" :checked="$credential->is_featured" help="The most recent featured item with a photo is shown large." />
        <x-admin.input name="sort_order" label="Display order" type="number" min="0" :value="$credential->sort_order" required />
        <x-admin.icon-picker :value="$credential->icon" />
      </div>
    </div>
  </div>
  <div class="sticky-actions">
    <a href="{{ route('admin.credentials.index') }}" class="btn btn-light">Back</a>
    <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save</button>
  </div>
</form>
@endsection
