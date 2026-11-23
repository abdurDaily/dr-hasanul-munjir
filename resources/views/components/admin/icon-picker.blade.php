@props(['name' => 'icon', 'label' => 'Icon', 'value' => null])
@php($current = old($name, $value))
<fieldset {{ $attributes->merge(['class' => 'mb-3']) }}>
  <legend class="form-label">{{ $label }} <span class="text-danger">*</span></legend>
  <div class="icon-pick" role="radiogroup">
    @foreach(config('icons') as $icon)
    <span><input type="radio" name="{{ $name }}" id="{{ $name }}-{{ $icon }}" value="{{ $icon }}" @checked($current === $icon) required><label for="{{ $name }}-{{ $icon }}" title="{{ str_replace('bi-', '', $icon) }}"><i class="bi {{ $icon }}"></i></label></span>
    @endforeach
  </div>
  @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</fieldset>
