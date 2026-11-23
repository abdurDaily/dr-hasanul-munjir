<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait SavesImages
{
    /**
     * Applies uploaded files to $data: [field => [directory, preset]]. Existing images are
     * replaced (old files deleted) only when a new file was uploaded.
     */
    protected function withImages(Request $request, array $data, array $fields, ?Model $model = null): array
    {
        $images = app(ImageService::class);
        foreach ($fields as $field => [$directory, $preset]) {
            unset($data[$field]);
            if ($request->hasFile($field)) {
                $data[$field] = $images->replace($model?->{$field}, $request->file($field), $directory, $preset);
            }
        }

        return $data;
    }

    protected function deleteImages(Model $model, array $fields): void
    {
        foreach ($fields as $field => [, $preset]) {
            app(ImageService::class)->delete($model->{$field}, $preset);
        }
    }

    /** Validation rule for an optional/required image upload. */
    protected static function imageRule(bool $required = false): array
    {
        return [$required ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('images.max_upload_kb'), 'dimensions:min_width=200,min_height=200,max_width=8000,max_height=8000'];
    }
}
