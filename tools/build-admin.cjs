// Copies the admin's third-party libraries into public/admin-assets/vendor (committed, so production needs no Node build).
// Run from the project root after updating the packages:  npm run build:admin
const fs = require('fs');
const path = require('path');

const OUT = 'public/admin-assets/vendor';
const files = {
  'node_modules/bootstrap/dist/css/bootstrap.min.css': 'bootstrap.min.css',
  'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js': 'bootstrap.bundle.min.js',
  'tools/vendor/bootstrap-icons.min.css': 'bootstrap-icons.min.css',
  'tools/vendor/bootstrap-icons.woff2': 'fonts/bootstrap-icons.woff2',
  'node_modules/sweetalert2/dist/sweetalert2.min.css': 'sweetalert2.min.css',
  'node_modules/sweetalert2/dist/sweetalert2.min.js': 'sweetalert2.min.js',
  'node_modules/quill/dist/quill.snow.css': 'quill.snow.css',
  'node_modules/quill/dist/quill.js': 'quill.js',
};

for (const [from, to] of Object.entries(files)) {
  const dest = path.join(OUT, to);
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(from, dest);
  console.log(`${to}  ${(fs.statSync(dest).size / 1024) | 0} KB`);
}

// The icon CSS expects its font in ./fonts/ — point it there
const iconsCss = path.join(OUT, 'bootstrap-icons.min.css');
fs.writeFileSync(iconsCss, fs.readFileSync(iconsCss, 'utf8').replace(/url\("?[^")]*bootstrap-icons\.woff2[^")]*"?\)/g, 'url("fonts/bootstrap-icons.woff2")').replace(/,\s*url\("?[^")]*bootstrap-icons\.woff[^2][^)]*\)\s*format\("woff"\)/g, ''));
