<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\FaqCategory;
use App\Models\GalleryCategory;
use App\Models\TreatmentCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * One screen for every simple name/slug list (categories and tags). Items in use are not
 * deleted with their content: the foreign keys set the content's category to "none".
 */
class TaxonomyController extends Controller
{
    private const TYPES = [
        'treatment-categories' => [TreatmentCategory::class, 'treatments.manage', 'Treatment Categories', 'treatments', 'admin.treatments.index'],
        'blog-categories' => [BlogCategory::class, 'blog.manage', 'Blog Categories', 'posts', 'admin.posts.index'],
        'blog-tags' => [BlogTag::class, 'blog.manage', 'Blog Tags', 'posts', 'admin.posts.index'],
        'gallery-categories' => [GalleryCategory::class, 'gallery.manage', 'Gallery Categories', 'photos', 'admin.clinic-photos.index'],
        'faq-categories' => [FaqCategory::class, 'content.manage', 'FAQ Categories', 'faqs', 'admin.faqs.index'],
    ];

    public function index(Request $request, string $taxonomy): View
    {
        [$model, , $title, $relation, $back] = $this->type($request, $taxonomy);
        $hasOrder = $this->hasOrder($model);

        return view('admin.taxonomies.index', [
            'taxonomy' => $taxonomy,
            'title' => $title,
            'back' => route($back),
            'hasOrder' => $hasOrder,
            'hasDescription' => $model === BlogCategory::class,
            'items' => $model::query()->withCount($relation)->when($hasOrder, fn ($q) => $q->orderBy('sort_order'))->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $taxonomy): RedirectResponse
    {
        [$model] = $this->type($request, $taxonomy);
        $model::create($this->data($request, $model));

        return back()->with('success', 'Added.');
    }

    public function update(Request $request, string $taxonomy, int $id): RedirectResponse
    {
        [$model] = $this->type($request, $taxonomy);
        $item = $model::query()->findOrFail($id);
        $item->update($this->data($request, $model, $item));

        return back()->with('success', 'Saved.');
    }

    public function destroy(Request $request, string $taxonomy, int $id): RedirectResponse
    {
        [$model] = $this->type($request, $taxonomy);
        $model::query()->findOrFail($id)->delete();

        return back()->with('success', 'Deleted.');
    }

    private function type(Request $request, string $taxonomy): array
    {
        $type = self::TYPES[$taxonomy] ?? abort(404);
        abort_unless($request->user()->can($type[1]), 403);

        return $type;
    }

    private function hasOrder(string $model): bool
    {
        return in_array('sort_order', (new $model)->getFillable(), true);
    }

    private function data(Request $request, string $model, ?Model $item = null): array
    {
        $table = (new $model)->getTable();

        return $request->validate(array_filter([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'alpha_dash', 'max:100', Rule::unique($table)->ignore($item)],
            'sort_order' => $this->hasOrder($model) ? ['nullable', 'integer', 'min:0'] : null,
            'description' => $model === BlogCategory::class ? ['nullable', 'string', 'max:300'] : null,
        ])) + ($this->hasOrder($model) ? ['sort_order' => (int) $request->input('sort_order', 0)] : []);
    }
}
