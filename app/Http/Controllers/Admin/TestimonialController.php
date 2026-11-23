<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesImages;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\Treatment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    use SavesImages;

    private const IMAGES = ['avatar' => ['testimonials', 'avatar']];

    public function index(): View
    {
        return view('admin.testimonials.index', ['testimonials' => Testimonial::query()->with('treatment:id,name')->ordered()->paginate(20)]);
    }

    public function create(): View
    {
        return $this->form(new Testimonial(['rating' => 5, 'source' => 'google', 'reviewed_on' => today()]));
    }

    public function store(Request $request): RedirectResponse
    {
        Testimonial::create($this->data($request));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return $this->form($testimonial);
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->data($request, $testimonial));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial saved.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->deleteImages($testimonial, self::IMAGES);
        $testimonial->forceDelete(); // remove patient data completely

        return back()->with('success', 'Testimonial deleted.');
    }

    private function form(Testimonial $testimonial): View
    {
        return view('admin.testimonials.form', ['testimonial' => $testimonial, 'treatments' => Treatment::query()->ordered()->pluck('name', 'id')]);
    }

    private function data(Request $request, ?Testimonial $testimonial = null): array
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:80'],
            'body' => ['required', 'string', 'max:1500'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5', 'multiple_of:0.5'],
            'treatment_id' => ['nullable', 'exists:treatments,id'],
            'reviewed_on' => ['nullable', 'date', 'before_or_equal:today'],
            'source' => ['required', Rule::in(array_keys(Testimonial::SOURCES))],
            'avatar' => self::imageRule(),
            'consent_confirmed' => ['boolean'],
            // Only genuine reviews the patient agreed to publish may go live
            'is_active' => ['boolean', Rule::prohibitedIf(fn () => $request->boolean('is_active') && ! $request->boolean('consent_confirmed'))],
            'is_featured' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ], ['is_active.prohibited' => 'Confirm the patient agreed to publish this review before showing it.']);

        return $this->withImages($request, $data, self::IMAGES, $testimonial);
    }
}
