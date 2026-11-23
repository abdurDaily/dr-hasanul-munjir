<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $email = env('ADMIN_EMAIL', 'admin@munjirdental.test');
        if (! User::query()->where('email', $email)->exists()) {
            $password = env('ADMIN_PASSWORD') ?: Str::password(16);
            $admin = User::create(['name' => 'Super Admin', 'email' => $email, 'password' => $password]);
            $admin->assignRole(User::SUPER_ADMIN);
            $this->command?->warn("Super Admin created: {$email} / {$password}  — change this password after logging in.");
        }

        $this->call([ScheduleSeeder::class, ContentSeeder::class]);
    }
}
