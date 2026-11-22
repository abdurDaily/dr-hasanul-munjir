// Writes purged copies of the vendor CSS into assets/css/.
// Run from the project root:  npx --yes --package purgecss@6 node tools/purge-css.cjs
const fs = require('fs');
const path = require('path');
const { PurgeCSS } = require('purgecss');
const config = require('./purgecss.config.cjs');

const files = ['bootstrap.min.css', 'swiper-bundle.min.css'];

(async () => {
  for (const name of files) {
    const [result] = await new PurgeCSS().purge({ ...config, css: [path.posix.join('tools/vendor', name)] });
    fs.writeFileSync(path.join('assets/css', name), result.css);
    console.log(`${name}: ${(fs.statSync(path.join('tools/vendor', name)).size / 1024) | 0} KB -> ${(result.css.length / 1024) | 0} KB`);
  }
})();
