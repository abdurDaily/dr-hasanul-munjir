// Strip unused rules from the vendor CSS (full originals live in tools/vendor/).
// Run from the project root:  node tools/purge-css.cjs   (needs: npm i -g purgecss  or  npx --package purgecss)
module.exports = {
  content: ['index.html', 'assets/js/*.js'],
  // Classes added at runtime by Bootstrap / Swiper / our scripts
  safelist: {
    standard: ['show', 'showing', 'hiding', 'fade', 'collapsing', 'collapsed', 'active', 'disabled', 'was-validated', 'is-invalid', 'is-valid'],
    greedy: [/^swiper/, /^offcanvas/, /^accordion/, /^collapse/, /^btn-close/, /^spinner-border/, /^invalid-feedback/, /^form-/],
  },
  variables: true,
  keyframes: true,
  fontFace: true,
};
