/* ==========================================================================
   animations.js — GSAP + ScrollTrigger
   Content is visible by default; animations only run when GSAP loads and
   the user hasn't asked for reduced motion. Elements animated here have no
   CSS transform/opacity transitions, so the two never fight.
   ========================================================================== */
(() => {
  'use strict';

  const counters = document.querySelectorAll('[data-count]');
  const format = (n) => Math.round(n).toLocaleString('en-US');

  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!window.gsap || !window.ScrollTrigger || reduce) return; // HTML already shows final values

  gsap.registerPlugin(ScrollTrigger);
  gsap.defaults({ ease: 'power3.out', duration: 1 });

  const exists = (sel) => document.querySelector(sel) !== null;

  /* ---------- Hero ----------
     No entrance animation: the hero (and its portrait, the page's LCP element)
     paints immediately. Only the floating cards get a gentle idle motion. */
  gsap.to('.hero-chip--exp', { y: -8, duration: 3, ease: 'sine.inOut', repeat: -1, yoyo: true });
  gsap.to('.hero-chip--doc', { y: 8, duration: 3.4, ease: 'sine.inOut', repeat: -1, yoyo: true });
  /* ---------- Section headings: masked reveal ---------- */
  document.querySelectorAll('.split-heading').forEach((h) => {
    gsap.fromTo(h, { clipPath: 'inset(0% 0% 100% 0%)', y: 40 }, {
      clipPath: 'inset(0% 0% -20% 0%)', y: 0, duration: 1.1, ease: 'power4.out', clearProps: 'clipPath',
      scrollTrigger: { trigger: h, start: 'top 90%', once: true },
    });
  });

  /* ---------- Generic fade-up helper ---------- */
  const fadeUp = (targets, trigger, opts = {}) => {
    if (typeof targets === 'string' && !exists(targets)) return;
    gsap.from(targets, {
      y: 40, autoAlpha: 0, duration: .9, stagger: .1, ...opts,
      scrollTrigger: { trigger, start: 'top 88%', once: true },
    });
  };

  document.querySelectorAll('section .eyebrow:not(.hero__eyebrow)').forEach((el) => fadeUp(el, el, { y: 16 }));
  document.querySelectorAll('.section-head__aside, .about p, .about__facts .fact, .philosophy').forEach((el) => fadeUp(el, el, { y: 24 }));

  /* ---------- Counters (count up from 0 when visible) ---------- */
  counters.forEach((el) => {
    const target = +el.dataset.count;
    const obj = { v: 0 };
    el.textContent = '0';
    gsap.to(obj, {
      v: target, duration: 2.2, ease: 'power2.out',
      onUpdate: () => { el.textContent = format(obj.v); },
      scrollTrigger: { trigger: el, start: 'top 92%', once: true },
    });
  });
  fadeUp('.stat', '.stats__grid', { y: 30 });

  /* ---------- Image reveals (curtain wipe) ---------- */
  document.querySelectorAll('.reveal-img').forEach((wrap) => {
    const img = wrap.querySelector('img');
    const tl = gsap.timeline({ scrollTrigger: { trigger: wrap, start: 'top 88%', once: true } });
    tl.fromTo(wrap, { clipPath: 'inset(0% 0% 100% 0%)' }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.3, ease: 'expo.inOut', clearProps: 'clipPath' });
    if (img) tl.from(img, { scale: 1.3, duration: 1.6, ease: 'expo.out' }, '-=1');
  });
  gsap.from('.about__exp', { scale: .5, autoAlpha: 0, duration: .8, ease: 'back.out(1.7)', scrollTrigger: { trigger: '.about__media', start: 'top 70%', once: true } });

  /* ---------- Treatment cards ---------- */
  gsap.set('.treat-card', { y: 50, autoAlpha: 0 });
  ScrollTrigger.batch('.treat-card', {
    start: 'top 92%',
    once: true,
    onEnter: (batch) => gsap.to(batch, { y: 0, autoAlpha: 1, stagger: .1, duration: .9, overwrite: true }),
  });

  /* ---------- Why choose ---------- */
  fadeUp('.why__list li', '.why__list', { x: -24, y: 0, stagger: .08 });
  fadeUp('.why__slider-wrap', '.why__slider-wrap', { y: 50 });
  gsap.from('.why__badge', { scale: 0, rotate: -15, duration: .9, ease: 'back.out(1.8)', scrollTrigger: { trigger: '.why__slider-wrap', start: 'top 75%', once: true } });

  /* ---------- Experience ---------- */
  fadeUp('.xp-feature__body > *', '.xp-feature', { y: 24, stagger: .08 });
  gsap.from('.xp-feature__year', { scale: 0, rotate: -12, duration: .8, ease: 'back.out(2)', scrollTrigger: { trigger: '.xp-feature', start: 'top 70%', once: true } });
  fadeUp('.xp-step', '.xp-steps', { y: 40, stagger: .12 });
  gsap.from('.xp-step__node', { scale: 0, duration: .5, stagger: .12, ease: 'back.out(2)', scrollTrigger: { trigger: '.xp-steps', start: 'top 85%', once: true } });

  /* ---------- Remaining sections ---------- */
  fadeUp('.gallery__item', '.gallery__grid', { y: 50, stagger: .12 });
  fadeUp('.bento__item--cta', '.bento', { y: 40 });
  fadeUp('.g-summary', '.g-head', { y: 24 });
  fadeUp('.testimonial-swiper', '.testimonial-swiper', { y: 40 });
  fadeUp('.booking .bk-card, .booking .bk-policy', '.booking', { y: 40, stagger: .1 });
  fadeUp('.summary', '.booking', { y: 40, delay: .2 });
  fadeUp('.post', '.blog .row', { y: 50, stagger: .12 });
  fadeUp('.faq-acc .accordion-item', '.faq-acc', { y: 24, stagger: .07 });
  fadeUp('.c-card', '.contact__cards', { y: 30, stagger: .08 });
  fadeUp('.map-card', '.map-card', { y: 40 });
  fadeUp('.footer__cta > *', '.footer', { y: 30 });

  /* ---------- Magnetic button micro-interaction (pointer devices only) ---------- */
  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    document.querySelectorAll('.magnetic').forEach((btn) => {
      const xTo = gsap.quickTo(btn, 'x', { duration: .4, ease: 'power3.out' });
      const yTo = gsap.quickTo(btn, 'y', { duration: .4, ease: 'power3.out' });
      btn.addEventListener('mousemove', (e) => {
        const r = btn.getBoundingClientRect();
        xTo((e.clientX - r.left - r.width / 2) * .25);
        yTo((e.clientY - r.top - r.height / 2) * .35);
      });
      btn.addEventListener('mouseleave', () => { xTo(0); yTo(0); });
    });
  }

  // This file is loaded after window 'load', so positions are already settled
  ScrollTrigger.refresh();
})();
