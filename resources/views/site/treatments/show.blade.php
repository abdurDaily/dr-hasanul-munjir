@extends('layouts.site')

@section('content')
@include('site.partials.page-hero', [
    'crumbs' => [['Treatments', route('treatments.index')], [$treatment->name, null]],
    'eyebrow' => $treatment->category?->name ?? 'Dental Treatment',
    'title' => $treatment->name,
    'lead' => $treatment->short_description,
])

<section class="treatment-detail section-pad pt-0">
  <div class="container">
    <div class="row gy-5 gx-lg-5">
      <div class="col-lg-7">
        @if($treatment->image)
        <div class="treatment-detail__media reveal-img">
          <x-picture :path="$treatment->image" preset="treatment" :alt="$treatment->image_alt ?? $treatment->name" sizes="(min-width: 992px) 700px, 92vw" :eager="true" />
          @if($treatment->is_speciality)<span class="treat-card__tag"><i class="bi bi-star-fill"></i> Speciality</span>@endif
        </div>
        @endif
        <div class="prose">
          @if($treatment->description)
            {!! $treatment->description !!}
          @else
            <p>{{ $treatment->short_description }}</p>
            <p>Every treatment starts with a careful examination and an honest explanation of your options. Book a visit and {{ $profile->full_name }} will recommend what is right for your teeth.</p>
          @endif
        </div>

        @if($treatment->images->isNotEmpty())
        <div class="treat-gallery">
          <h2 class="treat-gallery__title">Gallery</h2>
          <div class="treat-gallery__grid">
            @foreach($treatment->images as $img)
            <figure class="treat-gallery__item">
              <button type="button" class="treat-gallery__btn" data-lightbox="{{ image_url($img->image, 'photo', 1400) }}" data-caption="{{ $img->caption ?: $treatment->name }}" aria-label="Enlarge image{{ $img->caption ? ': '.$img->caption : '' }}">
                <x-picture :path="$img->image" preset="photo" :alt="$img->alt ?: $treatment->name" sizes="(min-width: 992px) 340px, (min-width: 576px) 45vw, 92vw" :width="600" />
                <span class="treat-gallery__zoom" aria-hidden="true"><i class="bi bi-arrows-angle-expand"></i></span>
              </button>
              @if($img->caption)<figcaption>{{ $img->caption }}</figcaption>@endif
            </figure>
            @endforeach
          </div>
        </div>
        @endif
      </div>
      <div class="col-lg-5">
        <aside class="treatment-facts">
          <span class="treat-card__icon"><i class="bi {{ $treatment->icon }}"></i></span>
          <h2 class="h4">At a glance</h2>
          <ul class="list-unstyled">
            @if($treatment->category)<li><i class="bi bi-grid"></i><span><small>Category</small>{{ $treatment->category->name }}</span></li>@endif
            @if($treatment->duration)<li><i class="bi bi-hourglass-split"></i><span><small>Estimated duration</small>{{ $treatment->duration }}</span></li>@endif
            <li><i class="bi bi-person-badge"></i><span><small>Treated by</small>{{ $profile->full_name }}@if($profile->credentials_line), {{ $profile->credentials_line }}@endif</span></li>
            <li><i class="bi bi-cash-coin"></i><span><small>Fees</small>Confirmed after examination</span></li>
          </ul>
          <a href="#appointment" class="btn-brand btn-brand--accent w-100 justify-content-center"><span>Book this treatment</span><i class="bi bi-arrow-down"></i></a>
          <a href="#" class="summary__wa js-whatsapp" data-wa-text="Hello, I'd like to ask about {{ $treatment->name }}."><i class="bi bi-whatsapp"></i> Ask on WhatsApp</a>
        </aside>
      </div>
    </div>
  </div>
</section>

@if($treatment->images->isNotEmpty())@include('site.partials.lightbox')@endif

@include('site.partials.booking', ['selectedTreatment' => $treatment])

@if($related->isNotEmpty())
<section class="treatments section-pad">
  <div class="container">
    <div class="section-head d-lg-flex align-items-end justify-content-between">
      <div>
        <p class="eyebrow"><span class="eyebrow__line"></span>Related Treatments</p>
        <h2 class="section-title split-heading">You may also <em>consider.</em></h2>
      </div>
      <a href="{{ route('treatments.index') }}" class="link-arrow">All treatments <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4 treat-grid">
      @foreach($related as $item)
        @include('site.partials.treatment-card', ['treatment' => $item, 'number' => $loop->iteration])
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection
