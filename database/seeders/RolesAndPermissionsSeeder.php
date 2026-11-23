<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /** Every permission the admin checks. Super Admin bypasses all checks (see AppServiceProvider). */
    public const PERMISSIONS = [
        'dashboard.view' => 'View dashboard',
        'appointments.manage' => 'Manage appointments & booking requests',
        'schedule.manage' => 'Manage availability, holidays & booking rules',
        'messages.manage' => 'Manage contact messages',
        'subscribers.manage' => 'Manage newsletter subscribers',
        'treatments.manage' => 'Manage treatments',
        'blog.manage' => 'Manage blog',
        'testimonials.manage' => 'Manage testimonials',
        'gallery.manage' => 'Manage smile & clinic galleries',
        'content.manage' => 'Manage profile, credentials, FAQs & homepage text',
        'seo.manage' => 'Manage SEO settings',
        'settings.manage' => 'Manage website settings',
        'users.manage' => 'Manage users',
        'roles.manage' => 'Manage roles & permissions',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(self::PERMISSIONS) as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate(User::SUPER_ADMIN, 'web');

        Role::findOrCreate('Receptionist', 'web')->syncPermissions([
            'dashboard.view', 'appointments.manage', 'schedule.manage', 'messages.manage', 'subscribers.manage',
        ]);

        Role::findOrCreate('Content Manager', 'web')->syncPermissions([
            'dashboard.view', 'treatments.manage', 'blog.manage', 'testimonials.manage', 'gallery.manage',
            'content.manage', 'seo.manage',
        ]);
    }
}
