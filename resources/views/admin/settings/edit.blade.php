@extends('layouts.admin')
@section('title', $config['label'])
@section('subtitle', 'Changes appear on the website immediately')

@section('content')
@if($tabs->count() > 1)
<nav class="a-tabs mb-3" aria-label="Settings sections">
  @foreach($tabs as $key => $label)
  <a href="{{ route('admin.settings.edit', $key) }}" class="{{ $key === $group ? 'active' : '' }}">{{ $label }}</a>
  @endforeach
</nav>
@endif

<form method="post" action="{{ route('admin.settings.update', $group) }}" enctype="multipart/form-data" class="a-card a-card__body" style="max-width: 980px">
  @csrf @method('PUT')
  @include('admin.settings._fields', ['fields' => $config['fields']])
  <div class="sticky-actions"><button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save {{ strtolower($config['label']) }}</button></div>
</form>
@endsection
