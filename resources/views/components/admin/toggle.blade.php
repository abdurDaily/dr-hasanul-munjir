@props(['name', 'label', 'checked' => false, 'help' => null])
<div {{ $attributes->merge(['class' => 'form-check form-switch mb-3']) }}>
  <input type="hidden" name="{{ $name }}" value="0">
  <input class="form-check-input{{ $errors->has($name) ? ' is-invalid' : '' }}" type="checkbox" role="switch" id="f-{{ $name }}" name="{{ $name }}" value="1" @checked(old($name, $checked))>
  <label class="form-check-label fw-semibold" for="f-{{ $name }}">{{ $label }}</label>
  @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
  @if($help)<div class="form-text mt-0">{{ $help }}</div>@endif
</div>
