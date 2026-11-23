@props(['name', 'label', 'value' => null, 'help' => null, 'required' => false, 'rows' => 3, 'editor' => false, 'uploadUrl' => null])
@php($id = 'f-'.$name)
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
  <label for="{{ $id }}" class="form-label">{{ $label }}@if($required) <span class="text-danger">*</span>@endif</label>
  <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if($uploadUrl) data-upload-url="{{ $uploadUrl }}" @endif
    {{ $attributes->except('class')->merge(['class' => 'form-control'.($editor ? ' js-editor' : '').($errors->has($name) ? ' is-invalid' : '')]) }} @required($required && ! $editor)>{{ old($name, $value) }}</textarea>
  @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
  @if($help)<div class="form-text">{{ $help }}</div>@endif
</div>
@if($editor)
  @once
  @push('styles')<link rel="stylesheet" href="{{ asset('admin-assets/vendor/quill.snow.css') }}">@endpush
  @push('scripts')<script src="{{ asset('admin-assets/vendor/quill.js') }}"></script>@endpush
  @endonce
@endif
