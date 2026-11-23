<?php

use App\Services\ImageService;
use App\Services\Settings;
use Illuminate\Support\HtmlString;

if (! function_exists('setting')) {
    function setting(string $key, ?string $default = null): ?string
    {
        return app(Settings::class)->get($key, $default);
    }
}

if (! function_exists('emphasis')) {
    /**
     * Escapes text, then turns *words* into the italic accent <em> used in headings.
     */
    function emphasis(?string $text): HtmlString
    {
        $escaped = e((string) $text);

        return new HtmlString(preg_replace('/\*(.+?)\*/u', '<em>$1</em>', $escaped));
    }
}

if (! function_exists('image_url')) {
    function image_url(?string $base, string $preset, ?int $width = null): ?string
    {
        return ImageService::url($base, $preset, $width);
    }
}

if (! function_exists('tel_href')) {
    /** "01690-106079" → "tel:+8801690106079" */
    function tel_href(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($digits, '01')) {
            $digits = '88'.$digits;
        }

        return 'tel:+'.$digits;
    }
}
