"""Subset the self-hosted web fonts to the characters the site uses.

Run from the project root:  python tools/subset_fonts.py
Needs: pip install fonttools brotli
Sources (Google Fonts latin WOFF2, variable weights) live in tools/vendor/fonts/.
Output overwrites assets/fonts/<same name>. Variable-weight axes are kept.
"""
from pathlib import Path
from fontTools import subset

SRC = Path("tools/vendor/fonts")
OUT = Path("public/assets/fonts")

# Basic Latin + Latin-1 + the typographic punctuation used in the copy
UNICODES = "U+0020-007E,U+00A0-00FF,U+2013-2014,U+2018-201F,U+2022,U+2026,U+2122,U+2192"

for src in sorted(SRC.glob("*.woff2")):
    out = OUT / src.name
    subset.main([
        str(src),
        f"--unicodes={UNICODES}",
        "--flavor=woff2",
        f"--output-file={out}",
        "--layout-features=kern,liga,calt,ccmp,locl,mark,mkmk",
        "--no-hinting",
        "--desubroutinize",
    ])
    print(f"{src.name}: {src.stat().st_size // 1024} KB -> {out.stat().st_size // 1024} KB")
