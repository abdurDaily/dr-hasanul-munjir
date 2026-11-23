@extends('layouts.admin')
@section('title', 'FAQs')
@section('subtitle', 'Questions shown in the FAQ section (also marked up for Google)')
@section('actions')
<a href="{{ route('admin.faqs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add</a>
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
  <form method="get"><select class="form-select" name="category" aria-label="Category" onchange="this.form.submit()"><option value="">All categories</option>@foreach($categories as $id => $name)<option value="{{ $id }}" @selected((int) request('category') === $id)>{{ $name }}</option>@endforeach</select></form>
  <a href="{{ route('admin.taxonomies.index', 'faq-categories') }}" class="btn btn-light"><i class="bi bi-tags me-1"></i>Categories</a>
</div>
<div class="a-card">
  @if($faqs->isEmpty())
    <div class="empty-state"><i class="bi bi-question-circle"></i>No FAQs yet. The section is hidden on the website until you add one.</div>
  @else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>#</th><th>Question</th><th>Category</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($faqs as $f)
        <tr>
          <td class="text-muted">{{ $f->sort_order }}</td>
          <td style="max-width:560px"><strong>{{ $f->question }}</strong><br><small class="text-muted d-block text-truncate">{{ $f->answer }}</small></td>
          <td>{{ $f->category?->name ?? '—' }}</td>
          <td><span class="badge {{ $f->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $f->is_active ? 'Active' : 'Hidden' }}</span></td>
          <td class="text-end text-nowrap"><a href="{{ route('admin.faqs.edit', $f) }}" class="btn btn-sm btn-soft">Edit</a> <x-admin.delete-button :action="route('admin.faqs.destroy', $f)" /></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $faqs->links() }}</div>
  @endif
</div>
@endsection
