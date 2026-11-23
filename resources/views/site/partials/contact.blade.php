@php
    $socials = array_filter(['facebook' => $site->get('facebook_url'), 'instagram' => $site->get('instagram_url'), 'youtube' => $site->get('youtube_url')]);
    $mapQuery = $site->get('map_query', $site->get('address_line2'));
@endphp
<!-- ============ CONTACT ============ -->
<section class="contact section-pad" id="contact">
  <div class="container">
    <div class="text-center section-head section-head--center">
      <p class="eyebrow justify-content-center"><span class="eyebrow__line"></span>Contact</p>
      <h2 class="section-title split-heading">{{ emphasis($site->get('contact_title')) }}</h2>
    </div>

    <div class="row g-4">
      <div class="col-lg-5">
        <div class="contact__cards">
          <div class="c-card">
            <i class="bi bi-geo-alt"></i>
            <div><h3>Clinic Address</h3><p>{{ $site->get('address_line1') }}@if($site->get('address_line2'))<br>{{ $site->get('address_line2') }}@endif</p></div>
          </div>
          <div class="c-card">
            <i class="bi bi-telephone"></i>
            <div><h3>Call Us</h3><p>@foreach(array_filter([$site->get('phone_primary'), $site->get('phone_secondary')]) as $phone)<a href="{{ tel_href($phone) }}">{{ $phone }}</a>@unless($loop->last)<br>@endunless @endforeach @if($site->get('email'))<br><a href="mailto:{{ $site->get('email') }}">{{ $site->get('email') }}</a>@endif</p></div>
          </div>
          @if($chamberHours)
          <div class="c-card">
            <i class="bi bi-clock"></i>
            <div><h3>Chamber Hours</h3><p>@foreach($chamberHours as [$name, $range]){{ $name }}: {{ $range }}@unless($loop->last)<br>@endunless @endforeach</p></div>
          </div>
          @endif
          <div class="c-card c-card--cta">
            <a href="#" class="btn-whatsapp js-whatsapp" data-wa-text="Hello, I'd like to know more about {{ $site->get('clinic_name') }}."><i class="bi bi-whatsapp"></i> Chat on WhatsApp</a>
            @if($socials)
            <div class="socials">
              @foreach($socials as $network => $url)
              <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}"><i class="bi bi-{{ $network }}"></i></a>
              @endforeach
            </div>
            @endif
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="map-card">
          <iframe title="Map showing {{ $site->get('clinic_name') }} at {{ $site->get('address_short') }}" src="https://maps.google.com/maps?q={{ rawurlencode($mapQuery) }}&z=16&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          <div class="map-card__bar">
            <div><strong>{{ $site->get('clinic_name') }}</strong><span>{{ $site->get('address_short') }}</span></div>
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($mapQuery) }}" target="_blank" rel="noopener" class="btn-brand btn-brand--sm"><span>Get Directions</span><i class="bi bi-sign-turn-right"></i></a>
          </div>
        </div>
      </div>

      <div class="col-12">
        <form class="bk-card contact-form" id="contactForm" action="{{ route('contact.store') }}#contactForm" method="post" novalidate>
          @csrf
          <h3 class="bk-title">Send us a message</h3>
          @if(session('contact_status'))
          <div class="form-success mb-3" role="status"><i class="bi bi-check2-circle"></i><div><strong>Message sent.</strong><p class="mb-0">{{ session('contact_status') }}</p></div></div>
          @endif
          <div class="row g-3">
            <div class="col-md-4">
              <label for="cName" class="form-label">Full Name *</label>
              <input type="text" class="form-control @error('name', 'contact') is-invalid @enderror" id="cName" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
              <div class="invalid-feedback">{{ $errors->contact->first('name') ?: 'Please enter your name.' }}</div>
            </div>
            <div class="col-md-4">
              <label for="cPhone" class="form-label">Phone Number</label>
              <input type="tel" class="form-control @error('phone', 'contact') is-invalid @enderror" id="cPhone" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" maxlength="20" autocomplete="tel">
              <div class="invalid-feedback">{{ $errors->contact->first('phone') ?: 'Please enter a valid phone number.' }}</div>
            </div>
            <div class="col-md-4">
              <label for="cEmail" class="form-label">Email Address</label>
              <input type="email" class="form-control @error('email', 'contact') is-invalid @enderror" id="cEmail" name="email" value="{{ old('email') }}" maxlength="190" autocomplete="email">
              <div class="invalid-feedback">{{ $errors->contact->first('email') ?: 'Please enter a valid email, or leave it empty.' }}</div>
            </div>
            <div class="col-12">
              <label for="cMessage" class="form-label">Message *</label>
              <textarea class="form-control @error('message', 'contact') is-invalid @enderror" id="cMessage" name="message" rows="4" required minlength="10" maxlength="2000">{{ old('message') }}</textarea>
              <div class="invalid-feedback">{{ $errors->contact->first('message') ?: 'Please write a short message (at least 10 characters).' }}</div>
            </div>
            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
            <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-3">
              <small class="text-muted-2"><i class="bi bi-lock"></i> Please don't share medical records here. Give a phone number or email so we can reply.</small>
              <button type="submit" class="btn-brand"><span>Send Message</span><i class="bi bi-send"></i></button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
