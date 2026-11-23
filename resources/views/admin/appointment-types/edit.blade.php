@extends('layouts.admin')
@section('title', 'Edit '.$type->name)

@section('content')
<form method="post" action="{{ route('admin.schedule.appointment-types.update', $type) }}" class="a-card a-card__body" style="max-width: 760px">
  @csrf @method('PUT')
  <div class="row">
    <x-admin.input class="col-md-6" name="name" label="Name" :value="$type->name" required />
    <x-admin.input class="col-md-6" name="short_label" label="Button label" :value="$type->short_label" required />
  </div>
  <x-admin.input name="note" label="Note in the booking summary" :value="$type->note" />
  <x-admin.icon-picker :value="$type->icon" />
  <div class="row">
    <x-admin.input class="col-md-4" name="sort_order" label="Display order" type="number" min="0" :value="$type->sort_order" required />
    <div class="col-md-8 pt-md-4"><x-admin.toggle name="is_active" label="Offered on the website" :checked="$type->is_active" /></div>
  </div>
  <div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('admin.schedule.appointment-types.index') }}" class="btn btn-light">Cancel</a>
    <button class="btn btn-primary">Save</button>
  </div>
</form>
@endsection
