@extends('layouts.admin')
@section('title', 'Dentist Profile')
@section('subtitle', 'Shown in the hero, About section, booking summary and structured data')

@section('content')
<form method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" novalidate>
  @csrf @method('PUT')
  <div class="row g-3">
    <div class="col-xl-7">
      <div class="a-card a-card__body">
        <div class="form-section">
          <h3>Identity</h3>
          <div class="row">
            <x-admin.input class="col-md-6" name="full_name" label="Full name" :value="$profile->full_name" required />
            <x-admin.input class="col-md-6" name="credentials_line" label="Degrees" :value="$profile->credentials_line" placeholder="BDS (CU) · PGT (OMS)" />
          </div>
          <x-admin.input name="role_line" label="Professional title line" :value="$profile->role_line" />
          <div class="row">
            <x-admin.input class="col-md-6" name="specialty_line" label="Speciality line" :value="$profile->specialty_line" />
            <x-admin.input class="col-md-3" name="bmdc_reg_no" label="BMDC reg. no." :value="$profile->bmdc_reg_no" />
            <x-admin.input class="col-md-3" name="years_experience" label="Years of experience" type="number" min="0" :value="$profile->years_experience" />
          </div>
        </div>
        <div class="form-section">
          <h3>About</h3>
          <x-admin.textarea name="bio" label="Biography" :value="$profile->bio" rows="7" help="Separate paragraphs with a blank line. Wrap words in *asterisks* for italics." />
          <div class="row">
            <x-admin.textarea class="col-md-6" name="qualifications_summary" label="Qualifications (card)" :value="$profile->qualifications_summary" />
            <x-admin.textarea class="col-md-6" name="expertise_summary" label="Expertise (card)" :value="$profile->expertise_summary" />
          </div>
          <x-admin.textarea name="philosophy_quote" label="Philosophy quote" :value="$profile->philosophy_quote" rows="2" />
          <x-admin.textarea name="specializations" label="Specializations / areas of expertise" :value="implode(PHP_EOL, $profile->specializations ?? [])" rows="4" help="One per line." />
        </div>
      </div>
    </div>
    <div class="col-xl-5">
      <div class="a-card a-card__body">
        <x-admin.image name="hero_image" label="Hero photo (transparent PNG works best)" :path="$profile->hero_image" preset="hero" />
        <x-admin.image name="portrait_image" label="Small round portrait" :path="$profile->portrait_image" preset="portrait" />
        <x-admin.image name="about_image" label="About — main photo" :path="$profile->about_image" preset="photo" />
        <x-admin.input name="about_image_alt" label="Main photo description" :value="$profile->about_image_alt" />
        <x-admin.image name="about_image_secondary" label="About — small photo" :path="$profile->about_image_secondary" preset="photo" />
        <x-admin.input name="about_image_secondary_alt" label="Small photo description" :value="$profile->about_image_secondary_alt" />
      </div>
    </div>
  </div>
  <div class="sticky-actions"><button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save profile</button></div>
</form>
@endsection
