<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', ['users' => User::query()->with('roles:id,name')->orderBy('name')->paginate(25)]);
    }

    public function create(): View
    {
        return $this->form(new User(['is_active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->data($request);
        $user = User::create($data);
        $user->syncRoles($data['roles']);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->guardSuperAdmin($request, $user);

        return $this->form($user);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->guardSuperAdmin($request, $user);
        $data = $this->data($request, $user);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        // Never let the last active Super Admin lock everyone out
        $losesSuper = $user->hasRole(User::SUPER_ADMIN) && (! in_array(User::SUPER_ADMIN, $data['roles'], true) || ! $data['is_active']);
        if ($losesSuper && $this->activeSuperAdmins() <= 1) {
            return back()->withInput()->with('error', 'There must always be at least one active Super Admin.');
        }

        $user->update($data);
        $user->syncRoles($data['roles']);

        return redirect()->route('admin.users.index')->with('success', 'User saved.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->guardSuperAdmin($request, $user);
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($user->hasRole(User::SUPER_ADMIN) && $this->activeSuperAdmins() <= 1) {
            return back()->with('error', 'There must always be at least one active Super Admin.');
        }
        $user->delete();

        return back()->with('success', 'User deleted.');
    }

    private function form(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'roles' => Role::query()->orderBy('name')->pluck('name', 'name')]);
    }

    private function data(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'is_active' => ['boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ], ['roles.required' => 'Choose at least one role.']);

        // Only a Super Admin can grant Super Admin
        if (in_array(User::SUPER_ADMIN, $data['roles'], true) && ! $request->user()->hasRole(User::SUPER_ADMIN)) {
            abort(403, 'Only a Super Admin can assign the Super Admin role.');
        }
        // You cannot deactivate yourself
        if ($user?->is($request->user())) {
            $data['is_active'] = true;
        }

        return $data;
    }

    /** Only a Super Admin may change or remove a Super Admin account (prevents account takeover). */
    private function guardSuperAdmin(Request $request, User $target): void
    {
        abort_if($target->hasRole(User::SUPER_ADMIN) && ! $request->user()->hasRole(User::SUPER_ADMIN), 403);
    }

    private function activeSuperAdmins(): int
    {
        return User::role(User::SUPER_ADMIN)->where('is_active', true)->count();
    }
}
