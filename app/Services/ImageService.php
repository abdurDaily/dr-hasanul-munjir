<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Stores uploaded images as responsive WebP variants. Re-encoding every upload also
 * strips metadata (EXIF/GPS) and any non-image payload hidden in the original file.
 */
class ImageService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * @return string base path, e.g. "treatments/01jh…" (variants are "<base>-<width>.webp")
     */
    public function store(UploadedFile|string $file, string $directory, string $preset): string
    {
        $widths = self::widths($preset);
        $base = trim($directory, '/').'/'.Str::lower((string) Str::ulid());
        $source = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        foreach ($widths as $width) {
            $image = $this->manager->read($source)->scaleDown(width: $width);
            Storage::disk(config('images.disk'))->put("{$base}-{$width}.webp", (string) $image->toWebp(config('images.quality')));
        }

        return $base;
    }

    /** Replace an existing image: stores the new one and deletes the old variants. */
    public function replace(?string $old, UploadedFile $file, string $directory, string $preset): string
    {
        $new = $this->store($file, $directory, $preset);
        $this->delete($old, $preset);

        return $new;
    }

    public function delete(?string $base, string $preset): void
    {
        if (! $base) {
            return;
        }
        Storage::disk(config('images.disk'))->delete(array_map(fn ($w) => "{$base}-{$w}.webp", self::widths($preset)));
    }

    public static function widths(string $preset): array
    {
        return config("images.presets.{$preset}") ?? throw new \InvalidArgumentException("Unknown image preset [{$preset}]");
    }

    public static function url(?string $base, string $preset, ?int $width = null): ?string
    {
        if (! $base) {
            return null;
        }
        $widths = self::widths($preset);
        $width = $width && in_array($width, $widths, true) ? $width : end($widths);

        return Storage::disk(config('images.disk'))->url("{$base}-{$width}.webp");
    }

    public static function srcset(?string $base, string $preset): ?string
    {
        if (! $base) {
            return null;
        }

        return collect(self::widths($preset))
            ->map(fn ($w) => self::url($base, $preset, $w)." {$w}w")
            ->implode(', ');
    }
}
