@extends('layouts.admin')
@section('title', $case->exists ? 'Edit case' : 'Add smile case')

@section('content')
<form method="post" action="{{ $case->exists ? route('admin.smile-cases.update', $case) : route('admin.smile-cases.store') }}" enctype="multipart/form-data" novalidate>
  @csrf @if($case->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-xl-8">
      <div class="a-card a-card__body">
        <div class="row">
          <x-admin.input class="col-md-7" name="title" label="Title" :value="$case->title" required maxlength="120" placeholder="In-office whitening" />
          <x-admin.input class="col-md-5" name="duration" label="Treatment duration" :value="$case->duration" placeholder="1 visit" />
        </div>
        <x-admin.select name="treatment_id" label="Treatment (used for the gallery filter)" :options="$treatments" :value="$case->treatment_id" placeholder="— None —" />
        <x-admin.textarea name="description" label="Description" :value="$case->description" maxlength="1000" />
        <div class="row">
          <x-admin.image class="col-md-6" name="before_image" label="Before photo" :path="$case->before_image" preset="compare" :required="true" />
          <x-admin.image class="col-md-6" name="after_image" label="After photo" :path="$case->after_image" preset="compare" :required="true" />
        </div>
        <p class="form-text mt-0">Use photos taken from the same angle and lighting so the slider comparison is fair. Photos are re-encoded, which removes location and camera data.</p>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="a-card a-card__body">
        <h3 class="h6">Patient consent</h3>
        <x-admin.toggle name="consent_confirmed" label="Written consent obtained" :checked="$case->consent_confirmed" />
        <x-admin.input name="consent_date" label="Consent date" type="date" :value="$case->consent_date?->toDateString()" />
        <x-admin.textarea name="consent_note" label="Where the consent form is kept (internal)" :value="$case->consent_note" rows="2" maxlength="500" />
        <hr>
        <x-admin.toggle name="is_active" label="Publish on website" :checked="$case->is_active" help="Requires recorded consent." />
        <x-admin.toggle name="is_featured" label="Featured" :checked="$case->is_featured" />
        <x-admin.input name="sort_order" label="Display order" type="number" min="0" :value="$case->sort_order ?? 0" required />
      </div>
    </div>
  </div>
  <div class="sticky-actions">
    <a href="{{ route('admin.smile-cases.index') }}" class="btn btn-light">Back</a>
    <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save case</button>
  </div>
</form>
@endsection
