@extends('layouts.contentNavbarLayout')

@section('title', 'Update Service Use Case')

@section('content')
<div class="row">
  <div class="col-12">

    <form method="POST" action="{{ route('update-service-use-case', $useCase->id) }}">
      @csrf

      <div class="card mb-6">

        <!-- Header -->
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h5 class="mb-0">Update Service Use Case</h5>

            <a href="{{ route('service-use-case') }}" class="btn btn-sm btn-primary">
              <i class="ri-arrow-left-line me-1"></i>
              Go Back
            </a>
          </div>
        </div>

        <!-- Form -->
        <div class="card-body pt-0">
          <div class="row g-5">

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" name="title" class="form-control"
                       value="{{ old('title') ?? $useCase->title }}">
                <label>Title</label>
                @error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_full_day" id="is_full_day" value="1"
                       @checked(old('is_full_day', $useCase->is_full_day))>
                <label class="form-check-label" for="is_full_day">Full Day</label>
              </div>
              @error('is_full_day') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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