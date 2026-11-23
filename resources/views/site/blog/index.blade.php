@extends('layouts.site')

@section('content')
@include('site.partials.page-hero', [
    'crumbs' => $heading ? [['Blog', route('blog.index')], [$heading, null]] : [['Blog', null]],
    'eyebrow' => 'Dental Health Blog',
    'title' => $heading ?? emphasis($site->get('blog_title')),
    'lead' => $activeCategory?->description,
])

<section class="blog section-pad pt-0">
  <div class="container">
    @if($categories->count() > 1)
    <nav class="filters mb-4" aria-label="Blog categories">
      <a href="{{ route('blog.index') }}" class="filter-btn{{ ! $heading ? ' is-active' : '' }}"@if(! $heading) aria-current="page"@endif>All</a>
      @foreach($categories as $cat)
      <a href="{{ route('blog.category', $cat->slug) }}" class="filter-btn{{ $activeCategory?->id === $cat->id ? ' is-active' : '' }}"@if($activeCategory?->id === $cat->id) aria-current="page"@endif>{{ $cat->name }}</a>
      @endforeach
    </nav>
    @endif

    @if($posts->isEmpty())
      <p class="demo-note"><i class="bi bi-journal-text"></i> New articles are on the way — check back soon.</p>
    @else
      <div class="row g-4">
        @foreach($posts as $post)
          @include('site.partials.post-card', ['post' => $post])
        @endforeach
      </div>
      <div class="site-pagination mt-5">{{ $posts->onEachSide(1)->links() }}</div>
    @endif
  </div>
</section>
@endsection
