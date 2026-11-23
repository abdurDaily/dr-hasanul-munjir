@extends('layouts.auth')
@section('title', 'Sign in')
@section('content')
<h1 class="h4 mb-1">Welcome back</h1>
<p class="text-muted mb-4">Sign in to manage appointments and website content.</p>
<form method="post" action="{{ route('admin.login.store') }}" novalidate>
  @csrf
  <x-admin.input name="email" label="Email" type="email" required autofocus autocomplete="username" />
  <div class="mb-3">
    <div class="d-flex justify-content-between"><label for="password" class="form-label">Password <span class="text-danger">*</span></label><a href="{{ route('admin.password.request') }}" class="small">Forgot password?</a></div>
    <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
  </div>
  <div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
    <label class="form-check-label" for="remember">Keep me signed in on this device</label>
  </div>
  <button class="btn btn-primary w-100" type="submit">Sign in</button>
</form>
@endsection
