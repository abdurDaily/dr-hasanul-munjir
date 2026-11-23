"""Build a tiny Bootstrap Icons subset containing only the icons the site uses.

Run from the project root:  python tools/build_icons.py
Needs: pip install fonttools brotli
Source font + CSS live in tools/vendor/ (full Bootstrap Icons 1.11.3).
Re-run after adding a new `bi-*` icon to a public Blade view, the JS, or config/icons.php.
"""
import re
from pathlib import Path
from fontTools import subset

ROOT = Path(".")
VENDOR = Path("tools/vendor")
full_css = (VENDOR / "bootstrap-icons.min.css").read_text(encoding="utf-8")
glyphs = dict(re.findall(r'\.bi-([a-z0-9-]+)::before\{content:"\\([0-9a-f]+)"\}', full_css))

# Every bi-* class referenced in the page or scripts
# Public pages only (the admin loads the full icon font). config/icons.php lists the icons admins can pick.
sources = [*ROOT.glob("resources/views/site/**/*.blade.php"), ROOT / "resources/views/layouts/site.blade.php",
           *ROOT.glob("resources/views/components/*.blade.php"), *ROOT.glob("public/assets/js/*.js"),
           ROOT / "config/icons.php", ROOT / "database/seeders/ScheduleSeeder.php", ROOT / "database/seeders/ContentSeeder.php"]
used = sorted({m for f in sources for m in re.findall(r"\bbi-([a-z0-9-]+)", f.read_text(encoding="utf-8"))} & glyphs.keys())

subset.main([
    str(VENDOR / "bootstrap-icons.woff2"),
    "--unicodes=" + ",".join(f"U+{glyphs[n]}" for n in used),
    "--flavor=woff2",
    "--output-file=public/assets/fonts/bootstrap-icons-subset.woff2",
    "--no-hinting",
    "--desubroutinize",
])

css = [
    '@font-face{font-display:block;font-family:"bootstrap-icons";'
    'src:url("../fonts/bootstrap-icons-subset.woff2") format("woff2")}',
    '.bi::before,[class^="bi-"]::before,[class*=" bi-"]::before{display:inline-block;font-family:bootstrap-icons!important;'
    'font-style:normal;font-weight:400!important;font-variant:normal;text-transform:none;line-height:1;'
    'vertical-align:-.125em;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}',
]
css += [f'.bi-{n}::before{{content:"\\{glyphs[n]}"}}' for n in used]
Path("public/assets/css/icons.min.css").write_text("".join(css), encoding="utf-8")
print(f"{len(used)} icons ->", Path("public/assets/fonts/bootstrap-icons-subset.woff2").stat().st_size, "bytes font,",
      Path("public/assets/css/icons.min.css").stat().st_size, "bytes css")
