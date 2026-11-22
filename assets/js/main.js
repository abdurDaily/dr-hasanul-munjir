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
  requestAnimationFrame(onScroll);

  toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

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

    const oc = window.bootstrap && bootstrap.Offcanvas.getInstance(offcanvasEl);
    if (oc && offcanvasEl.classList.contains('show')) {
      offcanvasEl.addEventListener('hidden.bs.offcanvas', go, { once: true });
      oc.hide();
    } else {
      go();
    }
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
