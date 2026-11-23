<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesImages;
use App\Http\Controllers\Controller;
use App\Models\SmileCase;
use App\Models\Treatment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmileCaseController extends Controller
{
    use SavesImages;

    private const IMAGES = ['before_image' => ['smile-cases', 'compare'], 'after_image' => ['smile-cases', 'compare']];

    public function index(): View
    {
        return view('admin.smile-cases.index', ['cases' => SmileCase::query()->with('treatment:id,name')->ordered()->paginate(20)]);
    }

    public function create(): View
    {
        return $this->form(new SmileCase(['sort_order' => SmileCase::query()->max('sort_order') + 1]));
    }

    public function store(Request $request): RedirectResponse
    {
        SmileCase::create($this->data($request));

        return redirect()->route('admin.smile-cases.index')->with('success', 'Case added.');
    }

    public function edit(SmileCase $smileCase): View
    {
        return $this->form($smileCase);
    }

    public function update(Request $request, SmileCase $smileCase): RedirectResponse
    {
        $smileCase->update($this->data($request, $smileCase));

        return redirect()->route('admin.smile-cases.index')->with('success', 'Case saved.');
    }

    public function destroy(SmileCase $smileCase): RedirectResponse
    {
        $this->deleteImages($smileCase, self::IMAGES);
        $smileCase->forceDelete(); // patient photos are removed completely

        return back()->with('success', 'Case and its photos deleted.');
    }

    private function form(SmileCase $case): View
    {
        return view('admin.smile-cases.form', ['case' => $case, 'treatments' => Treatment::query()->ordered()->pluck('name', 'id')]);
    }

    private function data(Request $request, ?SmileCase $case = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'treatment_id' => ['nullable', 'exists:treatments,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration' => ['nullable', 'string', 'max:60'],
            'before_image' => self::imageRule($case === null),
            'after_image' => self::imageRule($case === null),
            'consent_confirmed' => ['boolean'],
            'consent_date' => ['nullable', 'required_if_accepted:consent_confirmed', 'date', 'before_or_equal:today'],
            'consent_note' => ['nullable', 'string', 'max:500'],
            // Patient results are never published without recorded written consent
            'is_active' => ['boolean', Rule::prohibitedIf(fn () => $request->boolean('is_active') && ! $request->boolean('consent_confirmed'))],
            'is_featured' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ], [
            'is_active.prohibited' => 'Record the patient\'s written consent before publishing this case.',
            'consent_date.required_if_accepted' => 'Enter the date the patient signed the consent.',
        ]);

        return $this->withImages($request, $data, self::IMAGES, $case);
    }
}
