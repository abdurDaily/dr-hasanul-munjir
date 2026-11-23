@extends('layouts.auth')
@section('title', 'Forgot password')
@section('content')
<h1 class="h4 mb-1">Reset your password</h1>
<p class="text-muted mb-4">Enter your staff email and we'll send you a reset link.</p>
<form method="post" action="{{ route('admin.password.email') }}" novalidate>
  @csrf
  <x-admin.input name="email" label="Email" type="email" required autofocus autocomplete="username" />
  <button class="btn btn-primary w-100" type="submit">Send reset link</button>
  <p class="text-center mt-3 mb-0"><a href="{{ route('admin.login') }}" class="small">Back to sign in</a></p>
</form>
@endsection
