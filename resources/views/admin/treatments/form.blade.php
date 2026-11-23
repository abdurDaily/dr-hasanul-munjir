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
          <h3>Detail page gallery</h3>
          <p class="form-text mt-0 mb-3">Extra images shown on this treatment's page — for example how the condition looks, the stages of treatment, or the equipment used. Visitors can click to enlarge them. Only use patient photos with written consent.</p>

          @if($gallery->isNotEmpty())
          <div class="row g-3 mb-3">
            @foreach($gallery as $img)
            <div class="col-md-6 col-xxl-4">
              <div class="border rounded-3 p-2 h-100 gallery-item">
                <img src="{{ image_url($img->image, 'photo', 600) }}" alt="{{ $img->alt }}" class="w-100 rounded-2 mb-2" style="aspect-ratio: 4/3; object-fit: cover" loading="lazy">
                <input class="form-control form-control-sm mb-1" name="gallery[{{ $img->id }}][caption]" value="{{ old("gallery.{$img->id}.caption", $img->caption) }}" placeholder="Caption (shown under the image)" maxlength="160" aria-label="Caption">
                <input class="form-control form-control-sm mb-1" name="gallery[{{ $img->id }}][alt]" value="{{ old("gallery.{$img->id}.alt", $img->alt) }}" placeholder="Image description for screen readers" maxlength="255" aria-label="Alt text">
                <div class="d-flex align-items-center gap-2">
                  <input type="number" min="0" class="form-control form-control-sm" style="max-width: 90px" name="gallery[{{ $img->id }}][sort_order]" value="{{ old("gallery.{$img->id}.sort_order", $img->sort_order) }}" aria-label="Order" title="Display order">
                  <div class="form-check ms-auto mb-0">
                    <input class="form-check-input js-gallery-delete" type="checkbox" name="gallery[{{ $img->id }}][delete]" value="1" id="gdel-{{ $img->id }}">
                    <label class="form-check-label small text-danger" for="gdel-{{ $img->id }}">Remove</label>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @endif

          <label for="gallery-new" class="form-label">Add images</label>
          <input type="file" id="gallery-new" name="gallery_new[]" multiple accept="image/jpeg,image/png,image/webp"
            class="form-control{{ $errors->has('gallery_new') || $errors->has('gallery_new.*') ? ' is-invalid' : '' }}"
            data-preview-list="#gallery-new-preview" data-max-kb="{{ config('images.max_upload_kb') }}" data-max-files="{{ \App\Models\TreatmentImage::MAX_PER_UPLOAD }}">
          @if($errors->has('gallery_new') || $errors->has('gallery_new.*'))<div class="invalid-feedback">{{ $errors->first('gallery_new') ?: collect($errors->get('gallery_new.*'))->flatten()->first() }}</div>@endif
          <div class="form-text">Select several at once (up to {{ \App\Models\TreatmentImage::MAX_PER_UPLOAD }} per save, {{ \App\Models\TreatmentImage::MAX_PER_TREATMENT }} in total). Add captions after saving.</div>
          <div id="gallery-new-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
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
