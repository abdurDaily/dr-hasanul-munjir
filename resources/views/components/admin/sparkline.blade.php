@props(['values' => [], 'color' => '#14B1BE'])
@php
    $w = 90; $h = 32; $n = count($values);
    $max = max(1, max($values ?: [0]));
    $points = collect($values)->values()->map(fn ($v, $i) => round($n > 1 ? $i * $w / ($n - 1) : 0, 1).','.round($h - 3 - ($v / $max) * ($h - 6), 1))->implode(' ');
@endphp
@if($n > 1)
<svg class="kpi__spark" viewBox="0 0 {{ $w }} {{ $h }}" aria-hidden="true" preserveAspectRatio="none">
  <polyline points="{{ $points }}" fill="none" stroke="{{ $color }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
@endif
