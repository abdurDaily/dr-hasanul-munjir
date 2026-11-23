@extends('layouts.admin')
@section('title', 'My Account')

@section('content')
<form method="post" action="{{ route('admin.account.update') }}" class="a-card a-card__body" style="max-width: 640px" novalidate>
  @csrf @method('PUT')
  <x-admin.input name="name" label="Name" :value="$user->name" required />
  <x-admin.input name="email" label="Email" type="email" :value="$user->email" required autocomplete="username" />
  <x-admin.input name="phone" label="Phone" :value="$user->phone" />
  <hr>
  <x-admin.input name="password" label="New password" type="password" autocomplete="new-password" help="Leave empty to keep your current password. Changing it signs you out on other devices." />
  <x-admin.input name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" />
  <x-admin.input name="current_password" label="Current password (required to save)" type="password" required autocomplete="current-password" />
  <div class="d-flex justify-content-end"><button class="btn btn-primary">Save</button></div>
</form>
@endsection
