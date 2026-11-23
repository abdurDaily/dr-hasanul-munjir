<section class="page-hero">
  <div class="container">
    <nav aria-label="Breadcrumb">
      <ol class="crumbs list-unstyled">
        <li><a href="{{ route('home') }}">Home</a></li>
        @foreach($crumbs ?? [] as [$label, $href])
        <li>@if($href)<a href="{{ $href }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif</li>
        @endforeach
      </ol>
    </nav>
    @isset($eyebrow)<p class="eyebrow"><span class="eyebrow__line"></span>{{ $eyebrow }}</p>@endisset
    <h1 class="section-title page-hero__title">{{ $title }}</h1>
    @isset($lead)<p class="page-hero__lead">{{ $lead }}</p>@endisset
    {{ $slot ?? '' }}
  </div>
</section>
