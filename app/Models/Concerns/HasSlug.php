<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Fills a unique `slug` from the model's name/title when none is given.
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            $source = $model->slug ?: ($model->name ?? $model->title);
            $base = Str::slug($source) ?: Str::lower(Str::random(8));
            if ($model->slug === $base && ! $model->isDirty('slug') && $model->exists) {
                return;
            }

            $slug = $base;
            $i = 2;
            $query = fn ($s) => static::query()
                ->when(method_exists($model, 'trashed'), fn ($q) => $q->withTrashed())
                ->where('slug', $s)
                ->when($model->exists, fn ($q) => $q->whereKeyNot($model->getKey()))
                ->exists();
            while ($query($slug)) {
                $slug = "{$base}-".$i++;
            }
            $model->slug = $slug;
        });
    }
}
