<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesImages;
use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Images inserted into rich-text content (blog posts, treatment descriptions). */
class EditorImageController extends Controller
{
    use SavesImages;

    public function __invoke(Request $request, ImageService $images): JsonResponse
    {
        abort_unless($request->user()->canAny(['blog.manage', 'treatments.manage', 'settings.manage']), 403);
        $request->validate(['image' => self::imageRule(true)]);

        $path = $images->store($request->file('image'), 'editor', 'blog');

        // Root-relative, so stored content keeps working if the domain changes
        return response()->json(['url' => parse_url(ImageService::url($path, 'blog', 1000), PHP_URL_PATH)]);
    }
}
