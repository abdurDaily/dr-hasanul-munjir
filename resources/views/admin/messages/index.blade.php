@extends('layouts.admin')
@section('title', 'Contact Messages')
@section('subtitle', 'Sent from the website contact form')

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
  <form class="d-flex gap-2" method="get">
    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search messages" aria-label="Search">
    <input type="hidden" name="filter" value="{{ request('filter') }}">
  </form>
  <nav class="a-tabs">
    <a href="{{ route('admin.messages.index', request()->except('filter', 'page')) }}" class="{{ request('filter') ? '' : 'active' }}">All</a>
    <a href="{{ route('admin.messages.index', [...request()->except('page'), 'filter' => 'unread']) }}" class="{{ request('filter') === 'unread' ? 'active' : '' }}">Unread</a>
  </nav>
</div>
<div class="a-card">
  @if($messages->isEmpty())
    <div class="empty-state"><i class="bi bi-inbox"></i>No messages.</div>
  @else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>From</th><th>Message</th><th>Received</th><th></th></tr></thead>
      <tbody>
        @foreach($messages as $m)
        <tr class="{{ $m->read_at ? '' : 'fw-semibold' }}">
          <td>{{ $m->name }} @unless($m->read_at)<span class="badge badge-soft-warning">New</span>@endunless<br><small class="text-muted fw-normal">{{ $m->phone ?? $m->email }}</small></td>
          <td style="max-width:480px"><span class="d-block text-truncate">{{ $m->message }}</span></td>
          <td class="text-nowrap fw-normal">{{ $m->created_at->format('j M Y, g:i A') }}</td>
          <td class="text-end text-nowrap"><a href="{{ route('admin.messages.show', $m) }}" class="btn btn-sm btn-soft">Open</a> <x-admin.delete-button :action="route('admin.messages.destroy', $m)" confirm="Delete this message?" /></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $messages->links() }}</div>
  @endif
</div>
@endsection
