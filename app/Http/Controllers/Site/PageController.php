<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    public function privacy(): View
    {
        return $this->legal('Privacy Policy', 'privacy_policy');
    }

    public function terms(): View
    {
        return $this->legal('Terms of Use', 'terms_of_use');
    }

    private function legal(string $title, string $key): View
    {
        return view('site.page', [
            'title' => $title,
            'body' => setting($key),
            'seo' => ['title' => "{$title} | ".setting('clinic_name'), 'description' => "{$title} for ".setting('clinic_name').'.'],
        ]);
    }
}
