@extends('layouts.contentNavbarLayout')

@section('title', 'Add Service Use Case')

@section('content')
<div class="row">
  <div class="col-12">

    <form method="POST" action="{{ route('store-service-use-case') }}">
      @csrf

      <div class="card mb-6">

        <!-- Header -->
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h5 class="mb-0">Add Service Use Case</h5>

            <a href="{{ route('service-use-case') }}" class="btn btn-primary btn-sm">
              <i class="ri-arrow-left-line"></i> Go Back
            </a>
          </div>
        </div>

        <!-- Form -->
        <div class="card-body pt-0">
          <div class="row g-5">

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                <label>Title</label>
                @error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_full_day" id="is_full_day" value="1"
                       @checked(old('is_full_day'))>
                <label class="form-check-label" for="is_full_day">Full Day</label>
              </div>
              @error('is_full_day') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="mt-6">
              <button class="btn btn-primary">Save</button>
              <button type="reset" class="btn btn-outline-secondary">Reset</button>
            </div>

          </div>
        </div>

      </div>
    </form>

  </div>
</div>
@endsection