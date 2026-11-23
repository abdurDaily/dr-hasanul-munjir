// Bundle + minify the render-blocking CSS into one request: public/assets/css/site.min.css
// Edit the source files (style.css, responsive.css), then run from the project root:
//   node tools/build-css.cjs        (needs the `csso` package: npm i csso)
// Swiper's full CSS is NOT bundled — interactions.js loads it together with Swiper.
const fs = require('fs');
const { minify } = require('csso');

const parts = [
  'public/assets/css/bootstrap.min.css', // purged by tools/purge-css.cjs
  'public/assets/css/icons.min.css',     // subset by tools/build_icons.py
  'public/assets/css/style.css',
  'public/assets/css/responsive.css',
];

const source = parts.map((f) => fs.readFileSync(f, 'utf8')).join('\n');
const { css } = minify(source, { restructure: false }); // keep rule order (media-query overrides)
fs.writeFileSync('public/assets/css/site.min.css', `/* Built by tools/build-css.cjs — do not edit; edit style.css / responsive.css */\n${css}`);
console.log(`site.min.css: ${(source.length / 1024) | 0} KB -> ${(css.length / 1024) | 0} KB`);
