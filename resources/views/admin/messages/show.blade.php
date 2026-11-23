@extends('layouts.admin')
@section('title', 'Message from '.$message->name)
@section('actions')
<a href="{{ route('admin.messages.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back</a>
@endsection

@section('content')
<div class="a-card a-card__body" style="max-width: 860px">
  <dl class="detail-list mb-4">
    <dt>Name</dt><dd>{{ $message->name }}</dd>
    <dt>Phone</dt><dd>@if($message->phone)<a href="{{ tel_href($message->phone) }}">{{ $message->phone }}</a>@else — @endif</dd>
    <dt>Email</dt><dd>@if($message->email)<a href="mailto:{{ $message->email }}">{{ $message->email }}</a>@else — @endif</dd>
    <dt>Received</dt><dd>{{ $message->created_at->format('l, j F Y \a\t g:i A') }}</dd>
  </dl>
  <div class="p-3 rounded-3" style="background: var(--a-bg); white-space: pre-line">{{ $message->message }}</div>
  <div class="d-flex flex-wrap gap-2 mt-4">
    @if($message->phone)<a href="{{ tel_href($message->phone) }}" class="btn btn-primary"><i class="bi bi-telephone me-1"></i>Call</a>@endif
    @if($message->email)<a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: your message to '.setting('clinic_name')) }}" class="btn btn-soft"><i class="bi bi-reply me-1"></i>Reply by email</a>@endif
    <x-admin.delete-button :action="route('admin.messages.destroy', $message)" confirm="Delete this message?" class="btn btn-light text-danger" />
  </div>
</div>
@endsection
