@extends('layouts.admin')
@section('title', 'Newsletter Subscribers')
@section('subtitle')
{{ number_format($total) }} active subscribers
@endsection
@section('actions')
<a href="{{ route('admin.subscribers.export') }}" class="btn btn-soft"><i class="bi bi-download me-1"></i>Export CSV</a>
@endsection

@section('content')
<form class="mb-3" method="get" style="max-width: 320px"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search email" aria-label="Search"></form>
<div class="a-card">
  @if($subscribers->isEmpty())
    <div class="empty-state"><i class="bi bi-envelope-paper"></i>No subscribers yet.</div>
  @else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Email</th><th>Status</th><th>Subscribed</th><th></th></tr></thead>
      <tbody>
        @foreach($subscribers as $s)
        <tr>
          <td>{{ $s->email }}</td>
          <td><span class="badge {{ $s->status === 'subscribed' ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ ucfirst($s->status) }}</span></td>
          <td>{{ $s->created_at->format('j M Y') }}</td>
          <td class="text-end"><x-admin.delete-button :action="route('admin.subscribers.destroy', $s)" confirm="Remove this subscriber?" /></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $subscribers->links() }}</div>
  @endif
</div>
@endsection
