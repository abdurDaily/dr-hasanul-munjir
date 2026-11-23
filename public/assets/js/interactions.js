/* ==========================================================================
   interactions.js — FAQ, gallery filter, compare slider, lightbox, sliders,
   appointment booking, contact form, newsletter.
   Every block checks its elements exist, so the file works on every page.
   ========================================================================== */
(() => {
  'use strict';

  const ASSETS = (window.SITE_CONFIG && window.SITE_CONFIG.assets) || 'assets';
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $ = (id) => document.getElementById(id);

  /** POST a form as JSON-accepting fetch; resolves to { status, data }. */
  const postForm = async (form) => {
    const res = await fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    });
    let data = {};
    try { data = await res.json(); } catch (e) { /* non-JSON error page */ }
    return { status: res.status, data };
  };

  /* ---------- FAQ accordion ----------
     Small replacement for Bootstrap's Collapse JS (reuses .collapse/.collapsing/.show CSS).
     One panel open at a time within the same data-collapse-parent. */
  const collapse = (panel, open) => {
    if (panel.classList.contains('show') === open || panel.classList.contains('collapsing')) return;
    const end = () => {
      panel.classList.remove('collapsing');
      panel.classList.add('collapse');
      panel.classList.toggle('show', open);
      panel.style.height = '';
    };
    panel.style.height = open ? '0px' : `${panel.getBoundingClientRect().height}px`;
    panel.classList.remove('collapse', 'show');
    panel.classList.add('collapsing');
    void panel.offsetHeight; // start the transition from the height set above
    panel.style.height = open ? `${panel.scrollHeight}px` : '0px';
    let done = false;
    const finish = () => { if (!done) { done = true; end(); } };
    panel.addEventListener('transitionend', finish, { once: true });
    setTimeout(finish, 500);
  };

  document.querySelectorAll('[data-collapse]').forEach((btn) => {
    const panel = document.querySelector(btn.dataset.collapse);
    if (!panel) return;
    btn.addEventListener('click', () => {
      const open = !panel.classList.contains('show');
      const group = panel.dataset.collapseParent && document.querySelector(panel.dataset.collapseParent);
      if (open && group) {
        group.querySelectorAll('.collapse.show').forEach((other) => {
          if (other === panel) return;
          collapse(other, false);
          const otherBtn = group.querySelector(`[data-collapse="#${other.id}"]`);
          if (otherBtn) { otherBtn.classList.add('collapsed'); otherBtn.setAttribute('aria-expanded', 'false'); }
        });
      }
      collapse(panel, open);
      btn.classList.toggle('collapsed', !open);
      btn.setAttribute('aria-expanded', String(open));
    });
  });

  /* ---------- Gallery filtering ---------- */
  const filterBtns = document.querySelectorAll('button.filter-btn');
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
      if (window.gsap && !reduceMotion) {
        gsap.fromTo(visible, { autoAlpha: 0, y: 24 }, { autoAlpha: 1, y: 0, duration: .5, stagger: .08, ease: 'power3.out' });
      }
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
  const lb = $('lightbox');
  if (lb) {
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
      lbImg.removeAttribute('src');
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
  }

  /* ---------- Sliders (Swiper is fetched only when a slider is about to scroll into view) ---------- */
  const initSliders = () => {
    if (document.querySelector('.testimonial-swiper')) {
      // Google-style reviews
      new Swiper('.testimonial-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: document.querySelectorAll('.testimonial-swiper .swiper-slide').length > 3,
        grabCursor: true,
        speed: 600,
        autoplay: reduceMotion ? false : { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
        pagination: { el: '.testimonial-swiper .swiper-pagination', clickable: true },
        navigation: { nextEl: '.testimonial-swiper .swiper-nav--next', prevEl: '.testimonial-swiper .swiper-nav--prev' },
        a11y: { enabled: true },
        breakpoints: { 768: { slidesPerView: 2, spaceBetween: 24 }, 1200: { slidesPerView: 3, spaceBetween: 24 } },
      });
    }

    if (document.querySelector('.why-swiper')) {
      // "Why choose me" photo slider
      new Swiper('.why-swiper', {
        effect: 'fade',
        fadeEffect: { crossFade: true },
        loop: document.querySelectorAll('.why-swiper .swiper-slide').length > 1,
        speed: 900,
        autoplay: reduceMotion ? false : { delay: 4500, disableOnInteraction: false, pauseOnMouseEnter: true },
        pagination: { el: '.why-swiper__pagination', clickable: true },
        navigation: { nextEl: '.why-nav--next', prevEl: '.why-nav--prev' },
        a11y: { enabled: true },
      });
    }
  };

  const sliderEls = document.querySelectorAll('.why-swiper, .testimonial-swiper');
  if (sliderEls.length) {
    const sliderIO = new IntersectionObserver((entries) => {
      if (!entries.some((e) => e.isIntersecting)) return;
      sliderIO.disconnect();
      const css = document.createElement('link');
      css.rel = 'stylesheet';
      css.href = `${ASSETS}/css/swiper-bundle.min.css`;
      document.head.appendChild(css);
      window.loadScript(`${ASSETS}/js/swiper-bundle.min.js`).then(initSliders).catch(() => {});
    }, { rootMargin: '600px 0px' });
    sliderEls.forEach((el) => sliderIO.observe(el));
  }

  /* ---------- Appointment booking (live availability, calendar, slots, summary) ---------- */
  const form = $('appointmentForm');
  if (form) initBooking(form);

  function initBooking(form) {
    const success = $('formSuccess');
    const formError = $('formError');
    const submitBtn = form.querySelector('button[type="submit"]');
    const treatmentSelect = $('fTreatment');
    const calGrid = $('calGrid');
    const calTitle = $('calTitle');
    const calPrev = $('calPrev');
    const calNext = $('calNext');
    const slotsGroups = $('slotsGroups');
    const slotsEmpty = $('slotsEmpty');
    const slotError = $('slotError');
    const slotErrorText = slotError.querySelector('span');
    const SLOT_ERROR_DEFAULT = slotErrorText.textContent;

    // Dates are handled as "YYYY-MM-DD" strings from the server (Asia/Dhaka), so the
    // visitor's own time zone can never shift a booking onto a different day.
    const parse = (iso) => { const [y, m, d] = iso.split('-').map(Number); return new Date(y, m - 1, d); };
    const toISO = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    const fmtLong = (iso) => parse(iso).toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' });
    const label12 = (hhmm) => {
      const [h, m] = hhmm.split(':').map(Number);
      return `${((h + 11) % 12) + 1}:${String(m).padStart(2, '0')} ${h < 12 ? 'AM' : 'PM'}`;
    };
    const addMinutes = (hhmm, mins) => {
      const [h, m] = hhmm.split(':').map(Number);
      const t = h * 60 + m + mins;
      return `${String(Math.floor(t / 60) % 24).padStart(2, '0')}:${String(t % 60).padStart(2, '0')}`;
    };

    const state = { data: null, view: null, date: null, time: null };

    const daySessions = (iso) => (state.data && state.data.days[iso]) || [];
    const hasAvailability = (iso) => daySessions(iso).some((s) => s.slots.some((x) => x.available));
    const visit = () => form.querySelector('input[name="appointment_type_id"]:checked');

    /* --- Calendar --- */
    const renderCalendar = () => {
      if (!state.data) return;
      const today = parse(state.data.today);
      const last = parse(state.data.last);
      const y = state.view.getFullYear(), mo = state.view.getMonth();
      calTitle.textContent = state.view.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
      calPrev.disabled = y === today.getFullYear() && mo === today.getMonth();
      calNext.disabled = new Date(y, mo + 1, 1) > last;

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
        const iso = toISO(new Date(y, mo, d));
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'cal__day';
        btn.textContent = d;
        btn.dataset.date = iso;
        btn.disabled = !hasAvailability(iso);
        btn.setAttribute('aria-label', `${fmtLong(iso)}${btn.disabled ? ' — unavailable' : ''}`);
        if (iso === state.data.today) btn.classList.add('cal__day--today');
        if (iso === state.date) { btn.classList.add('is-selected'); btn.setAttribute('aria-pressed', 'true'); }
        frag.appendChild(btn);
      }
      calGrid.replaceChildren(frag);
    };

    /* --- Guided flow: date → time → patient details --- */
    const smooth = reduceMotion ? 'auto' : 'smooth';
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
      selectDate(btn.dataset.date);
      advanceTo($('slots'), $('slots').querySelector('.slot:not(:disabled)'));
    });
    calPrev.addEventListener('click', () => { state.view.setMonth(state.view.getMonth() - 1); renderCalendar(); });
    calNext.addEventListener('click', () => { state.view.setMonth(state.view.getMonth() + 1); renderCalendar(); });

    /* --- Slots (one group per chamber session, e.g. Morning / Evening) --- */
    const renderSlots = () => {
      const v = visit();
      $('slotsMeta').textContent = `${v ? v.dataset.name : ''}${state.data ? ` · ${state.data.slotMinutes} mins` : ''}`;
      if (!state.date) {
        $('slotsDate').textContent = 'Choose a date';
        slotsGroups.replaceChildren();
        slotsEmpty.hidden = !state.data;
        return;
      }
      $('slotsDate').textContent = parse(state.date).toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
      const sessions = daySessions(state.date);
      const frag = document.createDocumentFragment();
      sessions.forEach((session) => {
        const title = document.createElement('p');
        title.className = 'slots__group';
        title.textContent = session.name;
        const grid = document.createElement('div');
        grid.className = 'slots__grid';
        session.slots.forEach(({ time, label, available }) => {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = 'slot';
          b.textContent = label;
          b.dataset.time = time;
          b.disabled = !available;
          b.setAttribute('aria-label', `${label}${available ? '' : ' — unavailable'}`);
          if (state.time === time) { b.classList.add('is-selected'); b.setAttribute('aria-pressed', 'true'); }
          grid.appendChild(b);
        });
        frag.append(title, grid);
      });
      slotsGroups.replaceChildren(frag);
      slotsEmpty.hidden = sessions.some((s) => s.slots.some((x) => x.available));
    };

    $('slots').addEventListener('click', (e) => {
      const b = e.target.closest('.slot');
      if (!b || b.disabled) return;
      state.time = b.dataset.time;
      slotError.hidden = true;
      renderSlots();
      updateSummary();
      advanceTo($('patientDetails'), $('fName'));
    });

    function selectDate(iso, time = null) {
      state.date = iso;
      state.time = time;
      state.view = new Date(parse(iso).getFullYear(), parse(iso).getMonth(), 1);
      renderCalendar();
      renderSlots();
      updateSummary();
    }

    const earliest = () => {
      for (const iso of Object.keys(state.data.days).sort()) {
        for (const session of state.data.days[iso]) {
          const slot = session.slots.find((x) => x.available);
          if (slot) return [iso, slot.time];
        }
      }
      return null;
    };

    $('findEarliest').addEventListener('click', () => {
      if (!state.data) return;
      const first = earliest();
      if (!first) { showSlotError('No appointments are available in the booking window. Please call the clinic.'); return; }
      selectDate(first[0], first[1]);
      slotError.hidden = true;
      const sel = $('slots').querySelector('.slot.is-selected');
      if (sel) sel.focus({ preventScroll: true });
    });

    /* --- Live summary --- */
    function updateSummary() {
      const v = visit();
      if (v) {
        $('sumVisit').textContent = v.dataset.name;
        $('sumVisitNote').textContent = v.dataset.note || '';
        $('sumVisit').closest('li').querySelector('i').className = `bi ${v.dataset.icon}`;
      }
      $('sumDate').textContent = state.date ? fmtLong(state.date) : 'Not selected';
      $('sumTime').textContent = state.time
        ? `${label12(state.time)} – ${label12(addMinutes(state.time, state.data.slotMinutes))} (GMT+6)`
        : 'Pick a slot from the calendar';
      const opt = treatmentSelect.selectedOptions[0];
      $('sumTreatment').textContent = opt && opt.value ? opt.text : 'Not selected';

      $('fDate').value = state.date || '';
      $('fTime').value = state.time || '';
    }

    form.querySelectorAll('input[name="appointment_type_id"]').forEach((r) => r.addEventListener('change', () => { renderSlots(); updateSummary(); }));
    treatmentSelect.addEventListener('change', updateSummary);

    // "Book this treatment" links preselect the treatment
    document.querySelectorAll('[data-treatment-id]').forEach((link) => {
      link.addEventListener('click', () => {
        if ([...treatmentSelect.options].some((o) => o.value === link.dataset.treatmentId)) {
          treatmentSelect.value = link.dataset.treatmentId;
          treatmentSelect.dispatchEvent(new Event('change'));
        }
      });
    });

    /* --- Errors --- */
    const showSlotError = (msg) => {
      slotErrorText.textContent = msg || SLOT_ERROR_DEFAULT;
      slotError.hidden = false;
    };
    const clearServerErrors = () => {
      form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
      formError.hidden = true;
    };
    const showFieldErrors = (errors) => {
      let first = null;
      Object.entries(errors).forEach(([field, messages]) => {
        if (field === 'date' || field === 'time') { showSlotError(messages[0]); first = first || $('calendar'); return; }
        const input = form.querySelector(`[name="${field}"]`);
        const feedback = form.querySelector(`.invalid-feedback[data-for="${field}"]`);
        if (input && input.type !== 'radio') {
          input.classList.add('is-invalid');
          if (feedback) feedback.textContent = messages[0];
          first = first || input;
        } else {
          formError.querySelector('span').textContent = messages[0];
          formError.hidden = false;
        }
      });
      if (first === $('calendar')) first.scrollIntoView({ behavior: smooth, block: 'center' });
      else if (first) first.focus();
    };

    /* --- Load availability from the server (never cached) --- */
    const load = async () => {
      try {
        const res = await fetch(form.dataset.availabilityUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!res.ok) throw new Error(res.status);
        state.data = await res.json();
      } catch (e) {
        calTitle.textContent = 'Unavailable';
        showSlotError('We could not load available times. Please refresh the page or call the clinic.');
        return false;
      }
      if (!state.view) { const t = parse(state.data.today); state.view = new Date(t.getFullYear(), t.getMonth(), 1); }
      // Keep the chosen slot only if it is still free
      if (state.date && !hasAvailability(state.date)) state.date = null;
      if (state.date && state.time && !daySessions(state.date).some((s) => s.slots.some((x) => x.time === state.time && x.available))) state.time = null;
      return true;
    };

    /* --- Submit: the server validates and re-checks the slot inside a locked transaction --- */
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      success.hidden = true;
      clearServerErrors();

      const slotOk = state.date && state.time;
      if (!slotOk) showSlotError(); else slotError.hidden = true;
      const fieldsOk = form.checkValidity();
      form.classList.add('was-validated');

      if (!slotOk) { $('calendar').scrollIntoView({ behavior: smooth, block: 'center' }); return; }
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

      try {
        const { status, data } = await postForm(form);
        if (status === 201) {
          $('bookingRef').textContent = data.reference;
          $('bookingMsg').textContent = data.message;
          form.reset();
          form.classList.remove('was-validated');
          state.date = null;
          state.time = null;
          await load();
          renderCalendar();
          renderSlots();
          updateSummary();
          success.hidden = false;
          success.focus({ preventScroll: true });
          success.scrollIntoView({ behavior: smooth, block: 'nearest' });
        } else if (status === 409) {
          // Someone else took this slot a moment ago: refresh and ask for another time
          await load();
          renderCalendar();
          renderSlots();
          updateSummary();
          showSlotError(data.message);
          $('slots').scrollIntoView({ behavior: smooth, block: 'center' });
        } else if (status === 422 && data.errors) {
          form.classList.remove('was-validated');
          showFieldErrors(data.errors);
        } else if (status === 429) {
          formError.querySelector('span').textContent = 'Too many requests from this device. Please wait a few minutes, or call the clinic.';
          formError.hidden = false;
        } else if (status === 419) {
          formError.querySelector('span').textContent = 'Your session expired. Please refresh the page and try again.';
          formError.hidden = false;
        } else {
          throw new Error(status);
        }
      } catch (err) {
        formError.querySelector('span').textContent = 'Sorry, your request could not be sent. Please try again or call the clinic.';
        formError.hidden = false;
      } finally {
        submitBtn.disabled = false;
        label.textContent = 'Request Appointment';
        spinner.classList.add('d-none');
      }
    });

    // Built only when the booking section approaches, keeping page load light.
    const start = async () => {
      if (!(await load())) return;
      const first = Object.keys(state.data.days).sort().find(hasAvailability);
      if (first) selectDate(first);
      else { renderCalendar(); renderSlots(); updateSummary(); showSlotError('No appointments are available online right now. Please call the clinic.'); }
    };
    const bookingIO = new IntersectionObserver((entries) => {
      if (!entries.some((e) => e.isIntersecting)) return;
      bookingIO.disconnect();
      start();
    }, { rootMargin: '800px 0px' });
    bookingIO.observe(form);
    updateSummary();
  }

  /* ---------- Contact form (server-validated; this only adds instant feedback) ---------- */
  const contactForm = $('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      if (!contactForm.checkValidity()) {
        e.preventDefault();
        contactForm.classList.add('was-validated');
        const firstInvalid = contactForm.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
      }
    });
  }

  /* ---------- Newsletter ---------- */
  const nl = $('newsletterForm');
  if (nl) {
    const nlInput = $('nlEmail');
    const nlMsg = $('newsletterMsg');
    const say = (ok, text) => {
      nl.classList.toggle('is-invalid', !ok);
      nlMsg.classList.toggle('is-error', !ok);
      nlMsg.textContent = text;
    };

    nl.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!nlInput.checkValidity() || nlInput.value.trim() === '') { say(false, 'Please enter a valid email address.'); return; }
      try {
        const { status, data } = await postForm(nl);
        if (status === 200) { say(true, data.message); nl.reset(); }
        else if (status === 422) say(false, (data.errors && Object.values(data.errors)[0][0]) || 'Please enter a valid email address.');
        else if (status === 429) say(false, 'Too many attempts. Please try again later.');
        else say(false, 'Sorry, something went wrong. Please try again.');
      } catch (err) {
        say(false, 'Sorry, something went wrong. Please try again.');
      }
    });
  }
})();
