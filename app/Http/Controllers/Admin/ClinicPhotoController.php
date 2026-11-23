<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesImages;
use App\Http\Controllers\Controller;
use App\Models\ClinicPhoto;
use App\Models\GalleryCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicPhotoController extends Controller
{
    use SavesImages;

    private const IMAGES = ['image' => ['clinic', 'photo']];

    public function index(Request $request): View
    {
        $photos = ClinicPhoto::query()->with('category:id,name')
            ->when($request->query('category'), fn ($q, $id) => $q->where('gallery_category_id', $id))
            ->ordered()->paginate(24)->withQueryString();

        return view('admin.clinic-photos.index', ['photos' => $photos, 'categories' => GalleryCategory::query()->orderBy('sort_order')->pluck('name', 'id')]);
    }

    public function create(): View
    {
        return $this->form(new ClinicPhoto(['is_active' => true, 'show_in_slider' => true, 'sort_order' => ClinicPhoto::query()->max('sort_order') + 1]));
    }

    public function store(Request $request): RedirectResponse
    {
        ClinicPhoto::create($this->data($request));

        return redirect()->route('admin.clinic-photos.index')->with('success', 'Photo added.');
    }

    public function edit(ClinicPhoto $clinicPhoto): View
    {
        return $this->form($clinicPhoto);
    }

    public function update(Request $request, ClinicPhoto $clinicPhoto): RedirectResponse
    {
        $clinicPhoto->update($this->data($request, $clinicPhoto));

        return redirect()->route('admin.clinic-photos.index')->with('success', 'Photo saved.');
    }

    public function destroy(ClinicPhoto $clinicPhoto): RedirectResponse
    {
        $this->deleteImages($clinicPhoto, self::IMAGES);
        $clinicPhoto->forceDelete();

        return back()->with('success', 'Photo deleted.');
    }

    private function form(ClinicPhoto $photo): View
    {
        return view('admin.clinic-photos.form', ['photo' => $photo, 'categories' => GalleryCategory::query()->orderBy('sort_order')->pluck('name', 'id')]);
    }

    private function data(Request $request, ?ClinicPhoto $photo = null): array
    {
        $data = $request->validate([
            'image' => self::imageRule($photo === null),
            'title' => ['required', 'string', 'max:80'],
            'subtitle' => ['nullable', 'string', 'max:120'],
            'alt' => ['required', 'string', 'max:255'],
            'gallery_category_id' => ['nullable', 'exists:gallery_categories,id'],
            'show_in_clinic' => ['boolean'],
            'show_in_slider' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        return $this->withImages($request, $data, self::IMAGES, $photo);
    }
}
