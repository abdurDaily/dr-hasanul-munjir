<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>@yield('title') · {{ setting('clinic_name') }} Admin</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon.svg') }}">
  <link rel="stylesheet" href="{{ asset('admin-assets/vendor/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('admin-assets/vendor/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('admin-assets/admin.css') }}?v={{ filemtime(public_path('admin-assets/admin.css')) }}">
</head>
<body>
<main class="auth">
  <div class="a-card auth__card">
    <a href="{{ route('home') }}" class="auth__brand">
      <svg viewBox="0 0 40 40" aria-hidden="true"><path d="M20 7c-3-2.6-9.5-3.2-12 .8-2.6 4.2-.6 9.6.9 13.4 1.2 3.2 1.5 12.3 4.6 12.3 2.8 0 2.6-8.6 6.5-8.6s3.7 8.6 6.5 8.6c3.1 0 3.4-9.1 4.6-12.3 1.5-3.8 3.5-9.2.9-13.4C29.5 3.8 23 4.4 20 7Z"/></svg>
      <span><strong class="d-block h5 mb-0">{{ setting('clinic_name') }}</strong><small class="text-muted">Staff area</small></span>
    </a>
    @if(session('status'))<div class="alert alert-success small">{{ session('status') }}</div>@endif
    @yield('content')
  </div>
</main>
</body>
</html>
