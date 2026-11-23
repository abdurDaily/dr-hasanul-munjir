// Strip unused rules from the vendor CSS (full originals live in tools/vendor/).
// Run from the project root:  node tools/purge-css.cjs   (needs: npm i -g purgecss  or  npx --package purgecss)
module.exports = {
  // Only our own markup/scripts — vendor JS contains every class name and would defeat the purge
  content: [
    'resources/views/site/**/*.blade.php', 'resources/views/layouts/site.blade.php', 'resources/views/components/*.blade.php' /* public components only; admin uses full Bootstrap */,
    'vendor/laravel/framework/src/Illuminate/Pagination/resources/views/bootstrap-5.blade.php',
    'public/assets/js/main.js', 'public/assets/js/interactions.js', 'public/assets/js/animations.js',
  ],
  // Classes added at runtime by Bootstrap / Swiper / our scripts
  safelist: {
    standard: ['show', 'showing', 'hiding', 'fade', 'collapsing', 'collapsed', 'active', 'disabled', 'was-validated', 'is-invalid', 'is-valid'],
    greedy: [/^swiper/, /^offcanvas/, /^accordion/, /^collapse/, /^btn-close/, /^spinner-border/, /^invalid-feedback/, /^form-(control|select|label)/],
  },
  variables: true,
  keyframes: true,
  fontFace: true,
};
