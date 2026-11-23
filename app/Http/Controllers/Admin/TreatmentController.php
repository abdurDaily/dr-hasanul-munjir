<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesImages;
use App\Http\Controllers\Controller;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Services\HtmlCleaner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TreatmentController extends Controller
{
    use SavesImages;

    private const IMAGES = ['image' => ['treatments', 'treatment']];

    public function index(Request $request): View
    {
        $treatments = Treatment::query()
            ->with('category:id,name')
            ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->when($request->query('category'), fn ($q, $id) => $q->where('treatment_category_id', $id))
            ->ordered()->paginate(20)->withQueryString();

        return view('admin.treatments.index', ['treatments' => $treatments, 'categories' => TreatmentCategory::query()->orderBy('sort_order')->pluck('name', 'id')]);
    }

    public function create(): View
    {
        return $this->form(new Treatment(['is_active' => true, 'is_featured' => true, 'icon' => 'bi-shield-plus', 'sort_order' => Treatment::query()->max('sort_order') + 1]));
    }

    public function store(Request $request, HtmlCleaner $cleaner): RedirectResponse
    {
        $treatment = Treatment::create($this->data($request, $cleaner));

        return redirect()->route('admin.treatments.edit', $treatment)->with('success', 'Treatment created.');
    }

    public function edit(Treatment $treatment): View
    {
        return $this->form($treatment);
    }

    public function update(Request $request, Treatment $treatment, HtmlCleaner $cleaner): RedirectResponse
    {
        $treatment->update($this->data($request, $cleaner, $treatment));

        return back()->with('success', 'Treatment saved.');
    }

    public function destroy(Treatment $treatment): RedirectResponse
    {
        $treatment->delete(); // soft delete: past appointments keep their treatment

        return redirect()->route('admin.treatments.index')->with('success', 'Treatment moved to trash.');
    }

    private function form(Treatment $treatment): View
    {
        return view('admin.treatments.form', ['treatment' => $treatment, 'categories' => TreatmentCategory::query()->orderBy('sort_order')->pluck('name', 'id')]);
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
        ]);
        $data['description'] = $cleaner->clean($data['description'] ?? null);

        return $this->withImages($request, $data, self::IMAGES, $treatment);
    }
}
