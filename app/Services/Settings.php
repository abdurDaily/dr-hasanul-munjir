<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value website settings, cached as one array. Defaults come from config/settings.php.
 */
class Settings
{
    private const CACHE_KEY = 'site-settings';

    private ?array $values = null;

    public function all(): array
    {
        return $this->values ??= Cache::rememberForever(self::CACHE_KEY, fn () => array_merge(
            self::defaults(),
            Setting::query()->pluck('value', 'key')->map(fn ($v) => (string) $v)->all(),
        ));
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->all()[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    /** Non-empty lines of a multi-line setting. */
    public function lines(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->get($key)))));
    }

    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->values = null;
    }

    public static function defaults(): array
    {
        $defaults = [];
        foreach (config('settings') as $group) {
            foreach ($group['fields'] as $key => $field) {
                $defaults[$key] = $field['default'] ?? '';
            }
        }

        return $defaults;
    }
}
