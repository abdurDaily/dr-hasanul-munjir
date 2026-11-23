<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

/*
| Admin routes — prefix "/admin", names "admin.*" (registered in bootstrap/app.php).
| Every group is guarded server-side by a permission; hiding menu items is only cosmetic.
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [Admin\Auth\LoginController::class, 'create'])->name('login');
    Route::post('login', [Admin\Auth\LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('forgot-password', [Admin\Auth\PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [Admin\Auth\PasswordResetController::class, 'email'])->middleware('throttle:public-forms')->name('password.email');
    Route::get('reset-password/{token}', [Admin\Auth\PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('reset-password', [Admin\Auth\PasswordResetController::class, 'update'])->middleware('throttle:public-forms')->name('password.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('logout', [Admin\Auth\LoginController::class, 'destroy'])->name('logout');

    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('account', [Admin\AccountController::class, 'edit'])->name('account.edit');
    Route::put('account', [Admin\AccountController::class, 'update'])->name('account.update');

    // ---- Appointments ----
    Route::middleware('can:appointments.manage')->group(function () {
        Route::get('appointments/slots', [Admin\AppointmentController::class, 'slots'])->name('appointments.slots');
        Route::resource('appointments', Admin\AppointmentController::class)->only(['index', 'create', 'store', 'show']);
        Route::patch('appointments/{appointment}/status', [Admin\AppointmentController::class, 'status'])->name('appointments.status');
        Route::patch('appointments/{appointment}/reschedule', [Admin\AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::post('appointments/{appointment}/notes', [Admin\AppointmentController::class, 'note'])->name('appointments.note');
    });

    // ---- Availability ----
    Route::middleware('can:schedule.manage')->prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', [Admin\ScheduleController::class, 'index'])->name('index');
        Route::put('sessions', [Admin\ScheduleController::class, 'sessions'])->name('sessions');
        Route::post('{kind}', [Admin\ScheduleController::class, 'store'])->whereIn('kind', ['breaks', 'closures', 'blocks'])->name('store');
        Route::delete('{kind}/{id}', [Admin\ScheduleController::class, 'destroy'])->whereIn('kind', ['breaks', 'closures', 'blocks'])->name('destroy');
        Route::resource('appointment-types', Admin\AppointmentTypeController::class)->only(['index', 'edit', 'update']);
    });

    // ---- Messages & subscribers ----
    Route::middleware('can:messages.manage')->group(function () {
        Route::resource('messages', Admin\ContactMessageController::class)->only(['index', 'show', 'destroy']);
    });
    Route::middleware('can:subscribers.manage')->group(function () {
        Route::get('subscribers/export', [Admin\SubscriberController::class, 'export'])->name('subscribers.export');
        Route::resource('subscribers', Admin\SubscriberController::class)->only(['index', 'destroy']);
    });

    // ---- Content ----
    Route::middleware('can:treatments.manage')->group(function () {
        Route::resource('treatments', Admin\TreatmentController::class)->except('show');
    });
    Route::middleware('can:blog.manage')->group(function () {
        Route::resource('posts', Admin\BlogPostController::class)->except('show');
    });
    Route::middleware('can:testimonials.manage')->group(function () {
        Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
    });
    Route::middleware('can:gallery.manage')->group(function () {
        Route::resource('smile-cases', Admin\SmileCaseController::class)->except('show');
        Route::resource('clinic-photos', Admin\ClinicPhotoController::class)->except('show');
    });
    Route::middleware('can:content.manage')->group(function () {
        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::resource('credentials', Admin\CredentialController::class)->except('show');
        Route::resource('highlights', Admin\HighlightController::class)->except('show');
        Route::resource('faqs', Admin\FaqController::class)->except('show');
    });

    // Categories & tags share one controller; the {taxonomy} decides model and permission.
    Route::get('taxonomies/{taxonomy}', [Admin\TaxonomyController::class, 'index'])->name('taxonomies.index');
    Route::post('taxonomies/{taxonomy}', [Admin\TaxonomyController::class, 'store'])->name('taxonomies.store');
    Route::put('taxonomies/{taxonomy}/{id}', [Admin\TaxonomyController::class, 'update'])->name('taxonomies.update');
    Route::delete('taxonomies/{taxonomy}/{id}', [Admin\TaxonomyController::class, 'destroy'])->name('taxonomies.destroy');

    // Rich-text editor image uploads (blog posts, treatments)
    Route::post('editor-images', Admin\EditorImageController::class)->name('editor-images.store');

    // ---- Settings (each group checks its own permission) ----
    Route::get('settings/{group}', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings/{group}', [Admin\SettingsController::class, 'update'])->name('settings.update');

    // ---- Users & roles ----
    Route::middleware('can:users.manage')->group(function () {
        Route::resource('users', Admin\UserController::class)->except('show');
    });
    Route::middleware('can:roles.manage')->group(function () {
        Route::resource('roles', Admin\RoleController::class)->except('show');
    });
});
