<?php

/*
| Responsive image presets. Each upload is re-encoded to WebP at these widths
| ("<path>-<width>.webp"); the stored value is the shared <path> prefix.
*/

return [
    'disk' => 'public',
    'quality' => 78,
    'max_upload_kb' => 5120,

    'presets' => [
        'hero' => [480, 600, 720, 960],
        'portrait' => [160, 320],
        'photo' => [600, 900, 1400],
        'treatment' => [640, 960],
        'compare' => [420, 700, 1000],
        'blog' => [420, 700, 1000, 1400],
        'avatar' => [96],
        'certificate' => [600, 1200],
        'og' => [1200],
    ],
];
