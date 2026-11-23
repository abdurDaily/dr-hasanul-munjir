@extends('layouts.admin')
@section('title', $faq->exists ? 'Edit FAQ' : 'Add FAQ')

@section('content')
<form method="post" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="a-card a-card__body" style="max-width: 860px" novalidate>
  @csrf @if($faq->exists) @method('PUT') @endif
  <x-admin.input name="question" label="Question" :value="$faq->question" required maxlength="255" />
  <x-admin.textarea name="answer" label="Answer" :value="$faq->answer" required rows="5" maxlength="3000" />
  <div class="row">
    <x-admin.select class="col-md-5" name="faq_category_id" label="Category" :options="$categories" :value="$faq->faq_category_id" placeholder="— None —" />
    <x-admin.input class="col-md-3" name="sort_order" label="Display order" type="number" min="0" :value="$faq->sort_order" required />
    <div class="col-md-4 pt-md-4"><x-admin.toggle name="is_active" label="Show on website" :checked="$faq->is_active" /></div>
  </div>
  <div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('admin.faqs.index') }}" class="btn btn-light">Cancel</a>
    <button class="btn btn-primary">Save</button>
  </div>
</form>
@endsection
