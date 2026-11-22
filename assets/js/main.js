/* ==========================================================================
   main.js — site config & global behaviour
   (sticky header, active nav, smooth scroll, back-to-top, WhatsApp, year)
   ========================================================================== */

/** Load a script once; resolves when it has executed. Used to keep heavy libraries off the critical path. */
window.loadScript = (() => {
  const cache = {};
  return (src) => (cache[src] ||= new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = src;
    s.async = false; // preserve execution order when several are requested together
    s.onload = resolve;
    s.onerror = reject;
    document.head.appendChild(s);
  }));
})();

/** Site-wide configuration — edit these values for the live site. */
window.SITE_CONFIG = {
  whatsappNumber: '8801690106079', // international format, digits only — confirm this number uses WhatsApp
};

(() => {
  'use strict';

  const header = document.getElementById('siteHeader');
  const toTop = document.getElementById('toTop');
  const navLinks = document.querySelectorAll('.nav-desktop .nav-link, .mobile-nav__list a');
  const offcanvasEl = document.getElementById('mobileNav');
  const CIRCLE = 138.2; // circumference of the back-to-top progress ring

  /* ---------- Sticky header + back-to-top progress ---------- */
  let ticking = false;
  const onScroll = () => {
    // Read layout first, then write — avoids a forced reflow on every scroll
    const y = window.scrollY;
    const max = document.documentElement.scrollHeight - window.innerHeight;
    const progress = max > 0 ? y / max : 0;
    header.classList.toggle('is-scrolled', y > 40);
    toTop.classList.toggle('is-visible', y > 600);

    toTop.style.setProperty('--progress', (CIRCLE * (1 - progress)).toFixed(1));
    ticking = false;
  };
  window.addEventListener('scroll', () => {
    if (!ticking) { requestAnimationFrame(onScroll); ticking = true; }
  }, { passive: true });
  // Only measure at startup if the page opened already scrolled (avoids a forced layout on load)
  if (window.scrollY > 0) requestAnimationFrame(onScroll);

  toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  /* ---------- Mobile menu (offcanvas) ----------
     Small replacement for Bootstrap's Offcanvas JS: reuses Bootstrap's CSS classes
     (.showing/.show/.hiding, .offcanvas-backdrop) and handles focus, Esc, backdrop
     click, Tab trapping and scroll locking. */
  const menu = (() => {
    const toggles = document.querySelectorAll('[data-offcanvas-open="#mobileNav"]');
    const focusable = () => offcanvasEl.querySelectorAll('a[href], button:not([disabled])');
    let backdrop = null;
    let returnFocus = null;

    const afterTransition = (fn) => {
      let done = false;
      const finish = () => { if (!done) { done = true; fn(); } };
      offcanvasEl.addEventListener('transitionend', finish, { once: true });
      setTimeout(finish, 450); // fallback (reduced motion / no transition)
    };

    const onKey = (e) => {
      if (e.key === 'Escape') { hide(); return; }
      if (e.key !== 'Tab') return;
      const items = [...focusable()];
      const first = items[0];
      const last = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    };

    function show() {
      if (offcanvasEl.classList.contains('show')) return;
      returnFocus = document.activeElement;
      backdrop = document.createElement('div');
      backdrop.className = 'offcanvas-backdrop fade';
      backdrop.addEventListener('click', () => hide());
      document.body.appendChild(backdrop);
      document.body.style.overflow = 'hidden';
      offcanvasEl.setAttribute('role', 'dialog');
      offcanvasEl.setAttribute('aria-modal', 'true');
      offcanvasEl.classList.add('showing');
      toggles.forEach((t) => t.setAttribute('aria-expanded', 'true'));
      requestAnimationFrame(() => {
        backdrop.classList.add('show');
        offcanvasEl.classList.add('show');
        afterTransition(() => offcanvasEl.classList.remove('showing'));
        const firstItem = focusable()[0];
        if (firstItem) firstItem.focus({ preventScroll: true });
      });
      document.addEventListener('keydown', onKey);
    }

    function hide(onHidden) {
      if (!offcanvasEl.classList.contains('show')) { if (onHidden) onHidden(); return; }
      document.removeEventListener('keydown', onKey);
      offcanvasEl.classList.add('hiding');
      offcanvasEl.classList.remove('show');
      if (backdrop) backdrop.classList.remove('show');
      toggles.forEach((t) => t.setAttribute('aria-expanded', 'false'));
      afterTransition(() => {
        offcanvasEl.classList.remove('hiding');
        offcanvasEl.removeAttribute('role');
        offcanvasEl.removeAttribute('aria-modal');
        if (backdrop) { backdrop.remove(); backdrop = null; }
        document.body.style.overflow = '';
        if (onHidden) onHidden(); else if (returnFocus) returnFocus.focus({ preventScroll: true });
      });
    }

    toggles.forEach((t) => t.addEventListener('click', show));
    offcanvasEl.querySelectorAll('[data-offcanvas-close]').forEach((b) => b.addEventListener('click', () => hide()));
    return { show, hide };
  })();

  /* ---------- Smooth scroll (closes offcanvas first on mobile) ----------
     Off-screen sections use content-visibility:auto, so their heights are estimates
     until rendered. After each scroll settles we re-check the target and correct. */
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const scrollOffset = () => parseFloat(getComputedStyle(document.documentElement).scrollPaddingTop) || 0;
  const scrollToSection = (target, attempt = 0) => {
    target.scrollIntoView({ behavior: reduceMotion || attempt ? 'auto' : 'smooth', block: 'start' });
    if (attempt >= 3) return;
    const settle = () => {
      const off = target.getBoundingClientRect().top - scrollOffset();
      if (Math.abs(off) > 4) scrollToSection(target, attempt + 1);
    };
    if ('onscrollend' in window) window.addEventListener('scrollend', settle, { once: true });
    else setTimeout(settle, 900);
  };
  // Deep links (e.g. …/#appointment) need the same correction after load
  if (location.hash.length > 1) {
    const deep = document.querySelector(location.hash);
    if (deep) window.addEventListener('load', () => scrollToSection(deep, 1), { once: true });
  }

  document.addEventListener('click', (e) => {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;
    const id = link.getAttribute('href');
    if (id === '#' || id.length < 2) return;
    const target = document.querySelector(id);
    if (!target) return;

    e.preventDefault();
    const go = () => {
      scrollToSection(target);
      history.replaceState(null, '', id);
    };

    if (offcanvasEl.classList.contains('show')) menu.hide(go); else go();
  });

  /* ---------- Active navigation highlighting ---------- */
  const sections = [...new Set([...navLinks].map((a) => a.getAttribute('href')))]
    .map((id) => document.querySelector(id))
    .filter(Boolean);

  const setActive = (id) => {
    navLinks.forEach((a) => {
      const on = a.getAttribute('href') === `#${id}`;
      a.classList.toggle('active', on);
      if (on) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
    });
  };

  const spy = new IntersectionObserver((entries) => {
    entries.forEach((entry) => { if (entry.isIntersecting) setActive(entry.target.id); });
  }, { rootMargin: '-45% 0px -50% 0px' });
  sections.forEach((s) => spy.observe(s));

  /* ---------- WhatsApp links ---------- */
  document.querySelectorAll('.js-whatsapp').forEach((el) => {
    const text = encodeURIComponent(el.dataset.waText || 'Hello!');
    el.href = `https://wa.me/${window.SITE_CONFIG.whatsappNumber}?text=${text}`;
    el.target = '_blank';
    el.rel = 'noopener';
  });

  /* ---------- Scroll animations: load GSAP after the page has rendered ---------- */
  // Content is fully visible without it; GSAP only enhances what is below the fold.
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const startAnimations = () => {
      Promise.all([
        window.loadScript('assets/js/gsap.min.js'),
        window.loadScript('assets/js/animations.js'),
      ]).catch(() => {});
    };
    const idle = window.requestIdleCallback || ((fn) => setTimeout(fn, 1));
    const schedule = () => idle(startAnimations, { timeout: 2000 });
    if (document.readyState === 'complete') schedule(); else window.addEventListener('load', schedule, { once: true });
  }

  /* ---------- Footer year ---------- */
  const year = document.getElementById('year');
  if (year) year.textContent = new Date().getFullYear();
})();
