@extends('layouts.admin')
@section('title', $post->exists ? 'Edit post' : 'New post')

@section('content')
<form method="post" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" enctype="multipart/form-data" novalidate>
  @csrf @if($post->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-xl-8">
      <div class="a-card a-card__body">
        <x-admin.input name="title" id="p-title" label="Title" :value="$post->title" required maxlength="200" />
        <x-admin.input name="slug" label="URL slug" :value="$post->slug" data-slug-from="#p-title" help="Leave empty to generate from the title." />
        <x-admin.textarea name="excerpt" label="Short description" :value="$post->excerpt" maxlength="300" rows="2" help="Shown on blog cards and used as the meta description by default." />
        <x-admin.textarea name="body" label="Content" :value="$post->body" :editor="true" :upload-url="route('admin.editor-images.store')" />
      </div>
      <div class="a-card a-card__body mt-3">
        <div class="form-section">
          <h3>SEO &amp; social sharing</h3>
          <x-admin.input name="meta_title" label="Meta title" :value="$post->meta_title" maxlength="70" help="Defaults to the post title." />
          <x-admin.textarea name="meta_description" label="Meta description" :value="$post->meta_description" maxlength="320" rows="2" />
          <x-admin.image name="og_image" label="Open Graph image (1200×630)" :path="$post->og_image" preset="og" help="Optional — the featured image is used if empty." />
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="a-card a-card__body">
        <x-admin.select name="status" label="Status" :options="\App\Models\BlogPost::STATUSES" :value="$post->status" required />
        <x-admin.input name="published_at" label="Publish date" type="datetime-local" :value="$post->published_at?->format('Y-m-d\TH:i')" help="Leave empty to publish now. A future date schedules the post." />
        <x-admin.toggle name="is_featured" label="Featured post" :checked="$post->is_featured" />
        <x-admin.select name="blog_category_id" label="Category" :options="$categories" :value="$post->blog_category_id" placeholder="— None —" />
        <x-admin.input name="tags" label="Tags" :value="$post->tags?->pluck('name')->join(', ')" list="tag-list" help="Comma separated, e.g. braces, children" />
        <datalist id="tag-list">@foreach($allTags as $tag)<option value="{{ $tag }}">@endforeach</datalist>
        <x-admin.image name="image" label="Featured image" :path="$post->image" preset="blog" />
        <x-admin.input name="image_alt" label="Image alt text" :value="$post->image_alt" />
      </div>
    </div>
  </div>
  <div class="sticky-actions">
    <a href="{{ route('admin.posts.index') }}" class="btn btn-light">Back</a>
    @if($post->exists && $post->isPublished())<a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="btn btn-light"><i class="bi bi-box-arrow-up-right me-1"></i>View</a>@endif
    <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save post</button>
  </div>
</form>
@endsection
