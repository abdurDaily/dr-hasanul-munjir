@props(['name', 'label', 'value' => null, 'type' => 'text', 'help' => null, 'required' => false])
@php($id = $attributes->get('id', 'f-'.str_replace(['[', ']'], ['-', ''], $name)))
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
  <label for="{{ $id }}" class="form-label">{{ $label }}@if($required) <span class="text-danger">*</span>@endif</label>
  <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ old($name, $value) }}"
    {{ $attributes->except(['class', 'id'])->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }} @required($required)>
  @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
  @if($help)<div class="form-text">{{ $help }}</div>@endif
</div>
