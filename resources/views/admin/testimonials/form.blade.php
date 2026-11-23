@extends('layouts.admin')
@section('title', $testimonial->exists ? 'Edit testimonial' : 'Add testimonial')

@section('content')
<form method="post" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" enctype="multipart/form-data" class="a-card a-card__body" style="max-width: 900px" novalidate>
  @csrf @if($testimonial->exists) @method('PUT') @endif
  <div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>Copy genuine reviews only (for example from your Google Business profile). Never write or edit reviews on a patient's behalf.</div>
  <div class="row">
    <x-admin.input class="col-md-6" name="display_name" label="Display name" :value="$testimonial->display_name" required maxlength="80" help="As the patient agreed, e.g. “Rahima A.”" />
    <x-admin.select class="col-md-3" name="source" label="Source" :options="\App\Models\Testimonial::SOURCES" :value="$testimonial->source" required />
    <x-admin.input class="col-md-3" name="reviewed_on" label="Review date" type="date" :value="$testimonial->reviewed_on?->toDateString()" />
  </div>
  <x-admin.textarea name="body" label="Review text" :value="$testimonial->body" required rows="4" maxlength="1500" />
  <div class="row">
    <x-admin.select class="col-md-4" name="rating" label="Rating" :options="['5' => '★★★★★ 5', '4.5' => '★★★★½ 4.5', '4' => '★★★★ 4', '3.5' => '★★★½ 3.5', '3' => '★★★ 3', '2' => '★★ 2', '1' => '★ 1']" :value="rtrim(rtrim(number_format((float) $testimonial->rating, 1), '0'), '.')" required />
    <x-admin.select class="col-md-5" name="treatment_id" label="Treatment" :options="$treatments" :value="$testimonial->treatment_id" placeholder="— None —" />
    <x-admin.input class="col-md-3" name="sort_order" label="Order" type="number" min="0" :value="$testimonial->sort_order ?? 0" required />
  </div>
  <x-admin.image name="avatar" label="Patient photo (optional)" :path="$testimonial->avatar" preset="avatar" help="Only with the patient's permission. Initials are shown otherwise." />
  <x-admin.toggle name="consent_confirmed" label="The patient agreed to have this review published" :checked="$testimonial->consent_confirmed" />
  <x-admin.toggle name="is_active" label="Show on website" :checked="$testimonial->is_active" />
  <x-admin.toggle name="is_featured" label="Featured" :checked="$testimonial->is_featured" />
  <div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-light">Cancel</a>
    <button class="btn btn-primary">Save</button>
  </div>
</form>
@endsection
