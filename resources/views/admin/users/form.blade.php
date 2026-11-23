@extends('layouts.admin')
@section('title', $user->exists ? 'Edit '.$user->name : 'Add user')

@section('content')
<form method="post" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="a-card a-card__body" style="max-width: 760px" novalidate autocomplete="off">
  @csrf @if($user->exists) @method('PUT') @endif
  <div class="row">
    <x-admin.input class="col-md-6" name="name" label="Name" :value="$user->name" required />
    <x-admin.input class="col-md-6" name="phone" label="Phone" :value="$user->phone" />
  </div>
  <x-admin.input name="email" label="Email (used to sign in)" type="email" :value="$user->email" required />
  <div class="row">
    <x-admin.input class="col-md-6" name="password" label="{{ $user->exists ? 'New password' : 'Password' }}" type="password" :required="! $user->exists" autocomplete="new-password" :help="$user->exists ? 'Leave empty to keep the current password.' : 'At least 10 characters, with letters and numbers.'" />
    <x-admin.input class="col-md-6" name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" />
  </div>
  <fieldset class="mb-3">
    <legend class="form-label">Roles <span class="text-danger">*</span></legend>
    @php($current = old('roles', $user->exists ? $user->roles->pluck('name')->all() : []))
    @foreach($roles as $role)
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role }}" id="role-{{ $loop->index }}" @checked(in_array($role, $current, true)) @disabled($role === \App\Models\User::SUPER_ADMIN && ! auth()->user()->hasRole(\App\Models\User::SUPER_ADMIN))>
      <label class="form-check-label" for="role-{{ $loop->index }}">{{ $role }}</label>
    </div>
    @endforeach
    @error('roles')<div class="text-danger small">{{ $message }}</div>@enderror
  </fieldset>
  @unless($user->is(auth()->user()))<x-admin.toggle name="is_active" label="Account active" :checked="$user->is_active" />@else<input type="hidden" name="is_active" value="1">@endunless
  <div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
    <button class="btn btn-primary">Save</button>
  </div>
</form>
@endsection
