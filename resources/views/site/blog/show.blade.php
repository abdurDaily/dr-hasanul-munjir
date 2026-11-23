@extends('layouts.site')

@section('content')
<article class="article">
  @include('site.partials.page-hero', [
      'crumbs' => array_filter([['Blog', route('blog.index')], $post->category ? [$post->category->name, route('blog.category', $post->category->slug)] : null]),
      'title' => $post->title,
      'lead' => $post->excerpt,
  ])

  <div class="container section-pad pt-0">
    <div class="article__wrap">
      <div class="post__meta article__meta">
        @if($post->category)<a href="{{ route('blog.category', $post->category->slug) }}" class="post__cat">{{ $post->category->name }}</a>@endif
        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
        <span>· {{ $post->readingMinutes() }} min read</span>
        @if($post->author)<span>· By {{ $post->author->name }}</span>@endif
      </div>

      @if($post->image)
      <figure class="article__media">
        <x-picture :path="$post->image" preset="blog" :alt="$post->image_alt ?? $post->title" sizes="(min-width: 992px) 860px, 92vw" :eager="true" />
      </figure>
      @endif

      <div class="prose">{!! $post->body !!}</div>

      @if($post->tags->isNotEmpty())
      <ul class="article__tags list-unstyled" aria-label="Tags">
        @foreach($post->tags as $tag)<li><a href="{{ route('blog.tag', $tag->slug) }}">#{{ $tag->name }}</a></li>@endforeach
      </ul>
      @endif

      <aside class="article__cta">
        <div><strong>Have a question about your teeth?</strong><p class="mb-0">Book a check-up and get honest, personal advice.</p></div>
        <a href="{{ route('home') }}#appointment" class="btn-brand btn-brand--accent"><span>Book Appointment</span><i class="bi bi-arrow-up-right"></i></a>
      </aside>
    </div>
  </div>
</article>

@if($related->isNotEmpty())
<section class="blog section-pad">
  <div class="container">
    <div class="section-head d-lg-flex align-items-end justify-content-between">
      <div>
        <p class="eyebrow"><span class="eyebrow__line"></span>Keep Reading</p>
        <h2 class="section-title split-heading">Related <em>articles.</em></h2>
      </div>
      <a href="{{ route('blog.index') }}" class="link-arrow">View all articles <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      @foreach($related as $item)
        @include('site.partials.post-card', ['post' => $item])
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection
