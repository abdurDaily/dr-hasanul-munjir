<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesImages;
use App\Http\Controllers\Controller;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Models\TreatmentImage;
use App\Services\HtmlCleaner;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TreatmentController extends Controller
{
    use SavesImages;

    private const IMAGES = ['image' => ['treatments', 'treatment']];

    public function index(Request $request): View
    {
        $treatments = Treatment::query()
            ->when($request->boolean('trashed'), fn ($q) => $q->onlyTrashed())
            ->with('category:id,name')
            ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->when($request->query('category'), fn ($q, $id) => $q->where('treatment_category_id', $id))
            ->ordered()->paginate(20)->withQueryString();

        return view('admin.treatments.index', [
            'treatments' => $treatments,
            'categories' => TreatmentCategory::query()->orderBy('sort_order')->pluck('name', 'id'),
            'trashedCount' => Treatment::onlyTrashed()->count(),
        ]);
    }

    /** Deleted treatments are kept (past appointments reference them) and can be restored. */
    public function restore(int $id): RedirectResponse
    {
        $treatment = Treatment::onlyTrashed()->findOrFail($id);
        $treatment->restore();

        return redirect()->route('admin.treatments.edit', $treatment)->with('success', "{$treatment->name} restored — it is visible again if “Show on website” is on.");
    }

    public function create(): View
    {
        return $this->form(new Treatment(['is_active' => true, 'is_featured' => true, 'icon' => 'bi-shield-plus', 'sort_order' => Treatment::query()->max('sort_order') + 1]));
    }

    public function store(Request $request, HtmlCleaner $cleaner): RedirectResponse
    {
        $data = $this->data($request, $cleaner);
        $treatment = DB::transaction(function () use ($request, $data) {
            $treatment = Treatment::create($data);
            $this->syncGallery($request, $treatment);

            return $treatment;
        });

        return redirect()->route('admin.treatments.edit', $treatment)->with('success', 'Treatment created.');
    }

    public function edit(Treatment $treatment): View
    {
        return $this->form($treatment);
    }

    public function update(Request $request, Treatment $treatment, HtmlCleaner $cleaner): RedirectResponse
    {
        $data = $this->data($request, $cleaner, $treatment);
        DB::transaction(function () use ($request, $treatment, $data) {
            $treatment->update($data);
            $this->syncGallery($request, $treatment);
        });

        return back()->with('success', 'Treatment saved.');
    }

    public function destroy(Treatment $treatment): RedirectResponse
    {
        $treatment->delete(); // soft delete: past appointments keep their treatment

        return redirect()->route('admin.treatments.index')->with('success', 'Treatment moved to trash.');
    }

    private function form(Treatment $treatment): View
    {
        return view('admin.treatments.form', [
            'treatment' => $treatment,
            'gallery' => $treatment->exists ? $treatment->images()->get() : collect(),
            'categories' => TreatmentCategory::query()->orderBy('sort_order')->pluck('name', 'id'),
        ]);
    }

    private function data(Request $request, HtmlCleaner $cleaner, ?Treatment $treatment = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('treatments')->ignore($treatment)],
            'treatment_category_id' => ['nullable', 'exists:treatment_categories,id'],
            'short_description' => ['required', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:60000'],
            'image' => self::imageRule($treatment === null),
            'image_alt' => ['nullable', 'string', 'max:255'],
            'icon' => ['required', Rule::in(config('icons'))],
            'duration' => ['nullable', 'string', 'max:60'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'is_speciality' => ['boolean'],
            'is_active' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            // Detail-page gallery: new uploads + edits to existing images
            'gallery_new' => ['nullable', 'array', 'max:'.TreatmentImage::MAX_PER_UPLOAD],
            'gallery_new.*' => self::imageRule(true),
            'gallery' => ['nullable', 'array'],
            'gallery.*.caption' => ['nullable', 'string', 'max:160'],
            'gallery.*.alt' => ['nullable', 'string', 'max:255'],
            'gallery.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'gallery.*.delete' => ['nullable', 'boolean'],
        ], [
            'gallery_new.max' => 'You can upload up to '.TreatmentImage::MAX_PER_UPLOAD.' images at a time.',
            'gallery_new.*.image' => 'Each gallery file must be a JPG, PNG or WebP image.',
            'gallery_new.*.mimes' => 'Each gallery file must be a JPG, PNG or WebP image.',
            'gallery_new.*.max' => 'Each gallery image must be under '.round(config('images.max_upload_kb') / 1024).' MB.',
        ]);

        $kept = $treatment ? $treatment->images()->count() - collect($data['gallery'] ?? [])->filter(fn ($g) => ! empty($g['delete']))->count() : 0;
        if ($kept + count($data['gallery_new'] ?? []) > TreatmentImage::MAX_PER_TREATMENT) {
            throw ValidationException::withMessages(['gallery_new' => 'A treatment can have at most '.TreatmentImage::MAX_PER_TREATMENT.' gallery images.']);
        }
        unset($data['gallery_new'], $data['gallery']);
        $data['description'] = $cleaner->clean($data['description'] ?? null);

        return $this->withImages($request, $data, self::IMAGES, $treatment);
    }

    /** Applies caption/order edits and deletions to existing gallery images, then stores new uploads. */
    private function syncGallery(Request $request, Treatment $treatment): void
    {
        $images = app(ImageService::class);
        $existing = $treatment->images()->get()->keyBy('id');

        foreach ((array) $request->input('gallery', []) as $id => $fields) {
            $image = $existing->get((int) $id); // only this treatment's images can be touched
            if (! $image) {
                continue;
            }
            if (! empty($fields['delete'])) {
                $images->delete($image->image, 'photo');
                $image->delete();

                continue;
            }
            $image->update([
                'caption' => $fields['caption'] ?? null,
                'alt' => $fields['alt'] ?? null,
                'sort_order' => (int) ($fields['sort_order'] ?? $image->sort_order),
            ]);
        }

        $next = (int) $treatment->images()->max('sort_order') + 1;
        foreach ($request->file('gallery_new', []) as $file) {
            $treatment->images()->create([
                'image' => $images->store($file, 'treatments/gallery', 'photo'),
                'alt' => $treatment->name,
                'sort_order' => $next++,
            ]);
        }
    }
}
