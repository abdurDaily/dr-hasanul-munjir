<?php

namespace App\View\Composers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/** Sidebar menu (filtered by permission) and the pending / unread counters. */
class AdminComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $counts = [
            'pending' => $user->can('appointments.manage') ? Appointment::query()->where('status', AppointmentStatus::Pending)->count() : 0,
            'unread' => $user->can('messages.manage') ? ContactMessage::query()->whereNull('read_at')->count() : 0,
        ];

        $menu = [];
        foreach (config('admin-menu') as $section) {
            $items = [];
            foreach ($section['items'] as $item) {
                [$label, $icon, $route, $active, $permission] = $item;
                if ($permission && ! $user->can($permission)) {
                    continue;
                }
                $items[] = [
                    'label' => $label,
                    'icon' => $icon,
                    'url' => is_array($route) ? route($route[0], $route[1]) : route($route),
                    'active' => $this->isActive($active),
                    'badge' => isset($item[5]) ? $counts[$item[5]] : 0,
                ];
            }
            if ($items) {
                $menu[] = ['label' => $section['label'], 'items' => $items];
            }
        }

        $view->with(['adminMenu' => $menu, 'adminCounts' => $counts]);
    }

    /** "admin.posts.*" or "admin.settings.edit:home,seo" (route name + allowed {group} values). */
    private function isActive(string $pattern): bool
    {
        [$name, $groups] = array_pad(explode(':', $pattern, 2), 2, null);
        if (! request()->routeIs($name)) {
            return false;
        }

        return $groups === null || in_array(Route::current()?->parameter('group'), explode(',', $groups), true);
    }
}
