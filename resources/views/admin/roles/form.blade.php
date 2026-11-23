@extends('layouts.admin')
@section('title', $role->exists ? 'Edit role' : 'New role')

@section('content')
<form method="post" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="a-card a-card__body" style="max-width: 760px" novalidate>
  @csrf @if($role->exists) @method('PUT') @endif
  <x-admin.input name="name" label="Role name" :value="$role->name" required maxlength="60" />
  @php($current = old('permissions', $role->exists ? $role->permissions->pluck('name')->all() : []))
  <fieldset class="mb-3">
    <legend class="form-label">Permissions</legend>
    <div class="row">
      @foreach($permissions as $key => $label)
      <div class="col-md-6">
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}" id="perm-{{ $loop->index }}" @checked(in_array($key, $current, true))>
          <label class="form-check-label" for="perm-{{ $loop->index }}">{{ $label }}</label>
        </div>
      </div>
      @endforeach
    </div>
  </fieldset>
  <div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('admin.roles.index') }}" class="btn btn-light">Cancel</a>
    <button class="btn btn-primary">Save role</button>
  </div>
</form>
@endsection
