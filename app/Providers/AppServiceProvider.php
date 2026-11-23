<?php

namespace App\Providers;

use App\Models\AppointmentType;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\ClinicPhoto;
use App\Models\Credential;
use App\Models\DentistProfile;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryCategory;
use App\Models\Highlight;
use App\Models\ScheduleSession;
use App\Models\SmileCase;
use App\Models\Testimonial;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Models\User;
use App\Services\Settings;
use App\View\Composers\AdminComposer;
use App\View\Composers\SiteComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
    }

    public function boot(): void
    {
        // Super Admin passes every permission check; everyone else needs an explicit permission.
        Gate::before(fn (User $user) => $user->hasRole(User::SUPER_ADMIN) ? true : null);

        // Surface N+1 queries during development and tests
        Model::preventLazyLoading(! $this->app->isProduction());

        Paginator::useBootstrapFive();

        ResetPassword::createUrlUsing(fn (User $user, string $token) => route('admin.password.reset', ['token' => $token, 'email' => $user->email]));

        Password::defaults(fn () => Password::min(10)->letters()->numbers());

        RateLimiter::for('booking', fn (Request $r) => [
            Limit::perMinute(5)->by($r->ip()),
            Limit::perDay(20)->by($r->ip()),
        ]);
        RateLimiter::for('public-forms', fn (Request $r) => [
            Limit::perMinute(5)->by($r->ip()),
            Limit::perDay(30)->by($r->ip()),
        ]);
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(5)->by(strtolower((string) $r->input('email')).'|'.$r->ip()));

        View::composer(['layouts.site', 'site.*'], SiteComposer::class);
        View::composer('layouts.admin', AdminComposer::class);

        // Public pages cache their content; any content change clears those caches.
        foreach ([
            DentistProfile::class, Treatment::class, TreatmentCategory::class,
            Credential::class, Highlight::class, ClinicPhoto::class,
            GalleryCategory::class, SmileCase::class, Testimonial::class,
            Faq::class, FaqCategory::class, BlogPost::class,
            BlogCategory::class, ScheduleSession::class, AppointmentType::class,
        ] as $model) {
            $model::saved(fn () => SiteComposer::flush());
            $model::deleted(fn () => SiteComposer::flush());
        }
    }
}
