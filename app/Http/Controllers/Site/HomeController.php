<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\ClinicPhoto;
use App\Models\Credential;
use App\Models\Faq;
use App\Models\Highlight;
use App\Models\SmileCase;
use App\Models\Testimonial;
use App\Models\Treatment;
use App\Services\Seo;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const HOME_TREATMENTS = 9;

    public function __invoke(Seo $seo): View
    {
        // Content is cached briefly (and cleared on every admin change). Booking availability is NOT
        // part of this — it is fetched live by the booking form.
        $home = Cache::remember('home-page', now()->addMinutes(10), function () {
            $treatments = Treatment::query()->active()->ordered()->where('is_featured', true)->limit(self::HOME_TREATMENTS)->get();
            $smileCases = SmileCase::query()->published()->ordered()->with('treatment:id,name,slug')->get();

            return [
                'treatments' => $treatments,
                'moreTreatments' => Treatment::query()->active()->count() > $treatments->count(),
                'highlights' => Highlight::query()->active()->ordered()->get(),
                'sliderPhotos' => ClinicPhoto::query()->active()->ordered()->where('show_in_slider', true)->get(),
                'clinicPhotos' => ClinicPhoto::query()->active()->ordered()->where('show_in_clinic', true)->with('category:id,name')->limit(3)->get(),
                'featuredCredential' => Credential::query()->active()->where('is_featured', true)->orderByDesc('completed_on')->first(),
                'timeline' => Credential::query()->active()->ordered()->where('show_on_timeline', true)->get(),
                'smileCases' => $smileCases,
                'galleryFilters' => $smileCases->pluck('treatment')->filter()->unique('id')->pluck('name', 'slug'),
                'testimonials' => Testimonial::query()->published()->ordered()->with('treatment:id,name')->limit(12)->get(),
                'posts' => BlogPost::query()->published()->with('category:id,name,slug')->latest('published_at')->limit(3)->get(),
                'faqs' => Faq::query()->active()->ordered()->limit(12)->get(),
            ];
        });

        return view('site.home', [
            'home' => $home,
            'seo' => ['schema' => [$seo->dentistSchema(), $seo->faqSchema($home['faqs'])]],
        ]);
    }
}
