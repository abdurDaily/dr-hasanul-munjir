@props(['status'])
<span {{ $attributes->merge(['class' => 'badge badge-soft-'.$status->color()]) }}>{{ $status->label() }}</span>
