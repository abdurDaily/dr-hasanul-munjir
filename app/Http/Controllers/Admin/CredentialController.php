<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesImages;
use App\Http\Controllers\Controller;
use App\Models\Credential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Education, registration, experience, certifications, training, workshops and achievements. */
class CredentialController extends Controller
{
    use SavesImages;

    private const IMAGES = ['certificate_image' => ['credentials', 'certificate']];

    public function index(Request $request): View
    {
        $credentials = Credential::query()
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->ordered()->paginate(30)->withQueryString();

        return view('admin.credentials.index', ['credentials' => $credentials]);
    }

    public function create(): View
    {
        return view('admin.credentials.form', ['credential' => new Credential(['is_active' => true, 'show_on_timeline' => true, 'icon' => 'bi-patch-check', 'sort_order' => Credential::query()->max('sort_order') + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Credential::create($this->data($request));

        return redirect()->route('admin.credentials.index')->with('success', 'Saved.');
    }

    public function edit(Credential $credential): View
    {
        return view('admin.credentials.form', compact('credential'));
    }

    public function update(Request $request, Credential $credential): RedirectResponse
    {
        $credential->update($this->data($request, $credential));

        return redirect()->route('admin.credentials.index')->with('success', 'Saved.');
    }

    public function destroy(Credential $credential): RedirectResponse
    {
        $this->deleteImages($credential, self::IMAGES);
        $credential->forceDelete();

        return back()->with('success', 'Deleted.');
    }

    private function data(Request $request, ?Credential $credential = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Credential::TYPES))],
            'label' => ['nullable', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:200'],
            'title_emphasis' => ['nullable', 'string', 'max:200'],
            'institution' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'completed_on' => ['nullable', 'date'],
            'date_note' => ['nullable', 'string', 'max:120'],
            'certificate_image' => self::imageRule(),
            'icon' => ['required', Rule::in(config('icons'))],
            'is_featured' => ['boolean'],
            'show_on_timeline' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        return $this->withImages($request, $data, self::IMAGES, $credential);
    }
}
