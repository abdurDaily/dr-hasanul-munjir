<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesImages;
use App\Http\Controllers\Controller;
use App\Models\DentistProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use SavesImages;

    private const IMAGES = [
        'hero_image' => ['profile', 'hero'],
        'portrait_image' => ['profile', 'portrait'],
        'about_image' => ['profile', 'photo'],
        'about_image_secondary' => ['profile', 'photo'],
    ];

    public function edit(): View
    {
        return view('admin.profile.edit', ['profile' => DentistProfile::query()->firstOrNew()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = DentistProfile::query()->firstOrNew();
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'credentials_line' => ['nullable', 'string', 'max:120'],
            'role_line' => ['nullable', 'string', 'max:255'],
            'specialty_line' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'qualifications_summary' => ['nullable', 'string', 'max:500'],
            'expertise_summary' => ['nullable', 'string', 'max:500'],
            'philosophy_quote' => ['nullable', 'string', 'max:500'],
            'bmdc_reg_no' => ['nullable', 'string', 'max:30'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'specializations' => ['nullable', 'string', 'max:1000'],
            'hero_image' => self::imageRule(),
            'portrait_image' => self::imageRule(),
            'about_image' => self::imageRule(),
            'about_image_alt' => ['nullable', 'string', 'max:255'],
            'about_image_secondary' => self::imageRule(),
            'about_image_secondary_alt' => ['nullable', 'string', 'max:255'],
        ]);
        $data['specializations'] = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($data['specializations'] ?? '')))));

        $profile->fill($this->withImages($request, $data, self::IMAGES, $profile))->save();

        return back()->with('success', 'Profile saved.');
    }
}
