@extends('layouts.site')

@section('content')
@include('site.partials.page-hero', ['crumbs' => [[$title, null]], 'title' => $title])

<section class="section-pad pt-0">
  <div class="container">
    <div class="article__wrap prose">
      @if($body)
        {!! $body !!}
      @else
        <p>This page is being prepared. For any questions, please call {{ $site->get('phone_primary') }}@if($site->get('email')) or email <a href="mailto:{{ $site->get('email') }}">{{ $site->get('email') }}</a>@endif.</p>
      @endif
    </div>
  </div>
</section>
@endsection
