<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', ['roles' => Role::query()->withCount('users')->with('permissions:id,name')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return $this->form(new Role);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->data($request);
        Role::create(['name' => $data['name'], 'guard_name' => 'web'])->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        abort_if($role->name === User::SUPER_ADMIN, 403, 'The Super Admin role always has every permission.');

        return $this->form($role);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->name === User::SUPER_ADMIN, 403);
        $data = $this->data($request, $role);
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role saved.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === User::SUPER_ADMIN || $role->users()->exists()) {
            return back()->with('error', 'Roles that are assigned to users (and the Super Admin role) cannot be deleted.');
        }
        $role->delete();

        return back()->with('success', 'Role deleted.');
    }

    private function form(Role $role): View
    {
        return view('admin.roles.form', ['role' => $role, 'permissions' => RolesAndPermissionsSeeder::PERMISSIONS]);
    }

    private function data(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('roles')->ignore($role), Rule::notIn([User::SUPER_ADMIN])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(array_keys(RolesAndPermissionsSeeder::PERMISSIONS))],
        ]);
    }
}
