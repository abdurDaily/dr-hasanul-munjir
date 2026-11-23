@php($selectedTreatment = $selectedTreatment ?? null)
<!-- ============ APPOINTMENT ============ -->
<!-- Booking: visit type → calendar & live slots → patient details, with a live summary sidebar.
     Availability comes from the server; the server re-checks the slot when the request is submitted. -->
<section class="appointment section-pad" id="appointment">
  <div class="container">
    <div class="booking-head">
      <div>
        <p class="eyebrow"><span class="eyebrow__line"></span>Book a Visit</p>
        <h2 class="section-title split-heading">{{ emphasis($site->get('booking_title')) }}</h2>
      </div>
      @if($chamberHours)
      <p class="booking-head__aside"><i class="bi bi-clock-history"></i> Chamber hours: @foreach($chamberHours as [$name, $range])<strong>{{ $range }}</strong>@unless($loop->last) &amp; @endunless @endforeach</p>
      @endif
    </div>

    <form class="row g-4 booking needs-validation" id="appointmentForm" action="{{ route('appointments.store') }}" data-availability-url="{{ route('booking.availability') }}" method="post" novalidate>
      @csrf
      <!-- ===== Left: steps ===== -->
      <div class="col-lg-8">

        <div class="bk-card bk-card--row">
          <fieldset>
            <legend class="bk-label">How would you like to meet?</legend>
            <div class="seg" role="radiogroup">
              @foreach($appointmentTypes as $type)
              <input type="radio" class="btn-check" name="appointment_type_id" id="vt{{ $type->id }}" value="{{ $type->id }}" data-name="{{ $type->name }}" data-icon="{{ $type->icon }}" data-note="{{ $type->note }}" @checked($loop->first)>
              <label for="vt{{ $type->id }}"><i class="bi {{ $type->icon }}"></i> {{ $type->short_label }}</label>
              @endforeach
            </div>
          </fieldset>
          <div class="bk-sooner">
            <span>Need something sooner?</span>
            <button type="button" class="btn-soft" id="findEarliest"><i class="bi bi-lightning-charge"></i> Find Earliest Available</button>
          </div>
        </div>

        <div class="bk-card bk-card--split">
          <div class="cal" id="calendar">
            <div class="cal__head">
              <h3 class="cal__title" id="calTitle" aria-live="polite">Loading…</h3>
              <div class="cal__nav">
                <button type="button" id="calPrev" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
                <button type="button" id="calNext" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
              </div>
            </div>
            <div class="cal__week" aria-hidden="true"><span>Sa</span><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span></div>
            <div class="cal__grid" id="calGrid" role="group" aria-labelledby="calTitle"></div>
            <div class="cal__legend"><span><i class="dot dot--on"></i>Available</span><span><i class="dot"></i>Unavailable</span></div>
          </div>
          <div class="slots" id="slots">
            <h3 class="slots__date" id="slotsDate">Choose a date</h3>
            <p class="slots__meta" id="slotsMeta"></p>
            <div class="slots__tz"><i class="bi bi-globe2"></i> Bangladesh Time (GMT+6)</div>
            <div id="slotsGroups" aria-live="polite"></div>
            <p class="slots__empty" id="slotsEmpty" hidden><i class="bi bi-calendar-x"></i> <span>No appointments are available on this day. Please pick another date.</span></p>
          </div>
          <input type="hidden" name="date" id="fDate">
          <input type="hidden" name="time" id="fTime">
          <div class="bk-error" id="slotError" hidden><i class="bi bi-exclamation-circle"></i> <span>Please select a date and an available time slot.</span></div>
        </div>

        <div class="bk-card" id="patientDetails">
          <h3 class="bk-title">Patient details</h3>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="fName" class="form-label">Full Name *</label>
              <input type="text" class="form-control" id="fName" name="patient_name" placeholder="Your full name" required minlength="2" maxlength="120" autocomplete="name">
              <div class="invalid-feedback" data-for="patient_name">Please enter your full name.</div>
            </div>
            <div class="col-md-6">
              <label for="fPhone" class="form-label">Phone Number *</label>
              <input type="tel" class="form-control" id="fPhone" name="patient_phone" placeholder="01XXXXXXXXX" required pattern="^\+?[0-9\s\-]{10,16}$" maxlength="20" autocomplete="tel">
              <div class="invalid-feedback" data-for="patient_phone">Please enter a valid phone number.</div>
            </div>
            <div class="col-md-6">
              <label for="fEmail" class="form-label">Email Address <small class="text-muted-2">(optional)</small></label>
              <input type="email" class="form-control" id="fEmail" name="patient_email" placeholder="you@example.com" maxlength="190" autocomplete="email">
              <div class="invalid-feedback" data-for="patient_email">Please enter a valid email, or leave it empty.</div>
            </div>
            <div class="col-md-6">
              <label for="fTreatment" class="form-label">Select Treatment *</label>
              <select class="form-select" id="fTreatment" name="treatment_id" required>
                <option value="" disabled @selected(! $selectedTreatment)>Choose a treatment</option>
                @foreach($bookingTreatments as $t)
                <option value="{{ $t->id }}" @selected($selectedTreatment?->id === $t->id)>{{ $t->name }}</option>
                @endforeach
                <option value="consultation">Not sure — consultation</option>
              </select>
              <div class="invalid-feedback" data-for="treatment_id">Please select a treatment.</div>
            </div>
            <div class="col-12">
              <label for="fClinic" class="form-label">Preferred Clinic *</label>
              <select class="form-select" id="fClinic" disabled>
                <option selected>{{ $site->get('clinic_name') }} — {{ $site->get('address_short') }}</option>
              </select>
            </div>
            <div class="col-12">
              <label for="fMsg" class="form-label">Message</label>
              <textarea class="form-control" id="fMsg" name="message" rows="3" maxlength="1000" placeholder="Tell us briefly about your concern (optional)"></textarea>
              <div class="invalid-feedback" data-for="message">Please keep your message under 1000 characters.</div>
            </div>
            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
          </div>
        </div>

        @php($policies = $site->lines('booking_policies'))
        <div class="bk-policy">
          <span class="bk-policy__icon"><i class="bi bi-info-lg"></i></span>
          <div>
            <h3>Appointment Policies</h3>
            <ul class="list-unstyled">
              @foreach($policies as $policy)
              @php([$head, $body] = str_contains($policy, ':') ? array_map('trim', explode(':', $policy, 2)) : [null, $policy])
              <li><i class="bi bi-check2"></i><span>@if($head)<strong>{{ $head }}:</strong> @endif{{ $body }}</span></li>
              @endforeach
              <li><i class="bi bi-check2"></i><span><strong>Confirmation:</strong> Your request stays pending until the clinic confirms it.</span></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- ===== Right: live booking summary (sticky as one unit) ===== -->
      <div class="col-lg-4">
        <div class="summary-wrap">
          <aside class="summary" aria-label="Booking summary">
            <div class="summary__top">
              <h3 class="summary__title">Booking Summary</h3>
              <div class="summary__doc">
                @if($profile->portrait_image)<img src="{{ image_url($profile->portrait_image, 'portrait', 160) }}" alt="{{ $profile->full_name }}" width="64" height="64" loading="lazy">@endif
                <div>
                  <strong>{{ $profile->full_name }}</strong>
                  <span>{{ $profile->credentials_line }}</span>
                  @if($profile->specialty_line)<small><i class="bi bi-star-fill"></i> {{ $profile->specialty_line }}</small>@endif
                </div>
              </div>
            </div>
            <div class="summary__body">
              <ul class="summary__list list-unstyled">
                <li><i class="bi bi-calendar3"></i><div><small>Date &amp; Time</small><strong id="sumDate">Not selected</strong><span id="sumTime">Pick a slot from the calendar</span></div></li>
                <li><i class="bi {{ $appointmentTypes->first()?->icon }}"></i><div><small>Visit Type</small><strong id="sumVisit">{{ $appointmentTypes->first()?->name }}</strong><span id="sumVisitNote">{{ $appointmentTypes->first()?->note }}</span></div></li>
                <li><i class="bi bi-clipboard2-pulse"></i><div><small>Treatment</small><strong id="sumTreatment">{{ $selectedTreatment?->name ?? 'Not selected' }}</strong></div></li>
              </ul>
              <div class="summary__fee">
                <span>Consultation fee</span>
                <strong>Confirmed by clinic</strong>
              </div>
              <button type="submit" class="btn-brand btn-brand--accent w-100 justify-content-center"><span class="btn-label">Request Appointment</span><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i class="bi bi-arrow-right"></i></button>
              <a href="#" class="summary__wa js-whatsapp" data-wa-text="Hello {{ $profile->full_name }}, I'd like to book an appointment."><i class="bi bi-whatsapp"></i> Or book via WhatsApp</a>

              <div class="bk-error mt-3" id="formError" role="alert" hidden><i class="bi bi-exclamation-circle"></i> <span></span></div>
              <div class="form-success" id="formSuccess" role="status" aria-live="polite" tabindex="-1" hidden>
                <i class="bi bi-check2-circle"></i>
                <div><strong>Request received — reference <span id="bookingRef"></span></strong><p class="mb-0" id="bookingMsg"></p></div>
              </div>
            </div>
            <ul class="trust list-unstyled" aria-label="Clinic assurances">
              <li><i class="bi bi-shield-check"></i>Sterilised</li>
              @if($profile->bmdc_reg_no)<li><i class="bi bi-patch-check"></i>BMDC {{ $profile->bmdc_reg_no }}</li>@endif
              <li><i class="bi bi-lock"></i>Private</li>
            </ul>
          </aside>
        </div>
      </div>
    </form>
  </div>
</section>
