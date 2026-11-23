@extends('layouts.admin')
@section('title', 'Users')
@section('subtitle', 'Staff who can sign in to the admin')
@section('actions')
<a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add user</a>
@endsection

@section('content')
<div class="a-card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th>Last sign-in</th><th></th></tr></thead>
      <tbody>
        @foreach($users as $u)
        <tr>
          <td><div class="d-flex align-items-center gap-2"><span class="a-avatar">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span><strong>{{ $u->name }}</strong>@if($u->is(auth()->user()))<span class="badge badge-soft-info">You</span>@endif</div></td>
          <td>{{ $u->email }}</td>
          <td>@foreach($u->roles as $r)<span class="badge badge-soft-primary me-1">{{ $r->name }}</span>@endforeach</td>
          <td><span class="badge {{ $u->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $u->is_active ? 'Active' : 'Disabled' }}</span></td>
          <td class="text-nowrap">{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
          <td class="text-end text-nowrap">
            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-soft">Edit</a>
            @unless($u->is(auth()->user()))<x-admin.delete-button :action="route('admin.users.destroy', $u)" confirm="Delete {{ $u->name }}?" text="They will no longer be able to sign in." />@endunless
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $users->links() }}</div>
</div>
@endsection
