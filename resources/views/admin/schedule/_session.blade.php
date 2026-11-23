<div class="row g-2 align-items-center js-session">
  <input type="hidden" data-name="weekday" value="{{ $weekday }}">
  <div class="col-sm-3"><input class="form-control form-control-sm" data-name="name" value="{{ $row['name'] }}" placeholder="Morning" required maxlength="40" aria-label="Session name"></div>
  <div class="col-5 col-sm-3"><input type="time" class="form-control form-control-sm" data-name="starts_at" value="{{ $row['starts_at'] }}" required aria-label="Starts at"></div>
  <div class="col-5 col-sm-3"><input type="time" class="form-control form-control-sm" data-name="ends_at" value="{{ $row['ends_at'] }}" required aria-label="Ends at"></div>
  <div class="col-2 col-sm-3 d-flex align-items-center gap-2">
    <div class="form-check form-switch m-0" data-bs-toggle="tooltip" title="Active"><input class="form-check-input" type="checkbox" data-name="is_active" value="1" @checked($row['is_active'] ?? true) aria-label="Active"></div>
    <button type="button" class="btn btn-sm btn-light text-danger js-remove-session" aria-label="Remove session"><i class="bi bi-x-lg"></i></button>
  </div>
</div>
