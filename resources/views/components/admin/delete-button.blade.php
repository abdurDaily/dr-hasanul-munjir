@props(['action', 'label' => 'Delete', 'confirm' => 'Delete this item?', 'text' => 'This cannot be undone from the admin.'])
<form method="post" action="{{ $action }}" class="d-inline" data-confirm="{{ $confirm }}" data-confirm-text="{{ $text }}" data-confirm-button="Delete" data-confirm-danger>
  @csrf @method('DELETE')
  <button type="submit" {{ $attributes->merge(['class' => 'btn btn-sm btn-light text-danger']) }} aria-label="{{ $label }}" data-bs-toggle="tooltip" title="{{ $label }}"><i class="bi bi-trash"></i></button>
</form>
