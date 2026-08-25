@extends('layouts.contentNavbarLayout')

@section('title', 'Add Settings')

@section('content')
<style>
  .settings-intro {
    background: linear-gradient(135deg, #f1f5ff 0%, #f8f9fc 100%);
    border: 1px solid #e4e9f2;
  }

  .settings-panel {
    height: 100%;
    padding: 1.25rem;
    border: 1px solid #e6e8ee;
    border-radius: .5rem;
    background: #fff;
  }

  .settings-panel-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: .5rem;
    background: #eef2ff;
    color: #696cff;
    font-size: 1.25rem;
  }

  .settings-panel .form-control,
  .settings-panel .form-select {
    background-color: #fbfbfc;
  }

  .settings-panel .form-control:focus,
  .settings-panel .form-select:focus {
    background-color: #fff;
  }
</style>
<div class="row">
  <div class="col-12">

    <form method="POST" action="{{ route('save-settings') }}">
      @csrf

      <div class="card mb-6">
        <div class="card-body border-bottom">
          <div class="settings-intro rounded p-4">
            <div class="d-flex align-items-start gap-3">
              <span class="settings-panel-icon flex-shrink-0">
                <i class="ri-settings-4-line"></i>
              </span>
              <div>
                <h4 class="mb-1">Platform settings</h4>
                <p class="mb-0 text-muted">Manage the fees applied to bookings and cancellations.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6">
              <div class="settings-panel">
                <div class="d-flex align-items-center gap-3 mb-4">
                  <span class="settings-panel-icon"><i class="ri-percent-line"></i></span>
                  <div>
                    <h5 class="mb-1">Platform fee</h5>
                    <p class="mb-0 text-muted small">The fee charged on each completed booking.</p>
                  </div>
                </div>
              <div class="form-floating form-floating-outline">
                <input type="number" name="platform_fee" class="form-control @error('platform_fee') is-invalid @enderror"
                       min="0" step="0.01" placeholder="0"
                       value="{{ old('platform_fee',$settings->platform_fee ?? '') }}">
                <label for="platform_fee">Fee amount</label>
                @error('platform_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="form-floating form-floating-outline mt-4">
                <select name="platform_fee_type" id="platform_fee_type" class="form-select">
                  <option value="perc" {{ old('platform_fee_type', $settings->platform_fee_type ?? 'perc') === 'perc' ? 'selected' : '' }}>Percentage</option>
                  <option value="num" {{ old('platform_fee_type', $settings->platform_fee_type ?? '') === 'num' ? 'selected' : '' }}>Fixed amount</option>
                </select>
                <label for="platform_fee_type">Fee type</label>
              </div>
            </div>
            </div>

            <div class="col-md-6">
              <div class="settings-panel">
                <div class="d-flex align-items-center gap-3 mb-4">
                  <span class="settings-panel-icon"><i class="ri-calendar-close-line"></i></span>
                  <div>
                    <h5 class="mb-1">Cancellation charge</h5>
                    <p class="mb-0 text-muted small">The charge applied when a booking is cancelled.</p>
                  </div>
                </div>
              <div class="form-floating form-floating-outline">
                <input type="number" name="cancellation_charges" class="form-control @error('cancellation_charges') is-invalid @enderror"
                       min="0" step="0.01" placeholder="0"
                       value="{{ old('cancellation_charges',$settings->cancellation_charges ?? '') }}">
                <label for="cancellation_charges">Charge amount</label>
                @error('cancellation_charges') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="form-floating form-floating-outline mt-4">
                <select name="cancellation_charges_type" id="cancellation_charges_type" class="form-select">
                  <option value="perc" {{ old('cancellation_charges_type', $settings->cancellation_charges_type ?? 'perc') === 'perc' ? 'selected' : '' }}>Percentage</option>
                  <option value="num" {{ old('cancellation_charges_type', $settings->cancellation_charges_type ?? '') === 'num' ? 'selected' : '' }}>Fixed amount</option>
                </select>
                <label for="cancellation_charges_type">Charge type</label>
              </div>
            </div>
            </div>

            <div class="mt-6">
              <button class="btn btn-primary">Save changes</button>
              <button type="reset" class="btn btn-outline-secondary" onclick="window.location.reload();">
                Reset
              </button>
            </div>

          </div>
        </div>

      </div>
    </form>

  </div>
</div>
@endsection