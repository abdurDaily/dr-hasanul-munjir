# Build tools

The site is plain HTML/CSS/JS. These small scripts produce the optimised files it serves.
Run them from the project root after editing the matching sources.

| When you change… | Run | Produces |
|---|---|---|
| `assets/css/style.css` or `responsive.css` | `node tools/build-css.cjs` | `assets/css/site.min.css` (the only stylesheet the page loads) |
| classes used in `index.html` / JS (new Bootstrap utilities etc.) | `node tools/purge-css.cjs` then `node tools/build-css.cjs` | purged `assets/css/bootstrap.min.css`, `swiper-bundle.min.css` |
| a `bi-*` icon added or removed | `python tools/build_icons.py` then `node tools/build-css.cjs` | `assets/fonts/bootstrap-icons-subset.woff2`, `assets/css/icons.min.css` |
| a photo in `assets/images/` | `python tools/optimize_images.py` | responsive `*-<width>.webp` files |
| copy with new special characters (e.g. €, ±) | add them to `UNICODES` in `tools/subset_fonts.py`, then run it | subset fonts in `assets/fonts/` |

Dependencies: `npm i purgecss@6 csso@5` (Node) and `pip install pillow fonttools brotli` (Python).
Full, unpurged vendor files are kept in `tools/vendor/`.
