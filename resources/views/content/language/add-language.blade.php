@extends('layouts.contentNavbarLayout')

@section('title', 'Add language')

@section('content')
<div class="row">
  <div class="col-12">

    <form method="POST" action="{{ route('store-language') }}">
      @csrf

      <div class="card mb-6">

        <!-- Header -->
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h5 class="mb-0">Add language</h5>

            <a href="{{ route('language') }}" class="btn btn-primary btn-sm">
              <i class="ri-arrow-left-line"></i> Go Back
            </a>
          </div>
        </div>

        <!-- Form -->
        <div class="card-body pt-0">
          <div class="row g-5">

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" name="name" class="form-control" value="{{ old('language') }}">
                <label>Language</label>
                @error('language') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
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