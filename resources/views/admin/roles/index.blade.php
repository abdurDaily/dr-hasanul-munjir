@extends('layouts.admin')
@section('title', 'Roles & Permissions')
@section('subtitle', 'What each group of staff is allowed to do — enforced on every request')
@section('actions')
<a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New role</a>
@endsection

@section('content')
<div class="row g-3">
  @foreach($roles as $role)
  <div class="col-md-6 col-xl-4">
    <div class="a-card a-card__body h-100 d-flex flex-column">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <h2 class="h5 mb-0">{{ $role->name }}</h2>
        <span class="badge badge-soft-primary">{{ $role->users_count }} {{ \Illuminate\Support\Str::plural('user', $role->users_count) }}</span>
      </div>
      <div class="flex-grow-1 mb-3">
        @if($role->name === \App\Models\User::SUPER_ADMIN)
          <p class="text-muted small mb-0">Full access to everything, including users and roles.</p>
        @else
          @forelse($role->permissions as $p)<span class="badge badge-soft-secondary me-1 mb-1">{{ \Database\Seeders\RolesAndPermissionsSeeder::PERMISSIONS[$p->name] ?? $p->name }}</span>@empty<span class="text-muted small">No permissions</span>@endforelse
        @endif
      </div>
      @unless($role->name === \App\Models\User::SUPER_ADMIN)
      <div class="d-flex gap-2">
        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-soft flex-fill">Edit</a>
        @if($role->users_count === 0)<x-admin.delete-button :action="route('admin.roles.destroy', $role)" confirm="Delete the {{ $role->name }} role?" />@endif
      </div>
      @endunless
    </div>
  </div>
  @endforeach
</div>
@endsection
