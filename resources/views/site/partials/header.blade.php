@php
    // Section links scroll in place on the homepage and go to "/#section" elsewhere
    $to = fn (string $id) => $isHome ? "#{$id}" : route('home')."#{$id}";
    $nav = [
        ['Home', $isHome ? '#home' : route('home'), false],
        ['About', $to('about'), false],
        ['Treatments', $isHome ? '#treatments' : route('treatments.index'), request()->routeIs('treatments.*')],
        ['Experience', $to('experience'), false],
        ['Testimonials', $to('testimonials'), false],
        ['Blog', route('blog.index'), request()->routeIs('blog.*')],
        ['Contact', $to('contact'), false],
    ];
    $bookHref = $isHome || request()->routeIs('treatments.show') ? '#appointment' : route('home').'#appointment';
@endphp
<!-- ============ NAVBAR ============ -->
<header class="site-header" id="siteHeader">
  <nav class="navbar navbar-expand-xl" aria-label="Main navigation">
    <div class="container">
      <a class="brand" href="{{ $isHome ? '#home' : route('home') }}">
        <span class="brand__mark" aria-hidden="true">
          <svg viewBox="0 0 40 40"><path d="M20 7c-3-2.6-9.5-3.2-12 .8-2.6 4.2-.6 9.6.9 13.4 1.2 3.2 1.5 12.3 4.6 12.3 2.8 0 2.6-8.6 6.5-8.6s3.7 8.6 6.5 8.6c3.1 0 3.4-9.1 4.6-12.3 1.5-3.8 3.5-9.2.9-13.4C29.5 3.8 23 4.4 20 7Z"/></svg>
        </span>
        <span class="brand__text">
          <strong>{{ $profile->full_name }}</strong>
          <small>{{ $site->get('clinic_name') }}</small>
        </span>
      </a>

      <ul class="navbar-nav nav-desktop d-none d-xl-flex">
        @foreach($nav as [$label, $href, $active])
        <li><a class="nav-link{{ $active ? ' active' : '' }}" href="{{ $href }}"@if($active) aria-current="page"@endif>{{ $label }}</a></li>
        @endforeach
      </ul>

      <div class="d-flex align-items-center gap-2">
        <a href="{{ $bookHref }}" class="btn-brand btn-brand--outline btn-brand--sm d-none d-sm-inline-flex"><span>Book Appointment</span><i class="bi bi-arrow-right"></i></a>
        <button class="menu-toggle d-xl-none" type="button" data-offcanvas-open="#mobileNav" aria-controls="mobileNav" aria-expanded="false" aria-label="Open menu">
          <span></span><span></span>
        </button>
      </div>
    </div>
  </nav>
</header>

<!-- Mobile offcanvas navigation -->
<div class="offcanvas offcanvas-end mobile-nav" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
  <div class="offcanvas-header">
    <h2 class="offcanvas-title h5" id="mobileNavLabel">Menu</h2>
    <button type="button" class="btn-close btn-close-white" data-offcanvas-close aria-label="Close menu"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column">
    <ul class="mobile-nav__list">
      @foreach($nav as [$label, $href, $active])
      <li><a href="{{ $href }}"@if($active) class="active" aria-current="page"@endif>{{ $label }}</a></li>
      @endforeach
    </ul>
    <a href="{{ $bookHref }}" class="btn-brand btn-brand--accent mt-auto w-100 justify-content-center"><span>Book Appointment</span><i class="bi bi-arrow-up-right"></i></a>
    <a href="{{ tel_href($site->get('phone_primary')) }}" class="mobile-nav__phone"><i class="bi bi-telephone"></i> {{ $site->get('phone_primary') }}</a>
  </div>
</div>
