"""Generate responsive WebP versions of the site's local images.

Run from the project root:  python tools/optimize_images.py
Re-run whenever a source photo in assets/images is replaced.
Output files are written next to the source as <name>-<width>.webp.
"""
from pathlib import Path
from PIL import Image

IMG = Path("assets/images")

# source file -> widths to generate (largest first)
JOBS = {
    "dr-munjir-hero.png": [960, 720, 480],      # hero cut-out (keeps transparency)
    "dr-munjir-treatment.jpg": [900, 600],
    "dr-munjir-scaling.jpg": [900, 600],
    "dr-munjir-procedure.jpg": [900, 600],
    "endodontics-training.jpg": [900, 600],
    "clinic-treatment-room.jpg": [900, 600],
    "dr-munjir-portrait.jpg": [160],            # booking-summary thumbnail
}
TREATMENTS = sorted((IMG / "treatments").glob("*.jpg"))


def save_webp(src: Path, width: int, quality: int = 78) -> None:
    with Image.open(src) as im:
        im = im.convert("RGBA" if im.mode in ("RGBA", "LA", "P") else "RGB")
        if im.width > width:
            im = im.resize((width, round(im.height * width / im.width)), Image.LANCZOS)
        out = src.with_name(f"{src.stem}-{width}.webp")
        im.save(out, "WEBP", quality=quality, method=6)
        print(f"{out}  {out.stat().st_size // 1024} KB")


for name, widths in JOBS.items():
    for w in widths:
        save_webp(IMG / name, w)

for src in TREATMENTS:
    save_webp(src, 640, quality=74)
