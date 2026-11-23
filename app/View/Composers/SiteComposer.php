<?php

namespace App\View\Composers;

use App\Models\AppointmentType;
use App\Models\DentistProfile;
use App\Models\ScheduleSession;
use App\Models\Treatment;
use App\Services\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Data every public page needs (header, footer, contact details). Cached; the admin clears
 * the "site-layout" key whenever the underlying content changes.
 */
class SiteComposer
{
    public const CACHE_KEY = 'site-layout';

    private static ?array $data = null;

    public function compose(View $view): void
    {
        $view->with(self::$data ??= Cache::rememberForever(self::CACHE_KEY, fn () => [
            'profile' => DentistProfile::current(),
            'footerTreatments' => Treatment::query()->active()->ordered()->limit(6)->get(['id', 'name', 'slug']),
            'chamberHours' => self::chamberHours(),
            // Booking form options (the booking partial appears on the homepage and treatment pages)
            'appointmentTypes' => AppointmentType::query()->active()->ordered()->get(),
            'bookingTreatments' => Treatment::query()->active()->ordered()->get(['id', 'name']),
        ]) + ['site' => app(Settings::class)]);
    }

    /**
     * Distinct opening sessions, e.g. [["Morning", "10:30 AM – 2:00 PM"], ["Evening", "5:30 PM – 10:00 PM"]].
     */
    public static function chamberHours(): array
    {
        return ScheduleSession::query()->where('is_active', true)->orderBy('starts_at')->get()
            ->unique(fn ($s) => $s->name.$s->starts_at.$s->ends_at)
            ->map(fn ($s) => [$s->name, date('g:i A', strtotime($s->starts_at)).' – '.date('g:i A', strtotime($s->ends_at))])
            ->values()->all();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('home-page');
        self::$data = null;
    }
}
