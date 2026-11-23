@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'help' => null, 'required' => false])
@php($id = 'f-'.$name)
@php($current = (string) old($name, $value))
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
  <label for="{{ $id }}" class="form-label">{{ $label }}@if($required) <span class="text-danger">*</span>@endif</label>
  <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('class')->merge(['class' => 'form-select'.($errors->has($name) ? ' is-invalid' : '')]) }} @required($required)>
    @if($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
    @foreach($options as $key => $text)
    <option value="{{ $key }}" @selected($current === (string) $key)>{{ $text }}</option>
    @endforeach
  </select>
  @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
  @if($help)<div class="form-text">{{ $help }}</div>@endif
</div>
