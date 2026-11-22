/* ==========================================================================
   interactions.js — gallery filter, compare slider, lightbox, testimonials,
   appointment form, newsletter
   ========================================================================== */
(() => {
  'use strict';

  /* ---------- Gallery filtering ---------- */
  const filterBtns = document.querySelectorAll('.filter-btn');
  const galleryItems = document.querySelectorAll('.gallery__item');

  filterBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const filter = btn.dataset.filter;
      filterBtns.forEach((b) => {
        const on = b === btn;
        b.classList.toggle('is-active', on);
        b.setAttribute('aria-pressed', String(on));
      });

      galleryItems.forEach((item) => {
        const show = filter === 'all' || item.dataset.category === filter;
        item.classList.toggle('is-hidden', !show);
      });

      const visible = [...galleryItems].filter((i) => !i.classList.contains('is-hidden'));
      if (window.gsap && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        gsap.fromTo(visible, { autoAlpha: 0, y: 24 }, { autoAlpha: 1, y: 0, duration: .5, stagger: .08, ease: 'power3.out' });
      }
      if (window.ScrollTrigger) ScrollTrigger.refresh();
    });
  });

  /* ---------- Before / after compare slider (range input = keyboard + touch accessible) ---------- */
  document.querySelectorAll('.compare').forEach((wrap) => {
    const range = wrap.querySelector('.compare__range');
    const update = () => wrap.style.setProperty('--pos', `${range.value}%`);
    range.addEventListener('input', update);
    update();
  });

  /* ---------- Lightbox ---------- */
  const lb = document.getElementById('lightbox');
  const lbImg = lb.querySelector('img');
  const lbCap = lb.querySelector('.lightbox__caption');
  const lbClose = lb.querySelector('.lightbox__close');
  let lastFocus = null;

  const openLightbox = (src, caption) => {
    lastFocus = document.activeElement;
    lbImg.src = src;
    lbImg.alt = caption;
    lbCap.textContent = caption;
    lb.hidden = false;
    document.body.style.overflow = 'hidden';
    lbClose.focus();
    if (window.gsap) gsap.fromTo(lb.querySelector('.lightbox__figure'), { scale: .94, autoAlpha: 0 }, { scale: 1, autoAlpha: 1, duration: .45, ease: 'power3.out' });
  };
  const closeLightbox = () => {
    lb.hidden = true;
    lbImg.src = '';
    document.body.style.overflow = '';
    if (lastFocus) lastFocus.focus();
  };

  document.querySelectorAll('[data-lightbox]').forEach((btn) => {
    btn.addEventListener('click', () => openLightbox(btn.dataset.lightbox, btn.dataset.caption || ''));
  });
  lbClose.addEventListener('click', closeLightbox);
  lb.addEventListener('click', (e) => { if (e.target === lb) closeLightbox(); });
  document.addEventListener('keydown', (e) => {
    if (lb.hidden) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'Tab') { e.preventDefault(); lbClose.focus(); } // single focusable element: trap focus
  });

  /* ---------- Sliders ---------- */
  if (window.Swiper) {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Google-style reviews
    new Swiper('.testimonial-swiper', {
      slidesPerView: 1,
      spaceBetween: 20,
      loop: true,
      grabCursor: true,
      speed: 600,
      autoplay: reduce ? false : { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
      pagination: { el: '.testimonial-swiper .swiper-pagination', clickable: true },
      navigation: { nextEl: '.testimonial-swiper .swiper-nav--next', prevEl: '.testimonial-swiper .swiper-nav--prev' },
      a11y: { enabled: true },
      breakpoints: { 768: { slidesPerView: 2, spaceBetween: 24 }, 1200: { slidesPerView: 3, spaceBetween: 24 } },
    });

    // "Why choose me" photo slider
    new Swiper('.why-swiper', {
      effect: 'fade',
      fadeEffect: { crossFade: true },
      loop: true,
      speed: 900,
      autoplay: reduce ? false : { delay: 4500, disableOnInteraction: false, pauseOnMouseEnter: true },
      pagination: { el: '.why-swiper__pagination', clickable: true },
      navigation: { nextEl: '.why-nav--next', prevEl: '.why-nav--prev' },
      a11y: { enabled: true },
    });
  }

  /* ---------- Treatment "Learn more" → preselect treatment in form ---------- */
  const treatmentSelect = document.getElementById('fTreatment');
  document.querySelectorAll('[data-treatment]').forEach((link) => {
    link.addEventListener('click', () => {
      const opt = [...treatmentSelect.options].find((o) => o.text === link.dataset.treatment);
      if (opt) {
        treatmentSelect.value = opt.value || opt.text;
        treatmentSelect.dispatchEvent(new Event('change'));
      }
    });
  });

  /* ---------- Appointment booking (calendar, slots, live summary) ---------- */
  const form = document.getElementById('appointmentForm');
  const success = document.getElementById('formSuccess');
  const submitBtn = form.querySelector('button[type="submit"]');

  // Chamber schedule (from the visiting card). Times are minutes after midnight; slots are 30 min.
  const SESSIONS = {
    morning: { start: 10 * 60 + 30, end: 14 * 60 },
    evening: { start: 17 * 60 + 30, end: 22 * 60 },
  };
  const SLOT_MIN = 30;
  const CLOSED_DAYS = [];         // weekday numbers the chamber is closed (0 = Sun … 5 = Fri); none listed on the visiting card
  const BOOKING_WINDOW_DAYS = 60; // how far ahead patients can book

  const VISIT_NOTES = {
    'In-Clinic Visit': ['bi-hospital', 'Munjir Dental Care, Bahaddarhat'],
    'Phone Consultation': ['bi-telephone', 'The doctor will call your number'],
    'Video Consultation': ['bi-camera-video', 'A video link will be sent to you'],
  };

  const $ = (id) => document.getElementById(id);
  const calGrid = $('calGrid');
  const calTitle = $('calTitle');
  const calPrev = $('calPrev');
  const calNext = $('calNext');
  const slotsMorning = $('slotsMorning');
  const slotsEvening = $('slotsEvening');
  const slotsEmpty = $('slotsEmpty');
  const slotError = $('slotError');
  const fDate = $('fDate');
  const fTime = $('fTime');

  const startOfDay = (d) => { const x = new Date(d); x.setHours(0, 0, 0, 0); return x; };
  const addDays = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
  const sameDay = (a, b) => a && b && a.toDateString() === b.toDateString();
  const toISO = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  const fmtTime = (m) => {
    const h = Math.floor(m / 60), min = m % 60, h12 = ((h + 11) % 12) + 1;
    return `${h12}:${String(min).padStart(2, '0')} ${h < 12 ? 'AM' : 'PM'}`;
  };
  const fmtLong = (d) => d.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' });

  const today = startOfDay(new Date());
  const lastDay = addDays(today, BOOKING_WINDOW_DAYS);

  // Demo availability: a deterministic ~20% of slots appear "booked" so the UI looks realistic.
  const isDemoBooked = (date, m) => {
    let h = 0;
    for (const ch of `${toISO(date)}-${m}`) h = (h * 31 + ch.charCodeAt(0)) >>> 0;
    return h % 5 === 0;
  };

  const slotsFor = (date) => {
    if (CLOSED_DAYS.includes(date.getDay()) || date < today || date > lastDay) return { morning: [], evening: [] };
    const now = new Date();
    const nowMin = sameDay(date, now) ? now.getHours() * 60 + now.getMinutes() + 30 : -1;
    const build = ({ start, end }) => {
      const list = [];
      for (let m = start; m + SLOT_MIN <= end; m += SLOT_MIN) {
        list.push({ m, available: m > nowMin && !isDemoBooked(date, m) });
      }
      return list;
    };
    return { morning: build(SESSIONS.morning), evening: build(SESSIONS.evening) };
  };
  const hasAvailability = (date) => {
    const s = slotsFor(date);
    return [...s.morning, ...s.evening].some((x) => x.available);
  };

  const state = { view: new Date(today.getFullYear(), today.getMonth(), 1), date: null, time: null };

  /* --- Calendar --- */
  const renderCalendar = () => {
    const y = state.view.getFullYear(), mo = state.view.getMonth();
    calTitle.textContent = state.view.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    calPrev.disabled = y === today.getFullYear() && mo === today.getMonth();
    calNext.disabled = new Date(y, mo + 1, 1) > lastDay;

    const offset = (new Date(y, mo, 1).getDay() + 1) % 7; // week starts on Saturday
    const days = new Date(y, mo + 1, 0).getDate();
    const frag = document.createDocumentFragment();

    for (let i = 0; i < offset; i++) {
      const pad = document.createElement('span');
      pad.className = 'cal__day cal__day--pad';
      pad.setAttribute('aria-hidden', 'true');
      frag.appendChild(pad);
    }
    for (let d = 1; d <= days; d++) {
      const date = new Date(y, mo, d);
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'cal__day';
      btn.textContent = d;
      btn.dataset.date = toISO(date);
      btn.disabled = !hasAvailability(date);
      btn.setAttribute('aria-label', `${fmtLong(date)}${btn.disabled ? ' — unavailable' : ''}`);
      if (sameDay(date, today)) btn.classList.add('cal__day--today');
      if (sameDay(date, state.date)) { btn.classList.add('is-selected'); btn.setAttribute('aria-pressed', 'true'); }
      frag.appendChild(btn);
    }
    calGrid.replaceChildren(frag);
  };

  /* --- Guided flow: date → time → patient details --- */
  const smooth = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
  const advanceTo = (el, focusEl) => {
    el.scrollIntoView({ behavior: smooth, block: 'start' });
    el.classList.remove('is-next');
    void el.offsetWidth; // restart the highlight animation
    el.classList.add('is-next');
    if (focusEl) setTimeout(() => focusEl.focus({ preventScroll: true }), 650);
  };

  calGrid.addEventListener('click', (e) => {
    const btn = e.target.closest('.cal__day[data-date]');
    if (!btn || btn.disabled) return;
    selectDate(new Date(`${btn.dataset.date}T00:00:00`));
    // Step 2: show the time slots (they sit below the calendar on small screens)
    advanceTo($('slots'), $('slots').querySelector('.slot:not(:disabled)'));
  });
  calPrev.addEventListener('click', () => { state.view.setMonth(state.view.getMonth() - 1); renderCalendar(); });
  calNext.addEventListener('click', () => { state.view.setMonth(state.view.getMonth() + 1); renderCalendar(); });

  /* --- Slots --- */
  const renderSlots = () => {
    const visit = form.visitType.value;
    $('slotsMeta').textContent = `${visit} · ${SLOT_MIN} mins`;
    if (!state.date) {
      $('slotsDate').textContent = 'Choose a date';
      slotsMorning.replaceChildren(); slotsEvening.replaceChildren();
      return;
    }
    $('slotsDate').textContent = state.date.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
    const s = slotsFor(state.date);
    const fill = (el, list) => el.replaceChildren(...list.map(({ m, available }) => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'slot';
      b.textContent = fmtTime(m);
      b.dataset.min = m;
      b.disabled = !available;
      b.setAttribute('aria-label', `${fmtTime(m)}${available ? '' : ' — booked'}`);
      if (state.time === m) { b.classList.add('is-selected'); b.setAttribute('aria-pressed', 'true'); }
      return b;
    }));
    fill(slotsMorning, s.morning);
    fill(slotsEvening, s.evening);
    slotsEmpty.hidden = s.morning.length + s.evening.length > 0;
  };

  $('slots').addEventListener('click', (e) => {
    const b = e.target.closest('.slot');
    if (!b || b.disabled) return;
    state.time = +b.dataset.min;
    slotError.hidden = true;
    renderSlots();
    updateSummary();
    // Step 3: move straight on to the patient details
    advanceTo($('patientDetails'), $('fName'));
  });

  function selectDate(date, time = null) {
    state.date = date;
    state.time = time;
    state.view = new Date(date.getFullYear(), date.getMonth(), 1);
    renderCalendar();
    renderSlots();
    updateSummary();
  }

  /* --- Find earliest available --- */
  $('findEarliest').addEventListener('click', () => {
    for (let d = today; d <= lastDay; d = addDays(d, 1)) {
      const s = slotsFor(d);
      const first = [...s.morning, ...s.evening].find((x) => x.available);
      if (first) {
        selectDate(d, first.m);
        slotError.hidden = true;
        const sel = $('slots').querySelector('.slot.is-selected');
        if (sel) sel.focus({ preventScroll: true });
        return;
      }
    }
  });

  /* --- Live summary --- */
  function updateSummary() {
    const visit = form.visitType.value;
    const [icon, note] = VISIT_NOTES[visit];
    $('sumVisit').textContent = visit;
    $('sumVisitNote').textContent = note;
    $('sumVisit').closest('li').querySelector('i').className = `bi ${icon}`;

    $('sumDate').textContent = state.date ? fmtLong(state.date) : 'Not selected';
    $('sumTime').textContent = state.time != null
      ? `${fmtTime(state.time)} – ${fmtTime(state.time + SLOT_MIN)} (GMT+6)`
      : 'Pick a slot from the calendar';
    $('sumTreatment').textContent = treatmentSelect.value || 'Not selected';

    fDate.value = state.date ? toISO(state.date) : '';
    fTime.value = state.time != null ? fmtTime(state.time) : '';
  }

  form.querySelectorAll('input[name="visitType"]').forEach((r) => r.addEventListener('change', () => { renderSlots(); updateSummary(); }));

  // Links like the hero "Video Call" button preselect a visit type
  document.querySelectorAll('[data-visit]').forEach((link) => {
    link.addEventListener('click', () => {
      const radio = document.getElementById(link.dataset.visit);
      if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change')); }
    });
  });
  treatmentSelect.addEventListener('change', updateSummary);

  /* --- Submit (frontend demo only — nothing is sent) --- */
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    success.hidden = true;

    const slotOk = state.date && state.time != null;
    slotError.hidden = slotOk;
    const fieldsOk = form.checkValidity();
    form.classList.add('was-validated');

    if (!slotOk) { $('calendar').scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
    if (!fieldsOk) {
      const firstInvalid = form.querySelector(':invalid');
      if (firstInvalid) firstInvalid.focus();
      return;
    }

    const label = submitBtn.querySelector('.btn-label');
    const spinner = submitBtn.querySelector('.spinner-border');
    submitBtn.disabled = true;
    label.textContent = 'Sending…';
    spinner.classList.remove('d-none');

    setTimeout(() => {
      submitBtn.disabled = false;
      label.textContent = 'Request Appointment';
      spinner.classList.add('d-none');
      form.reset();
      form.classList.remove('was-validated');
      state.time = null;
      renderSlots();
      updateSummary();
      success.hidden = false;
      success.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 1400);
  });

  // Initial state: first day with availability, no time chosen yet
  for (let d = today; d <= lastDay; d = addDays(d, 1)) {
    if (hasAvailability(d)) { selectDate(d); break; }
  }
  if (!state.date) { renderCalendar(); renderSlots(); updateSummary(); }

  /* ---------- Newsletter ---------- */
  const nl = document.getElementById('newsletterForm');
  const nlInput = document.getElementById('nlEmail');
  const nlMsg = document.getElementById('newsletterMsg');

  nl.addEventListener('submit', (e) => {
    e.preventDefault();
    const ok = nlInput.checkValidity() && nlInput.value.trim() !== '';
    nl.classList.toggle('is-invalid', !ok);
    nlMsg.classList.toggle('is-error', !ok);
    nlMsg.textContent = ok
      ? 'Thanks! (Demo — connect an email service to collect subscribers.)'
      : 'Please enter a valid email address.';
    if (ok) nl.reset();
  });
})();
