@extends('layouts.contentNavbarLayout')

@section('title', 'Add Category')

@section('page-script')
  @vite(['resources/assets/js/pages-account-settings-account.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">

    <form method="POST" enctype="multipart/form-data" action="{{ route('store-category') }}">
      @csrf

      <div class="card mb-6">

        <!-- Header -->
        <div class="card-body">
          <div class="d-flex justify-content-between">

            <!-- Image Upload -->
            <div class="d-flex gap-4 align-items-center">
              <img src="{{ asset('assets/img/avatars/1.png') }}"
                   class="w-px-100 h-px-100 rounded"
                   id="uploadedAvatar" />

              <div>
                <label class="btn btn-primary btn-sm mb-2">
                  Upload Image
                  <input type="file" name="image" hidden accept="image/png, image/jpeg" />
                </label>

                <div class="text-muted">JPG/PNG, max 800KB</div>

                @error('image')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Back -->
            <a href="{{ route('category-list') }}" class="btn btn-primary btn-sm">
              <i class="ri-arrow-left-line"></i> Go Back
            </a>

          </div>
        </div>

        <!-- Form -->
        <div class="card-body pt-0">
          <div class="row g-5">

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                <label>Name</label>
                @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="number" name="price" class="form-control" value="{{ old('price') }}">
                <label>Price</label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <select name="status" class="form-select">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
                <label>Status</label>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-floating form-floating-outline">
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                <label>Description</label>
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