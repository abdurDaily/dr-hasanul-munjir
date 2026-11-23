@extends('layouts.admin')
@section('title', $title)
@section('actions')
<a href="{{ $back }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back</a>
@endsection

@section('content')
<div class="row g-3">
  <div class="col-lg-4">
    <form method="post" action="{{ route('admin.taxonomies.store', $taxonomy) }}" class="a-card a-card__body" novalidate>
      @csrf
      <h2 class="h6 mb-3">Add new</h2>
      <x-admin.input name="name" label="Name" required maxlength="80" />
      <x-admin.input name="slug" label="Slug" help="Optional — generated from the name." />
      @if($hasDescription)<x-admin.textarea name="description" label="Description" rows="2" maxlength="300" />@endif
      @if($hasOrder)<x-admin.input name="sort_order" label="Display order" type="number" min="0" value="0" />@endif
      <button class="btn btn-primary w-100">Add</button>
    </form>
  </div>
  <div class="col-lg-8">
    <div class="a-card">
      @if($items->isEmpty())
        <div class="empty-state"><i class="bi bi-tags"></i>None yet.</div>
      @else
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Name</th><th>Slug</th>@if($hasOrder)<th>Order</th>@endif<th>Used by</th><th></th></tr></thead>
          <tbody>
            @foreach($items as $item)
            @php($count = collect($item->getAttributes())->first(fn ($v, $k) => str_ends_with($k, '_count')))
            <tr>
              <td colspan="{{ $hasOrder ? 3 : 2 }}">
                <form method="post" action="{{ route('admin.taxonomies.update', [$taxonomy, $item->id]) }}" class="row g-2 align-items-center" id="tx-{{ $item->id }}">
                  @csrf @method('PUT')
                  <div class="col"><input name="name" class="form-control form-control-sm" value="{{ $item->name }}" required maxlength="80" aria-label="Name"></div>
                  <div class="col"><input name="slug" class="form-control form-control-sm" value="{{ $item->slug }}" maxlength="100" aria-label="Slug"></div>
                  @if($hasOrder)<div class="col-3"><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ $item->sort_order }}" aria-label="Order"></div>@endif
                  @if($hasDescription)<input type="hidden" name="description" value="{{ $item->description }}">@endif
                </form>
              </td>
              <td>{{ $count }}</td>
              <td class="text-end text-nowrap">
                <button form="tx-{{ $item->id }}" class="btn btn-sm btn-soft">Save</button>
                <x-admin.delete-button :action="route('admin.taxonomies.destroy', [$taxonomy, $item->id])" confirm="Delete “{{ $item->name }}”?" :text="$count ? 'Items using it will be left without one.' : 'This cannot be undone.'" />
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
