/* ==========================================================================
   main.js — site config & global behaviour
   (sticky header, active nav, smooth scroll, back-to-top, WhatsApp, year)
   ========================================================================== */

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
    const y = window.scrollY;
    header.classList.toggle('is-scrolled', y > 40);
    toTop.classList.toggle('is-visible', y > 600);

    const max = document.documentElement.scrollHeight - window.innerHeight;
    const progress = max > 0 ? y / max : 0;
    toTop.style.setProperty('--progress', (CIRCLE * (1 - progress)).toFixed(1));
    ticking = false;
  };
  window.addEventListener('scroll', () => {
    if (!ticking) { requestAnimationFrame(onScroll); ticking = true; }
  }, { passive: true });
  onScroll();

  toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  /* ---------- Smooth scroll (closes offcanvas first on mobile) ---------- */
  document.addEventListener('click', (e) => {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;
    const id = link.getAttribute('href');
    if (id === '#' || id.length < 2) return;
    const target = document.querySelector(id);
    if (!target) return;

    e.preventDefault();
    const go = () => {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
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

  /* ---------- Footer year ---------- */
  const year = document.getElementById('year');
  if (year) year.textContent = new Date().getFullYear();
})();
