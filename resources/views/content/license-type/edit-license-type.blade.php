@extends('layouts.contentNavbarLayout')

@section('title', 'Update License Type')

@section('content')
<div class="row">
  <div class="col-12">

    <form method="POST" action="{{ route('update-license-type', $licenseType->id) }}">
      @csrf

      <div class="card mb-6">

        <!-- Header -->
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h5 class="mb-0">Update License Type</h5>

            <a href="{{ route('license-type')}}" class="btn btn-sm btn-primary">
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
                <input type="text" name="name" class="form-control"
                       value="{{ old('name') ?? $licenseType->name }}">
                <label>Name</label>
                @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-floating form-floating-outline">
                <textarea name="description" class="form-control">{{ old('description') ?? $licenseType->description }}</textarea>
                <label>Description</label>
                @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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