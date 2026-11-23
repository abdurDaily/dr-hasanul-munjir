@extends('layouts.admin')
@section('title', 'Blog Posts')
@section('subtitle', 'Articles for the Dental Health Blog')
@section('actions')
<a href="{{ route('admin.posts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New post</a>
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
  <form class="d-flex flex-wrap gap-2" method="get">
    <input class="form-control" style="max-width:240px" name="q" value="{{ request('q') }}" placeholder="Search titles" aria-label="Search">
    <select class="form-select" style="max-width:180px" name="status" aria-label="Status" onchange="this.form.submit()"><option value="">All statuses</option>@foreach(\App\Models\BlogPost::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select>
    <select class="form-select" style="max-width:200px" name="category" aria-label="Category" onchange="this.form.submit()"><option value="">All categories</option>@foreach($categories as $id => $name)<option value="{{ $id }}" @selected((int) request('category') === $id)>{{ $name }}</option>@endforeach</select>
  </form>
  <div class="d-flex gap-2">
    @if(request()->boolean('trashed'))
      <a href="{{ route('admin.posts.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>All posts</a>
    @elseif($trashedCount)
      <a href="{{ route('admin.posts.index', ['trashed' => 1]) }}" class="btn btn-light"><i class="bi bi-trash me-1"></i>Trash ({{ $trashedCount }})</a>
    @endif
    <a href="{{ route('admin.taxonomies.index', 'blog-categories') }}" class="btn btn-light"><i class="bi bi-folder me-1"></i>Categories</a>
    <a href="{{ route('admin.taxonomies.index', 'blog-tags') }}" class="btn btn-light"><i class="bi bi-tags me-1"></i>Tags</a>
  </div>
</div>

<div class="a-card">
  @if($posts->isEmpty())
    <div class="empty-state"><i class="bi bi-journal-text"></i>No posts found.</div>
  @else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Post</th><th>Category</th><th>Status</th><th>Published</th><th>Author</th><th></th></tr></thead>
      <tbody>
        @foreach($posts as $p)
        <tr>
          <td><div class="d-flex align-items-center gap-3">@if($p->image)<img class="thumb" src="{{ image_url($p->image, 'blog', 420) }}" alt="" loading="lazy">@else<span class="thumb d-grid place-items-center text-muted"><i class="bi bi-image m-auto"></i></span>@endif<div><strong>{{ $p->title }}</strong>@if($p->is_featured) <i class="bi bi-star-fill text-warning" title="Featured"></i>@endif<br><small class="text-muted">/blog/{{ $p->slug }}</small></div></div></td>
          <td>{{ $p->category?->name ?? '—' }}</td>
          <td>
            @if($p->isPublished())<span class="badge badge-soft-success">Published</span>
            @elseif($p->status === 'published')<span class="badge badge-soft-info">Scheduled</span>
            @else<span class="badge badge-soft-secondary">Draft</span>@endif
          </td>
          <td class="text-nowrap">{{ $p->published_at?->format('j M Y') ?? '—' }}</td>
          <td>{{ $p->author?->name ?? '—' }}</td>
          <td class="text-end text-nowrap">
            @if($p->trashed())
            <form method="post" action="{{ route('admin.posts.restore', $p->id) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-soft"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button></form>
            <x-admin.delete-button :action="route('admin.posts.force-destroy', $p->id)" label="Delete permanently" confirm="Permanently delete this post?" text="The post and its images are removed for good. This cannot be undone." />
            @else
            @if($p->isPublished())<a href="{{ route('blog.show', $p->slug) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light" aria-label="View" data-bs-toggle="tooltip" title="View"><i class="bi bi-box-arrow-up-right"></i></a>@endif
            <a href="{{ route('admin.posts.edit', $p) }}" class="btn btn-sm btn-soft">Edit</a>
            <x-admin.delete-button :action="route('admin.posts.destroy', $p)" confirm="Move this post to Trash?" text="It disappears from the website. You can restore it from Trash." />
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $posts->links() }}</div>
  @endif
</div>
@endsection
