@extends('layouts.site')

@section('content')
@include('site.partials.page-hero', [
    'crumbs' => [['Treatments', null]],
    'eyebrow' => 'Dental Treatments',
    'title' => emphasis($site->get('treatments_title')),
    'lead' => $site->get('treatments_aside'),
])

<section class="treatments section-pad pt-0">
  <div class="container">
    @php($n = 0)
    @foreach($categories as $category)
    <div class="treat-group">
      <h2 class="treat-group__title">{{ $category->name }}</h2>
      <div class="row g-4 treat-grid">
        @foreach($category->treatments as $treatment)
          @include('site.partials.treatment-card', ['treatment' => $treatment, 'number' => ++$n])
        @endforeach
      </div>
    </div>
    @endforeach
    @if($uncategorised->isNotEmpty())
    <div class="treat-group">
      @if($categories->isNotEmpty())<h2 class="treat-group__title">More treatments</h2>@endif
      <div class="row g-4 treat-grid">
        @foreach($uncategorised as $treatment)
          @include('site.partials.treatment-card', ['treatment' => $treatment, 'number' => ++$n])
        @endforeach
      </div>
    </div>
    @endif
    @if($n === 0)
    <p class="demo-note"><i class="bi bi-info-circle"></i> Treatment details are being updated. Please call us to discuss your needs.</p>
    @endif

    <div class="text-center mt-5"><a href="{{ route('home') }}#appointment" class="btn-brand btn-brand--accent"><span>Book Appointment</span><i class="bi bi-arrow-up-right"></i></a></div>
  </div>
</section>
@endsection
