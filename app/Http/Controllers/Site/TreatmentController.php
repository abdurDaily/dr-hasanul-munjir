<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Services\Seo;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TreatmentController extends Controller
{
    public function index(Seo $seo): View
    {
        $categories = TreatmentCategory::query()->orderBy('sort_order')
            ->with(['treatments' => fn ($q) => $q->active()->ordered()])
            ->get()->filter(fn ($c) => $c->treatments->isNotEmpty());
        $uncategorised = Treatment::query()->active()->ordered()->whereNull('treatment_category_id')->get();

        return view('site.treatments.index', [
            'categories' => $categories,
            'uncategorised' => $uncategorised,
            'seo' => [
                'title' => 'Dental Treatments | '.setting('clinic_name'),
                'description' => setting('treatments_aside'),
                'schema' => [$seo->breadcrumbs([['Home', route('home')], ['Treatments', route('treatments.index')]])],
            ],
        ]);
    }

    public function show(string $slug, Seo $seo): View
    {
        $treatment = Treatment::query()->active()->where('slug', $slug)->with('category:id,name')->firstOrFail();
        // Same category first, then the usual display order
        $related = Treatment::query()->active()->whereKeyNot($treatment->id)
            ->orderByRaw('treatment_category_id <=> ? desc', [$treatment->treatment_category_id])
            ->ordered()->limit(3)->get();

        return view('site.treatments.show', [
            'treatment' => $treatment,
            'related' => $related,
            'selectedTreatment' => $treatment,
            'seo' => [
                'title' => $treatment->seo_title ?: "{$treatment->name} in Chattogram | ".setting('clinic_name'),
                'description' => $treatment->seo_description ?: Str::limit($treatment->short_description, 155),
                'image' => image_url($treatment->image, 'treatment'),
                'image_alt' => $treatment->image_alt,
                'schema' => [
                    $seo->treatmentSchema($treatment),
                    $seo->breadcrumbs([['Home', route('home')], ['Treatments', route('treatments.index')], [$treatment->name, route('treatments.show', $treatment->slug)]]),
                ],
            ],
        ]);
    }
}
