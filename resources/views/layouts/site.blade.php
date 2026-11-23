@php
    $seo = $seo ?? [];
    $title = $seo['title'] ?? $site->get('seo_title');
    $description = $seo['description'] ?? $site->get('seo_description');
    $canonical = $seo['canonical'] ?? url()->current();
    $ogImage = $seo['image'] ?? ($site->get('og_image') ? image_url($site->get('og_image'), 'og') : asset('assets/images/og-image.jpg'));
    $isHome = request()->routeIs('home');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }}</title>
  <meta name="description" content="{{ $description }}">
  <meta name="theme-color" content="#14B1BE">
  <link rel="canonical" href="{{ $canonical }}">
  @if(!empty($seo['noindex']))<meta name="robots" content="noindex, follow">@endif
  @if($site->get('google_site_verification'))<meta name="google-site-verification" content="{{ $site->get('google_site_verification') }}">@endif

  <!-- Open Graph (Facebook, WhatsApp, LinkedIn, Messenger) -->
  <meta property="og:type" content="{{ $seo['type'] ?? 'website' }}">
  <meta property="og:site_name" content="{{ $site->get('clinic_name') }}">
  <meta property="og:title" content="{{ $seo['og_title'] ?? ($isHome ? $site->get('og_title', $title) : $title) }}">
  <meta property="og:description" content="{{ $seo['og_description'] ?? ($isHome ? $site->get('og_description', $description) : $description) }}">
  <meta property="og:url" content="{{ $canonical }}">
  <meta property="og:image" content="{{ $ogImage }}">
  <meta property="og:image:alt" content="{{ $seo['image_alt'] ?? $title }}">
  @isset($seo['published_time'])<meta property="article:published_time" content="{{ $seo['published_time'] }}">@endisset

  <!-- Twitter / X -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $seo['og_title'] ?? $title }}">
  <meta name="twitter:description" content="{{ $description }}">
  <meta name="twitter:image" content="{{ $ogImage }}">
  <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon.svg') }}">

  <!-- Self-hosted fonts (latin subset) — preload the ones used above the fold -->
  <link rel="preload" href="{{ asset('assets/fonts/SpaceGrotesk-latin-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('assets/fonts/Inter-latin-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('assets/fonts/InstrumentSerif-latin-italic.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="{{ asset('assets/fonts/bootstrap-icons-subset.woff2') }}" as="font" type="font/woff2" crossorigin>
  @stack('preload')

  <!-- One minified bundle built by tools/build-css.cjs. Swiper's CSS is loaded on demand. -->
  <link rel="stylesheet" href="{{ asset('assets/css/site.min.css') }}?v={{ filemtime(public_path('assets/css/site.min.css')) }}">

  @foreach($seo['schema'] ?? [] as $schema)
  <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
  @endforeach
</head>
<body>

<a class="skip-link" href="#main">Skip to content</a>

@include('site.partials.header', ['isHome' => $isHome])

<main id="main">
@yield('content')
</main>

@include('site.partials.footer', ['isHome' => $isHome])

<!-- Floating actions -->
<a href="#" class="fab-whatsapp js-whatsapp" data-wa-text="Hello {{ $profile->full_name }}, I'd like to book an appointment." aria-label="Chat on WhatsApp"><i class="bi bi-whatsapp"></i></a>
<button type="button" class="to-top" id="toTop" aria-label="Back to top"><svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="22"/></svg><i class="bi bi-arrow-up"></i></button>

<script>window.SITE_CONFIG = {!! json_encode(['whatsappNumber' => $site->get('whatsapp_number', ''), 'assets' => asset('assets'), 'csrf' => csrf_token()], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!};</script>
<script src="{{ asset('assets/js/main.js') }}?v={{ filemtime(public_path('assets/js/main.js')) }}" defer></script>
<script src="{{ asset('assets/js/interactions.js') }}?v={{ filemtime(public_path('assets/js/interactions.js')) }}" defer></script>
</body>
</html>
