@extends('layouts.site')

@push('preload')
  @if($profile->hero_image)
  <!-- Hero portrait is the largest element: fetch it first -->
  <link rel="preload" as="image" type="image/webp" imagesrcset="{{ \App\Services\ImageService::srcset($profile->hero_image, 'hero') }}" imagesizes="(min-width: 992px) 580px, min(420px, 88vw)" fetchpriority="high">
  @endif
@endpush

@section('content')

<!-- ============ HERO ============ -->
<section class="hero" id="home">
  <div class="container">
    <div class="hero__shell">
      <!-- Decorative thin curves through the floating cards -->
      <svg class="hero__curves" viewBox="0 0 1000 620" fill="none" preserveAspectRatio="none" aria-hidden="true">
        <path d="M600 330C690 250 800 230 860 260C920 290 960 330 1000 345"/>
        <path d="M640 560C720 470 800 470 860 500"/>
      </svg>

      <div class="hero__left">
        @if($site->get('hero_tag'))<p class="hero__tag"><span class="hero__tag-line"></span>{{ $site->get('hero_tag') }}</p>@endif
        <svg class="hero__doodle" viewBox="0 0 120 70" fill="none" aria-hidden="true"><path d="M4 58c14-8 30-8 38 0 7 7-3 14-9 7-9-11 7-40 38-46 16-3 30 1 40 9"/><path d="M100 14l12 10-15 6"/></svg>

        <div class="hero__card">
          <h1 class="hero__title">
            <span class="line"><span class="line__inner">{{ emphasis($site->get('hero_title_line1')) }}</span></span>
            @if($site->get('hero_title_line2'))<span class="line"><span class="line__inner">{{ emphasis($site->get('hero_title_line2')) }}</span></span>@endif
          </h1>
          <div class="hero__ctas">
            <a href="#appointment" class="btn-brand btn-brand--sq"><i class="bi bi-calendar2-check"></i><span>Book Online</span></a>
            <a href="#treatments" class="btn-line"><i class="bi bi-grid"></i> Our Services</a>
          </div>
          @if($site->get('hero_patients'))
          <div class="hero__clients">
            <div class="avatars" aria-hidden="true"><span>RA</span><span>SK</span><span><i class="bi bi-plus-lg"></i></span></div>
            <div><strong>{{ $site->get('hero_patients') }}</strong><small>Patients</small></div>
          </div>
          @endif
        </div>
      </div>

      <div class="hero__stage">
        @if($profile->hero_image)
        <img class="hero__doctor" src="{{ image_url($profile->hero_image, 'hero', 720) }}" srcset="{{ \App\Services\ImageService::srcset($profile->hero_image, 'hero') }}" sizes="(min-width: 992px) 580px, min(420px, 88vw)" alt="{{ $profile->full_name }} in a white coat, smiling" width="1208" height="1302" fetchpriority="high" decoding="async">
        @endif

        @if($site->get('hero_badge_title'))
        <div class="hero-chip hero-chip--exp">
          <span class="hero-chip__icon"><i class="bi bi-award"></i></span>
          <div><strong>{{ $site->get('hero_badge_title') }}</strong><small>{{ $site->get('hero_badge_text') }}</small></div>
        </div>
        @endif

        <div class="hero-chip hero-chip--doc">
          <span class="hero-chip__icon hero-chip__icon--round"><svg viewBox="0 0 40 40" aria-hidden="true"><path d="M20 7c-3-2.6-9.5-3.2-12 .8-2.6 4.2-.6 9.6.9 13.4 1.2 3.2 1.5 12.3 4.6 12.3 2.8 0 2.6-8.6 6.5-8.6s3.7 8.6 6.5 8.6c3.1 0 3.4-9.1 4.6-12.3 1.5-3.8 3.5-9.2.9-13.4C29.5 3.8 23 4.4 20 7Z"/></svg></span>
          <strong>{{ \Illuminate\Support\Str::of($site->get('clinic_name'))->before(' ') }}</strong><small>{{ \Illuminate\Support\Str::of($site->get('clinic_name'))->after(' ') }} Clinic</small>
        </div>

        @if($site->get('hero_note'))<p class="hero__note">{{ $site->get('hero_note') }}</p>@endif
      </div>
    </div>
  </div>
</section>

<!-- Marquee -->
@php($marquee = $site->lines('marquee_items'))
@if($marquee)
<div class="marquee" aria-hidden="true">
  <div class="marquee__track">
    @foreach([1, 2] as $copy)
      @foreach($marquee as $i => $item)<span>@if($i % 2)<em>{{ $item }}</em>@else{{ $item }}@endif</span><b></b>@endforeach
    @endforeach
  </div>
</div>
@endif

<!-- ============ STATS ============ -->
<section class="stats section-pad-sm" aria-label="Practice statistics">
  <div class="container">
    <div class="row g-4 stats__grid">
      @foreach([
        ['stat_years', 'bi-calendar-heart', 'Years Experience', '+'],
        ['stat_patients', 'bi-people', 'Patients Treated', '+'],
        ['stat_procedures', 'bi-check2-circle', 'Procedures Done', '+'],
        ['stat_certifications', 'bi-award', 'Certifications', ''],
      ] as [$key, $icon, $label, $suffix])
        @if($site->get($key) !== null)
        <div class="col-6 col-lg-3"><div class="stat"><span class="stat__icon"><i class="bi {{ $icon }}"></i></span><span class="stat__num"><span data-count="{{ (int) $site->get($key) }}">{{ number_format((int) $site->get($key)) }}</span>{{ $suffix }}</span><span class="stat__label">{{ $label }}</span></div></div>
        @endif
      @endforeach
    </div>
  </div>
</section>

<!-- ============ ABOUT ============ -->
<section class="about section-pad" id="about">
  <div class="container">
    <div class="row align-items-center gy-5 gx-lg-5">
      <div class="col-lg-5">
        <div class="about__media">
          @if($profile->about_image)
          <div class="about__img-main reveal-img">
            <x-picture :path="$profile->about_image" preset="photo" :alt="$profile->about_image_alt" sizes="(min-width: 992px) 560px, 92vw" />
          </div>
          @endif
          @if($profile->about_image_secondary)
          <div class="about__img-small reveal-img">
            <x-picture :path="$profile->about_image_secondary" preset="photo" :alt="$profile->about_image_secondary_alt" sizes="(min-width: 992px) 560px, 92vw" />
          </div>
          @endif
          @if($profile->years_experience)
          <div class="about__exp">
            <strong>{{ $profile->years_experience }}<sup>+</sup></strong>
            <span>years caring<br>for smiles</span>
          </div>
          @endif
        </div>
      </div>

      <div class="col-lg-7">
        <p class="eyebrow"><span class="eyebrow__line"></span>About the Dentist</p>
        <h2 class="section-title split-heading">{{ emphasis($site->get('about_title')) }}</h2>
        @if($profile->role_line)<p class="about__role">{{ $profile->role_line }}</p>@endif
        @foreach(preg_split('/\R{2,}/', trim((string) $profile->bio)) as $para)
          @if(trim($para) !== '')<p>{{ emphasis(trim($para)) }}</p>@endif
        @endforeach

        <div class="row g-3 about__facts">
          @if($profile->qualifications_summary)
          <div class="col-sm-6">
            <div class="fact">
              <i class="bi bi-mortarboard"></i>
              <div><h3>Qualifications</h3><p>{!! nl2br(e($profile->qualifications_summary)) !!}</p></div>
            </div>
          </div>
          @endif
          @if($profile->expertise_summary)
          <div class="col-sm-6">
            <div class="fact">
              <i class="bi bi-bullseye"></i>
              <div><h3>Expertise</h3><p>{!! nl2br(e($profile->expertise_summary)) !!}</p></div>
            </div>
          </div>
          @endif
        </div>

        @if($profile->philosophy_quote)
        <blockquote class="philosophy">
          <i class="bi bi-quote" aria-hidden="true"></i>
          <p>“{{ $profile->philosophy_quote }}”</p>
        </blockquote>
        @endif

        <div class="d-flex flex-wrap align-items-center gap-4">
          <a href="#experience" class="btn-brand magnetic"><span>Discover My Journey</span><i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ TREATMENTS ============ -->
<section class="treatments section-pad" id="treatments">
  <div class="container">
    <div class="section-head d-lg-flex align-items-end justify-content-between">
      <div>
        <p class="eyebrow"><span class="eyebrow__line"></span>Dental Treatments</p>
        <h2 class="section-title split-heading">{{ emphasis($site->get('treatments_title')) }}</h2>
      </div>
      <p class="section-head__aside">{{ $site->get('treatments_aside') }}</p>
    </div>

    <div class="row g-4 treat-grid">
      @foreach($home['treatments'] as $treatment)
        @include('site.partials.treatment-card', ['treatment' => $treatment, 'number' => $loop->iteration])
      @endforeach
    </div>
    @if($home['moreTreatments'])
    <div class="text-center mt-5"><a href="{{ route('treatments.index') }}" class="btn-brand"><span>View all treatments</span><i class="bi bi-arrow-right"></i></a></div>
    @endif
  </div>
</section>

<!-- ============ WHY CHOOSE ME ============ -->
<section class="why section-pad">
  <div class="container">
    <div class="row align-items-center gy-5 gx-lg-5">
      @if($home['sliderPhotos']->isNotEmpty())
      <div class="col-lg-6 order-lg-2">
        <div class="why__slider-wrap">
          <div class="swiper why-swiper">
            <div class="swiper-wrapper">
              @foreach($home['sliderPhotos'] as $photo)
              <figure class="swiper-slide why-slide"><x-picture :path="$photo->image" preset="photo" :alt="$photo->alt" sizes="(min-width: 992px) 560px, 92vw" /><figcaption><strong>{{ $photo->title }}</strong>@if($photo->subtitle)<span>{{ $photo->subtitle }}</span>@endif</figcaption></figure>
              @endforeach
            </div>
            <div class="why-swiper__bar">
              <div class="swiper-pagination why-swiper__pagination"></div>
              <div class="d-flex gap-2">
                <button type="button" class="why-nav why-nav--prev" aria-label="Previous photo"><i class="bi bi-arrow-left"></i></button>
                <button type="button" class="why-nav why-nav--next" aria-label="Next photo"><i class="bi bi-arrow-right"></i></button>
              </div>
            </div>
          </div>
          @if($site->get('why_badge'))<div class="why__badge"><i class="bi bi-heart-pulse"></i><span>{!! nl2br(e(\Illuminate\Support\Str::replaceFirst(' ', "\n", $site->get('why_badge')))) !!}</span></div>@endif
        </div>
      </div>
      @endif
      <div class="col-lg-6 order-lg-1">
        <p class="eyebrow"><span class="eyebrow__line"></span>Why Choose Me</p>
        <h2 class="section-title split-heading">{{ emphasis($site->get('why_title')) }}</h2>
        <ul class="why__list list-unstyled">
          @foreach($home['highlights'] as $h)
          <li><span class="why__ico"><i class="bi {{ $h->icon }}"></i></span><div><h3>{{ $h->title }}</h3><p>{{ $h->text }}</p></div></li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ============ EXPERIENCE & QUALIFICATIONS ============ -->
<section class="experience section-pad" id="experience">
  <div class="container">
    <div class="text-center section-head section-head--center">
      <p class="eyebrow justify-content-center"><span class="eyebrow__line"></span>Experience &amp; Qualifications</p>
      <h2 class="section-title split-heading">{{ emphasis($site->get('experience_title')) }}</h2>
    </div>

    @if($feature = $home['featuredCredential'])
    <article class="xp-feature">
      @if($feature->certificate_image)
      <div class="xp-feature__media reveal-img">
        <x-picture :path="$feature->certificate_image" preset="certificate" :alt="$feature->title.' '.$feature->title_emphasis.' certificate'" sizes="(min-width: 992px) 560px, 92vw" />
        @if($feature->completed_on)<span class="xp-feature__year">{{ $feature->completed_on->year }}</span>@endif
      </div>
      @endif
      <div class="xp-feature__body">
        <span class="xp-chip xp-chip--sun"><i class="bi {{ $feature->icon }}"></i> Latest · {{ $feature->displayLabel() }}</span>
        <h3>{{ $feature->title }}@if($feature->title_emphasis) <em>{{ $feature->title_emphasis }}</em>@endif</h3>
        @if($feature->description)<p>{{ $feature->description }}</p>@endif
        <ul class="xp-feature__meta list-unstyled">
          @if($feature->completed_on)<li><i class="bi bi-calendar-event"></i><span><strong>{{ $feature->completed_on->format('j F Y') }}</strong>{{ $feature->date_note }}</span></li>@endif
          @if($feature->location)<li><i class="bi bi-geo-alt"></i><span><strong>{{ $feature->location }}</strong>{{ $feature->institution }}</span></li>@endif
          @if($feature->certificate_image)<li><i class="bi bi-patch-check"></i><span><strong>Certificate</strong>of Achievement</span></li>@endif
        </ul>
      </div>
    </article>
    @endif

    <ol class="xp-steps list-unstyled">
      @foreach($home['timeline'] as $step)
      <li class="xp-step">
        <span class="xp-step__node{{ $loop->last ? ' xp-step__node--sun' : '' }}"><i class="bi {{ $step->icon }}"></i></span>
        <span class="xp-step__label">{{ $step->displayLabel() }}</span>
        <h3>{{ $step->title }}</h3>
        @if($step->description)<p>{{ $step->description }}</p>@endif
      </li>
      @endforeach
    </ol>
  </div>
</section>

@if($home['smileCases']->isNotEmpty())
<!-- ============ SMILE GALLERY ============ -->
<section class="gallery section-pad" id="gallery">
  <div class="container">
    <div class="section-head d-lg-flex align-items-end justify-content-between">
      <div>
        <p class="eyebrow"><span class="eyebrow__line"></span>Smile Transformations</p>
        <h2 class="section-title split-heading">{{ emphasis($site->get('gallery_title')) }}</h2>
      </div>
      @if($home['galleryFilters']->count() > 1)
      <div class="filters" role="group" aria-label="Filter gallery by treatment">
        <button type="button" class="filter-btn is-active" data-filter="all" aria-pressed="true">All</button>
        @foreach($home['galleryFilters'] as $slug => $name)
        <button type="button" class="filter-btn" data-filter="{{ $slug }}" aria-pressed="false">{{ $name }}</button>
        @endforeach
      </div>
      @endif
    </div>

    <p class="demo-note"><i class="bi bi-info-circle"></i> Real patient results, published with each patient's written consent. Individual results vary.</p>

    <div class="row g-4 gallery__grid">
      @foreach($home['smileCases'] as $case)
      <div class="col-md-6 gallery__item" data-category="{{ $case->treatment?->slug ?? 'other' }}">
        <figure class="ba-card">
          <div class="compare">
            <x-picture class="compare__after" :path="$case->after_image" preset="compare" :alt="'After: '.$case->title" sizes="(min-width: 992px) 540px, (min-width: 768px) 45vw, 92vw" />
            <div class="compare__before"><x-picture :path="$case->before_image" preset="compare" :alt="'Before: '.$case->title" sizes="(min-width: 992px) 540px, (min-width: 768px) 45vw, 92vw" /></div>
            <span class="compare__tag compare__tag--before">Before</span><span class="compare__tag compare__tag--after">After</span>
            <span class="compare__handle" aria-hidden="true"><i class="bi bi-chevron-left"></i><i class="bi bi-chevron-right"></i></span>
            <input type="range" class="compare__range" min="0" max="100" value="50" aria-label="Before and after comparison — {{ $case->title }}">
          </div>
          <figcaption><div><span class="ba-card__cat">{{ $case->treatment?->name ?? 'Smile case' }}</span><h3>{{ $case->title }}@if($case->duration) · {{ $case->duration }}@endif</h3></div><button type="button" class="zoom-btn" data-lightbox="{{ image_url($case->after_image, 'compare') }}" data-caption="{{ $case->treatment?->name ?? $case->title }} — after treatment" aria-label="Open image in lightbox"><i class="bi bi-arrows-angle-expand"></i></button></figcaption>
        </figure>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Image viewer" hidden>
  <button type="button" class="lightbox__close" aria-label="Close image viewer"><i class="bi bi-x-lg"></i></button>
  <figure class="lightbox__figure">
    <img src="data:," alt="">
    <figcaption class="lightbox__caption"></figcaption>
  </figure>
</div>
@endif

<!-- ============ CLINIC SHOWCASE ============ -->
<section class="clinic section-pad">
  <div class="container">
    <div class="row gy-4 align-items-end mb-4 mb-lg-5">
      <div class="col-lg-6">
        <p class="eyebrow"><span class="eyebrow__line"></span>The Clinic</p>
        <h2 class="section-title split-heading">{{ emphasis($site->get('clinic_title')) }}</h2>
      </div>
      <div class="col-lg-5 offset-lg-1">
        <p class="mb-3">{{ $site->get('clinic_intro') }}</p>
        <p class="clinic__loc"><i class="bi bi-geo-alt-fill"></i> {{ rtrim($site->get('address_line1'), ',') }}, {{ $site->get('address_short') }}</p>
      </div>
    </div>

    <div class="bento">
      @foreach($home['clinicPhotos'] as $photo)
      <figure class="bento__item{{ $loop->first ? ' bento__item--lg' : '' }} reveal-img"><x-picture :path="$photo->image" preset="photo" :alt="$photo->alt" sizes="(min-width: 992px) 560px, 92vw" /><figcaption>{{ $photo->category?->name ?? $photo->title }}</figcaption></figure>
      @endforeach
      <div class="bento__item bento__item--cta">
        <ul class="list-unstyled">
          @foreach($site->lines('clinic_amenities') as $amenity)
          <li><i class="bi bi-check2-circle"></i> {{ $amenity }}</li>
          @endforeach
        </ul>
        <a href="#contact" class="btn-brand"><span>Visit the Clinic</span><i class="bi bi-geo-alt"></i></a>
      </div>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIALS (Google-review style) ============ -->
<section class="testimonials section-pad" id="testimonials">
  <div class="container">
    <div class="g-head">
      <div>
        <p class="eyebrow"><span class="eyebrow__line"></span>Patient Stories</p>
        <h2 class="section-title split-heading">{{ emphasis($site->get('testimonials_title')) }}</h2>
      </div>
      <div class="g-summary">
        <svg class="g-logo" viewBox="0 0 48 48" aria-hidden="true"><use href="#gmark"/></svg>
        @if($rating = $site->get('google_rating'))
        <div>
          <div class="g-summary__row"><strong>{{ $rating }}</strong>@include('site.partials.stars', ['rating' => (float) $rating])</div>
          <small>Google Reviews @if($site->get('google_review_count'))· {{ number_format((int) $site->get('google_review_count')) }} reviews @endif</small>
        </div>
        @else
        <div><div class="g-summary__row"><strong>Google</strong></div><small>Share your experience</small></div>
        @endif
        @if($site->get('google_review_url'))<a href="{{ $site->get('google_review_url') }}" target="_blank" rel="noopener" class="g-btn">Write a review</a>@endif
      </div>
    </div>

    @if($home['testimonials']->isNotEmpty())
    <div class="swiper testimonial-swiper">
      <div class="swiper-wrapper">
        @foreach($home['testimonials'] as $review)
        <div class="swiper-slide"><article class="g-review"><header>@if($review->avatar)<img class="g-avatar" src="{{ image_url($review->avatar, 'avatar') }}" alt="" width="44" height="44" loading="lazy">@else<span class="g-avatar g-avatar--{{ ['a', 'b', 'c', 'd'][$loop->index % 4] }}">{{ mb_strtoupper(mb_substr($review->display_name, 0, 1)) }}</span>@endif<div><strong>{{ $review->display_name }}</strong><small>{{ $review->reviewed_on?->diffForHumans() ?? '' }}</small></div>@if($review->source === 'google')<svg class="g-review__g" viewBox="0 0 48 48" aria-hidden="true"><use href="#gmark"/></svg>@endif</header>@include('site.partials.stars', ['rating' => $review->rating])<p>{{ $review->body }}</p>@if($review->treatment)<span class="g-review__tag">{{ $review->treatment->name }}</span>@endif</article></div>
        @endforeach
      </div>
      <div class="g-controls">
        <button type="button" class="swiper-nav swiper-nav--prev" aria-label="Previous review"><i class="bi bi-arrow-left"></i></button>
        <div class="swiper-pagination"></div>
        <button type="button" class="swiper-nav swiper-nav--next" aria-label="Next review"><i class="bi bi-arrow-right"></i></button>
      </div>
    </div>
    @else
    <p class="demo-note"><i class="bi bi-chat-heart"></i> Patient reviews will appear here soon. Visited us recently? We'd love to hear about your experience on Google.</p>
    @endif

    <!-- Reusable Google "G" mark -->
    <svg width="0" height="0" class="position-absolute" aria-hidden="true"><symbol id="gmark" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/></symbol></svg>
  </div>
</section>

@include('site.partials.booking')

@if($home['posts']->isNotEmpty())
<!-- ============ BLOG ============ -->
<section class="blog section-pad" id="blog">
  <div class="container">
    <div class="section-head d-lg-flex align-items-end justify-content-between">
      <div>
        <p class="eyebrow"><span class="eyebrow__line"></span>Dental Health Blog</p>
        <h2 class="section-title split-heading">{{ emphasis($site->get('blog_title')) }}</h2>
      </div>
      <a href="{{ route('blog.index') }}" class="link-arrow">View all articles <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      @foreach($home['posts'] as $post)
        @include('site.partials.post-card', ['post' => $post])
      @endforeach
    </div>
  </div>
</section>
@endif

@if($home['faqs']->isNotEmpty())
<!-- ============ FAQ ============ -->
<section class="faq section-pad">
  <div class="container">
    <div class="row gy-5 gx-lg-5">
      <div class="col-lg-4">
        <div class="faq__intro">
          <p class="eyebrow"><span class="eyebrow__line"></span>FAQ</p>
          <h2 class="section-title split-heading">{{ emphasis($site->get('faq_title')) }}</h2>
          <p>{{ $site->get('faq_intro') }}</p>
          <a href="#" class="btn-whatsapp btn-whatsapp--outline js-whatsapp" data-wa-text="Hello, I have a question about dental treatment."><i class="bi bi-whatsapp"></i> Ask a Question</a>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="accordion faq-acc" id="faqAcc">
          @foreach($home['faqs'] as $faq)
          <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button{{ $loop->first ? '' : ' collapsed' }}" type="button" data-collapse="#q{{ $faq->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="q{{ $faq->id }}">{{ $faq->question }}</button></h3><div id="q{{ $faq->id }}" class="accordion-collapse collapse{{ $loop->first ? ' show' : '' }}" data-collapse-parent="#faqAcc"><div class="accordion-body">{!! nl2br(e($faq->answer)) !!}</div></div></div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
@endif

@include('site.partials.contact')

@endsection
