@props(['path', 'preset', 'alt' => '', 'sizes' => '100vw', 'width' => null, 'eager' => false])
@if($path)
<img {{ $attributes }} src="{{ image_url($path, $preset, $width) }}" srcset="{{ \App\Services\ImageService::srcset($path, $preset) }}" sizes="{{ $sizes }}" alt="{{ $alt }}" @if($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async">
@endif
