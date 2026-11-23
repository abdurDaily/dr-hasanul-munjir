/* ==========================================================================
   animations.js — GSAP reveal animations (loaded by main.js after page load)
   Each animation is created only when its element approaches the viewport
   (IntersectionObserver), so there is no up-front setup cost and no layout
   measurement. Content is fully visible if this file never loads.
   ========================================================================== */
(() => {
  'use strict';
  if (!window.gsap || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  gsap.defaults({ ease: 'power3.out', duration: .9 });
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  /* ---------- Core helper: run `play(el)` once, just before `el` scrolls into view ---------- */
  const jobs = new Map();
  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      io.unobserve(entry.target);
      const play = jobs.get(entry.target);
      jobs.delete(entry.target);
      if (play) play(entry.target);
    });
  }, { rootMargin: '0px 0px 6% 0px' });

  const onEnter = (el, play) => { if (el) { jobs.set(el, play); io.observe(el); } };
  const each = (sel, play) => $$(sel).forEach((el) => onEnter(el, play));

  /** Fade/slide `targets` (selector inside trigger, or the trigger itself) when `trigger` enters. */
  const fadeUp = (triggerSel, targetSel, vars = {}) =>
    each(triggerSel, (trigger) => {
      const targets = targetSel ? $$(targetSel, trigger) : trigger;
      gsap.from(targets, { y: 36, autoAlpha: 0, stagger: .09, ...vars });
    });

  /* ---------- Hero: gentle idle float on the info cards ---------- */
  gsap.to('.hero-chip--exp', { y: -8, duration: 3, ease: 'sine.inOut', repeat: -1, yoyo: true });
  gsap.to('.hero-chip--doc', { y: 8, duration: 3.4, ease: 'sine.inOut', repeat: -1, yoyo: true });

  /* ---------- Section headings: masked reveal ---------- */
  each('.split-heading', (h) => gsap.fromTo(h,
    { clipPath: 'inset(0% 0% 100% 0%)', y: 30 },
    { clipPath: 'inset(0% 0% -20% 0%)', y: 0, duration: 1, ease: 'power4.out', clearProps: 'clipPath' }));

  fadeUp('section .eyebrow', null, { y: 14 });
  fadeUp('.section-head__aside, .about p, .about__facts .fact, .philosophy', null, { y: 22 });

  /* ---------- Counters ---------- */
  each('.stats__grid', (grid) => {
    gsap.from($$('.stat', grid), { y: 28, autoAlpha: 0, stagger: .1 });
    $$('[data-count]', grid).forEach((el) => {
      const obj = { v: 0 };
      gsap.to(obj, {
        v: +el.dataset.count, duration: 2, ease: 'power2.out',
        onUpdate: () => { el.textContent = Math.round(obj.v).toLocaleString('en-US'); },
      });
    });
  });

  /* ---------- Image curtain reveals ---------- */
  each('.reveal-img', (wrap) => {
    gsap.fromTo(wrap, { clipPath: 'inset(0% 0% 100% 0%)' }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.2, ease: 'expo.inOut', clearProps: 'clipPath' });
    const img = wrap.querySelector('img');
    if (img) gsap.from(img, { scale: 1.25, duration: 1.5, ease: 'expo.out', delay: .2 });
  });
  each('.about__exp', (el) => gsap.from(el, { scale: .5, autoAlpha: 0, duration: .7, ease: 'back.out(1.7)', delay: .4 }));

  /* ---------- Cards & lists ---------- */
  each('.treat-card', (card) => {
    const i = $$('.treat-card').indexOf(card) % 3;
    gsap.from(card, { y: 46, autoAlpha: 0, delay: i * .08 });
  });
  fadeUp('.why__list', 'li', { x: -22, y: 0, stagger: .07 });
  fadeUp('.why__slider-wrap', null, { y: 46 });
  each('.why__badge', (el) => gsap.from(el, { scale: 0, rotate: -15, duration: .8, ease: 'back.out(1.8)', delay: .3 }));

  fadeUp('.xp-feature', '.xp-feature__body > *', { y: 22, stagger: .07 });
  each('.xp-feature__year', (el) => gsap.from(el, { scale: 0, rotate: -12, duration: .7, ease: 'back.out(2)', delay: .3 }));
  each('.xp-steps', (list) => {
    gsap.from($$('.xp-step', list), { y: 36, autoAlpha: 0, stagger: .1 });
    gsap.from($$('.xp-step__node', list), { scale: 0, duration: .5, stagger: .1, ease: 'back.out(2)', delay: .2 });
  });

  fadeUp('.gallery__grid', '.gallery__item', { y: 46, stagger: .1 });
  fadeUp('.bento__item--cta');
  fadeUp('.g-summary');
  fadeUp('.testimonial-swiper');
  fadeUp('.booking .bk-card, .booking .bk-policy, .summary', null, { y: 32 });
  fadeUp('.blog .row', '.post', { y: 46, stagger: .1 });
  fadeUp('.faq-acc', '.accordion-item', { y: 20, stagger: .06 });
  fadeUp('.contact__cards', '.c-card', { y: 26, stagger: .07 });
  fadeUp('.map-card');
  fadeUp('.footer__cta', ':scope > *', { y: 26 });

  /* ---------- Magnetic buttons (mouse/trackpad only) ---------- */
  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    $$('.magnetic').forEach((btn) => {
      const xTo = gsap.quickTo(btn, 'x', { duration: .4 });
      const yTo = gsap.quickTo(btn, 'y', { duration: .4 });
      btn.addEventListener('mousemove', (e) => {
        const r = btn.getBoundingClientRect();
        xTo((e.clientX - r.left - r.width / 2) * .25);
        yTo((e.clientY - r.top - r.height / 2) * .35);
      });
      btn.addEventListener('mouseleave', () => { xTo(0); yTo(0); });
    });
  }
})();
