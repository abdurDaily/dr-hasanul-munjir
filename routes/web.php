<?php

use App\Http\Controllers\Site;
use Illuminate\Support\Facades\Route;

Route::get('/', Site\HomeController::class)->name('home');

Route::get('/treatments', [Site\TreatmentController::class, 'index'])->name('treatments.index');
Route::get('/treatments/{slug}', [Site\TreatmentController::class, 'show'])->name('treatments.show');

Route::get('/blog', [Site\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [Site\BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{slug}', [Site\BlogController::class, 'tag'])->name('blog.tag');
Route::get('/blog/{slug}', [Site\BlogController::class, 'show'])->name('blog.show');

Route::get('/privacy-policy', [Site\PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms-of-use', [Site\PageController::class, 'terms'])->name('pages.terms');

// Booking: live availability (never cached) and the request itself (rate limited)
Route::get('/booking/availability', [Site\BookingController::class, 'availability'])
    ->middleware('throttle:60,1')->name('booking.availability');
Route::post('/appointments', [Site\BookingController::class, 'store'])
    ->middleware('throttle:booking')->name('appointments.store');

Route::post('/contact', [Site\ContactController::class, 'store'])->middleware('throttle:public-forms')->name('contact.store');
Route::post('/newsletter', [Site\NewsletterController::class, 'store'])->middleware('throttle:public-forms')->name('newsletter.store');

Route::get('/sitemap.xml', [Site\SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [Site\SitemapController::class, 'robots'])->name('robots');
