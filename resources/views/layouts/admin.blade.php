<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">
  <title>@yield('title', 'Dashboard') · {{ setting('clinic_name') }} Admin</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon.svg') }}">
  <link rel="stylesheet" href="{{ asset('admin-assets/vendor/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('admin-assets/vendor/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('admin-assets/vendor/sweetalert2.min.css') }}">
  @stack('styles')
  <link rel="stylesheet" href="{{ asset('admin-assets/admin.css') }}?v={{ filemtime(public_path('admin-assets/admin.css')) }}">
</head>
<body data-toast-success="{{ session('success') }}" data-toast-error="{{ session('error') ?? ($errors->any() ? 'Please check the highlighted fields.' : '') }}">

<aside class="a-sidebar" aria-label="Admin navigation">
  <a href="{{ route('admin.dashboard') }}" class="a-brand">
    <svg viewBox="0 0 40 40" aria-hidden="true"><path d="M20 7c-3-2.6-9.5-3.2-12 .8-2.6 4.2-.6 9.6.9 13.4 1.2 3.2 1.5 12.3 4.6 12.3 2.8 0 2.6-8.6 6.5-8.6s3.7 8.6 6.5 8.6c3.1 0 3.4-9.1 4.6-12.3 1.5-3.8 3.5-9.2.9-13.4C29.5 3.8 23 4.4 20 7Z"/></svg>
    <span><strong>{{ setting('clinic_name') }}</strong><small>Admin panel</small></span>
  </a>
  <nav class="a-nav">
    @foreach($adminMenu as $section)
      <div class="a-nav__label">{{ $section['label'] }}</div>
      @foreach($section['items'] as $item)
      <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'active' : '' }}" @if($item['active']) aria-current="page" @endif>
        <i class="bi {{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span>
        @if($item['badge'])<span class="badge rounded-pill">{{ $item['badge'] }}</span>@endif
      </a>
      @endforeach
    @endforeach
  </nav>
  <div class="a-user dropdown">
    <span class="a-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
    <div class="flex-grow-1 min-w-0">
      <strong class="text-truncate">{{ auth()->user()->name }}</strong>
      <small>{{ auth()->user()->roles->pluck('name')->join(', ') }}</small>
    </div>
    <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu"><i class="bi bi-three-dots-vertical"></i></button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="{{ route('admin.account.edit') }}"><i class="bi bi-person-gear me-2"></i>My account</a></li>
      <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-2"></i>View website</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><form method="post" action="{{ route('admin.logout') }}">@csrf<button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Log out</button></form></li>
    </ul>
  </div>
</aside>
<div class="a-backdrop"></div>

<div class="a-main">
  <header class="a-topbar">
    <button class="a-icon-btn a-menu-btn" type="button" data-sidebar-toggle aria-label="Open menu"><i class="bi bi-list"></i></button>
    <div class="min-w-0">
      <h1>@yield('title', 'Dashboard')</h1>
      <div class="a-topbar__sub">@yield('subtitle')</div>
    </div>
    @can('appointments.manage')
    <form class="a-search" action="{{ route('admin.appointments.index') }}" role="search">
      <i class="bi bi-search"></i>
      <input class="form-control" type="search" name="q" value="{{ request()->routeIs('admin.appointments.index') ? request('q') : '' }}" placeholder="Search patients, phone, reference…" aria-label="Search appointments">
    </form>
    <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}" class="a-icon-btn" aria-label="{{ $adminCounts['pending'] }} pending appointment requests" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Pending requests">
      <i class="bi bi-bell"></i>@if($adminCounts['pending'])<span class="badge rounded-pill">{{ $adminCounts['pending'] }}</span>@endif
    </a>
    <a href="{{ route('admin.appointments.create') }}" class="btn btn-accent d-none d-sm-inline-flex align-items-center gap-2"><i class="bi bi-plus-lg"></i> New Appointment</a>
    @endcan
    @yield('actions')
  </header>

  <main>
    @yield('content')
  </main>
</div>

<script src="{{ asset('admin-assets/vendor/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/sweetalert2.min.js') }}"></script>
@stack('scripts')
<script src="{{ asset('admin-assets/admin.js') }}?v={{ filemtime(public_path('admin-assets/admin.js')) }}"></script>
</body>
</html>
