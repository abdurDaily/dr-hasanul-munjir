// Strip unused rules from the vendor CSS (full originals live in tools/vendor/).
// Run from the project root:  node tools/purge-css.cjs   (needs: npm i -g purgecss  or  npx --package purgecss)
module.exports = {
  // Only our own markup/scripts — vendor JS contains every class name and would defeat the purge
  content: ['index.html', 'assets/js/main.js', 'assets/js/interactions.js', 'assets/js/animations.js'],
  // Classes added at runtime by Bootstrap / Swiper / our scripts
  safelist: {
    standard: ['show', 'showing', 'hiding', 'fade', 'collapsing', 'collapsed', 'active', 'disabled', 'was-validated', 'is-invalid', 'is-valid'],
    greedy: [/^swiper/, /^offcanvas/, /^accordion/, /^collapse/, /^btn-close/, /^spinner-border/, /^invalid-feedback/, /^form-(control|select|label)/],
  },
  variables: true,
  keyframes: true,
  fontFace: true,
};
