@php
    $to = fn (string $id) => $isHome ? "#{$id}" : route('home')."#{$id}";
    $socials = array_filter(['facebook' => $site->get('facebook_url'), 'instagram' => $site->get('instagram_url'), 'youtube' => $site->get('youtube_url')]);
@endphp
<!-- ============ FOOTER ============ -->
<footer class="footer">
  <div class="container">
    <div class="footer__cta">
      <h2>{{ emphasis($site->get('footer_cta_title')) }}</h2>
      <a href="{{ $isHome || request()->routeIs('treatments.show') ? '#appointment' : route('home').'#appointment' }}" class="btn-brand btn-brand--accent magnetic"><span>Book Appointment</span><i class="bi bi-arrow-up-right"></i></a>
    </div>

    <div class="row gy-5 footer__main">
      <div class="col-lg-4">
        <a class="brand brand--light" href="{{ $isHome ? '#home' : route('home') }}">
          <span class="brand__mark" aria-hidden="true"><svg viewBox="0 0 40 40"><path d="M20 7c-3-2.6-9.5-3.2-12 .8-2.6 4.2-.6 9.6.9 13.4 1.2 3.2 1.5 12.3 4.6 12.3 2.8 0 2.6-8.6 6.5-8.6s3.7 8.6 6.5 8.6c3.1 0 3.4-9.1 4.6-12.3 1.5-3.8 3.5-9.2.9-13.4C29.5 3.8 23 4.4 20 7Z"/></svg></span>
          <span class="brand__text"><strong>{{ $profile->full_name }}</strong><small>{{ $profile->credentials_line }}</small></span>
        </a>
        <p class="footer__intro">{{ $site->get('footer_intro') }}</p>
        <div class="socials socials--light">
          @foreach($socials as $network => $url)
          <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}"><i class="bi bi-{{ $network }}"></i></a>
          @endforeach
          <a href="#" class="js-whatsapp" data-wa-text="Hello {{ $profile->full_name }}!" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h3 class="footer__h">Quick Links</h3>
        <ul class="list-unstyled footer__links">
          <li><a href="{{ $to('about') }}">About</a></li><li><a href="{{ route('treatments.index') }}">Treatments</a></li><li><a href="{{ $to('experience') }}">Experience</a></li><li><a href="{{ $to('gallery') }}">Smile Gallery</a></li><li><a href="{{ route('blog.index') }}">Blog</a></li><li><a href="{{ $to('contact') }}">Contact</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h3 class="footer__h">Treatments</h3>
        <ul class="list-unstyled footer__links">
          @foreach($footerTreatments as $t)
          <li><a href="{{ route('treatments.show', $t->slug) }}">{{ $t->name }}</a></li>
          @endforeach
        </ul>
      </div>
      <div class="col-lg-4">
        <h3 class="footer__h">Newsletter</h3>
        <p class="footer__intro mb-3">{{ $site->get('newsletter_text') }}</p>
        <form class="newsletter" id="newsletterForm" action="{{ route('newsletter.store') }}" method="post" novalidate>
          @csrf
          <label for="nlEmail" class="visually-hidden">Email address</label>
          <input type="email" id="nlEmail" name="email" placeholder="Your email address" required autocomplete="email" maxlength="190">
          <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
          <button type="submit" aria-label="Subscribe"><i class="bi bi-arrow-right"></i></button>
        </form>
        <p class="newsletter__msg{{ $errors->newsletter->any() ? ' is-error' : '' }}" id="newsletterMsg" role="status" aria-live="polite">{{ session('newsletter_status') ?? $errors->newsletter->first('email') }}</p>
        <ul class="list-unstyled footer__contact">
          @foreach(array_filter([$site->get('phone_primary'), $site->get('phone_secondary')]) as $phone)
          <li><i class="bi bi-telephone"></i><a href="{{ tel_href($phone) }}">{{ $phone }}</a></li>
          @endforeach
          <li><i class="bi bi-geo-alt"></i><span>{{ $site->get('address_line2') }}</span></li>
        </ul>
      </div>
    </div>

    <div class="footer__bottom">
      <p class="mb-0">© {{ now()->year }} {{ $profile->full_name }} · {{ $site->get('clinic_name') }}@if($profile->bmdc_reg_no) · BMDC Reg. No. {{ $profile->bmdc_reg_no }}@endif</p>
      <div class="d-flex gap-4"><a href="{{ route('pages.privacy') }}">Privacy Policy</a><a href="{{ route('pages.terms') }}">Terms of Use</a></div>
    </div>
  </div>
</footer>
