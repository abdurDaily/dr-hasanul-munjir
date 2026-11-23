@props(['name', 'label', 'path' => null, 'preset', 'required' => false, 'help' => null])
@php($id = 'f-'.$name)
<div {{ $attributes->merge(['class' => 'mb-3']) }}>
  <label for="{{ $id }}" class="form-label">{{ $label }}@if($required && ! $path) <span class="text-danger">*</span>@endif</label>
  <div class="img-field">
    <img id="{{ $id }}-preview" class="img-field__preview" src="{{ $path ? image_url($path, $preset, \App\Services\ImageService::widths($preset)[0]) : '' }}" alt="">
    <div class="flex-grow-1">
      <input type="file" id="{{ $id }}" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" data-preview="#{{ $id }}-preview" data-max-kb="{{ config('images.max_upload_kb') }}"
        class="form-control{{ $errors->has($name) ? ' is-invalid' : '' }}" @required($required && ! $path)>
      @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
      <div class="form-text">{{ $help ?? 'JPG, PNG or WebP, up to '.round(config('images.max_upload_kb') / 1024).' MB. Converted to optimised WebP automatically.' }}@if($path) Leave empty to keep the current image.@endif</div>
    </div>
  </div>
</div>
