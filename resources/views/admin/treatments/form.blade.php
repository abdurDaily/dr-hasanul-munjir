@extends('layouts.admin')
@section('title', $treatment->exists ? 'Edit '.$treatment->name : 'New Treatment')

@section('content')
<form method="post" action="{{ $treatment->exists ? route('admin.treatments.update', $treatment) : route('admin.treatments.store') }}" enctype="multipart/form-data" novalidate>
  @csrf @if($treatment->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-xl-8">
      <div class="a-card a-card__body">
        <div class="form-section">
          <div class="row">
            <x-admin.input class="col-md-7" name="name" id="t-name" label="Treatment name" :value="$treatment->name" required maxlength="120" />
            <x-admin.input class="col-md-5" name="slug" label="URL slug" :value="$treatment->slug" data-slug-from="#t-name" help="Leave empty to generate from the name." />
          </div>
          <x-admin.textarea name="short_description" label="Short description" :value="$treatment->short_description" required maxlength="300" help="Shown on treatment cards (max 300 characters)." />
          <x-admin.textarea name="description" label="Full description" :value="$treatment->description" :editor="true" :upload-url="route('admin.editor-images.store')" help="Shown on the treatment's own page." />
        </div>
        <div class="form-section">
          <h3>Image</h3>
          <x-admin.image name="image" label="Featured image" :path="$treatment->image" preset="treatment" :required="true" />
          <x-admin.input name="image_alt" label="Image description (alt text)" :value="$treatment->image_alt" help="Describe the photo for screen readers and search engines." />
        </div>
        <div class="form-section">
          <h3>SEO</h3>
          <x-admin.input name="seo_title" label="SEO title" :value="$treatment->seo_title" maxlength="70" help="Defaults to “Name in Chattogram | Clinic”." />
          <x-admin.textarea name="seo_description" label="SEO description" :value="$treatment->seo_description" maxlength="320" rows="2" help="Defaults to the short description." />
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="a-card a-card__body">
        <x-admin.toggle name="is_active" label="Show on website" :checked="$treatment->is_active" />
        <x-admin.toggle name="is_featured" label="Show on homepage" :checked="$treatment->is_featured" />
        <x-admin.toggle name="is_speciality" label="Mark as speciality" :checked="$treatment->is_speciality" />
        <x-admin.select name="treatment_category_id" label="Category" :options="$categories" :value="$treatment->treatment_category_id" placeholder="— None —" />
        <div class="row">
          <x-admin.input class="col-6" name="duration" label="Estimated duration" :value="$treatment->duration" placeholder="45–60 min" />
          <x-admin.input class="col-6" name="sort_order" label="Display order" type="number" min="0" :value="$treatment->sort_order" required />
        </div>
        <x-admin.icon-picker :value="$treatment->icon" />
      </div>
    </div>
  </div>
  <div class="sticky-actions">
    <a href="{{ route('admin.treatments.index') }}" class="btn btn-light">Back</a>
    <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save treatment</button>
  </div>
</form>
@endsection
