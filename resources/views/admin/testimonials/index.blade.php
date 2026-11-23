@extends('layouts.admin')
@section('title', 'Testimonials')
@section('subtitle', 'Only genuine reviews, published with the patient\'s agreement')
@section('actions')
<a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add</a>
@endsection

@section('content')
<div class="a-card">
  @if($testimonials->isEmpty())
    <div class="empty-state"><i class="bi bi-chat-heart"></i>No testimonials yet. Add real reviews from Google or patients who agreed to be quoted.<br><small>Until then, the website shows a "Write a review" invitation instead.</small></div>
  @else
  <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Patient</th><th>Review</th><th>Rating</th><th>Source</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($testimonials as $t)
        <tr>
          <td><strong>{{ $t->display_name }}</strong><br><small class="text-muted">{{ $t->reviewed_on?->format('j M Y') }}</small></td>
          <td style="max-width:420px"><span class="d-block text-truncate">{{ $t->body }}</span>@if($t->treatment)<small class="text-muted">{{ $t->treatment->name }}</small>@endif</td>
          <td class="text-nowrap text-warning">{{ $t->rating }} <i class="bi bi-star-fill"></i></td>
          <td>{{ \App\Models\Testimonial::SOURCES[$t->source] ?? $t->source }}</td>
          <td>
            @if($t->is_active && $t->consent_confirmed)<span class="badge badge-soft-success">Published</span>@else<span class="badge badge-soft-secondary">Hidden</span>@endif
            @unless($t->consent_confirmed)<span class="badge badge-soft-warning">No consent</span>@endunless
          </td>
          <td class="text-end text-nowrap">
            <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-sm btn-soft">Edit</a>
            <x-admin.delete-button :action="route('admin.testimonials.destroy', $t)" confirm="Delete this testimonial?" text="The review and photo are permanently removed." />
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $testimonials->links() }}</div>
  @endif
</div>
@endsection
