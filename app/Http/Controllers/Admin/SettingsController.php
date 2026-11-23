<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HtmlCleaner;
use App\Services\ImageService;
use App\Services\Settings;
use App\View\Composers\SiteComposer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/** Edits a group of website settings defined in config/settings.php. */
class SettingsController extends Controller
{
    public function edit(Request $request, string $group): View
    {
        $config = $this->group($request, $group);

        // Tabs: the other groups this user may edit (booking rules live on the Availability page)
        $tabs = collect(config('settings'))
            ->except('booking')
            ->filter(fn ($g) => $request->user()->can($g['permission']))
            ->map(fn ($g) => $g['label']);

        return view('admin.settings.edit', ['group' => $group, 'config' => $config, 'tabs' => $tabs]);
    }

    public function update(Request $request, string $group, Settings $settings, ImageService $images, HtmlCleaner $cleaner): RedirectResponse
    {
        $fields = $this->group($request, $group)['fields'];
        $data = $request->validate(collect($fields)->map(fn ($f) => $f['rules'])->all());

        $values = [];
        foreach ($fields as $key => $field) {
            $value = $data[$key] ?? null;
            $values[$key] = match ($field['type']) {
                'image' => $request->hasFile($key)
                    ? $images->replace($settings->get($key), $request->file($key), 'settings', 'og')
                    : ($request->boolean("remove_{$key}") ? tap(null, fn () => $images->delete($settings->get($key), 'og')) : $settings->get($key)),
                'html' => $cleaner->clean($value),
                'lines' => implode("\n", array_filter(array_map('trim', preg_split('/\R/', (string) $value)))),
                default => is_string($value) ? trim($value) : $value,
            };
        }

        $settings->set($values);
        SiteComposer::flush();
        Cache::forget('sitemap.xml');

        return back()->with('success', "{$this->group($request, $group)['label']} saved.");
    }

    private function group(Request $request, string $group): array
    {
        $config = config("settings.{$group}");
        abort_unless($config, 404);
        abort_unless($request->user()->can($config['permission']), 403);

        return $config;
    }
}
