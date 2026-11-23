@extends('layouts.auth')
@section('title', 'Choose a new password')
@section('content')
<h1 class="h4 mb-4">Choose a new password</h1>
<form method="post" action="{{ route('admin.password.update') }}" novalidate>
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <x-admin.input name="email" label="Email" type="email" :value="$email" required autocomplete="username" />
  <x-admin.input name="password" label="New password" type="password" required autocomplete="new-password" help="At least 10 characters, with letters and numbers." />
  <x-admin.input name="password_confirmation" label="Confirm new password" type="password" required autocomplete="new-password" />
  <button class="btn btn-primary w-100" type="submit">Reset password</button>
</form>
@endsection
