@extends('layouts.contentNavbarLayout')

@section('title', 'Update Category')

@section('page-script')
  @vite(['resources/assets/js/pages-account-settings-account.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">

    <form method="POST" enctype="multipart/form-data" action="{{ route('update-category', $category->id) }}">
      @csrf

      <div class="card mb-6">

        <!-- Header -->
        <div class="card-body">
          <div class="d-flex justify-content-between">

            <!-- Image -->
            <div class="d-flex gap-4 align-items-center">
              <img src="{{ $category->image ? asset($category->image) : asset('assets/img/avatars/1.png') }}"
                   class="w-px-100 h-px-100 rounded" />

              <div>
                <label class="btn btn-primary btn-sm mb-2">
                  Upload Image
                  <input type="file" name="image" hidden />
                </label>

                <div class="text-muted">JPG/PNG, max 800KB</div>
              </div>
            </div>
            <div>
              <a href="{{ route('category-list')}}" class="btn btn-sm btn-primary">
                <i class="ri-arrow-left-line me-1"></i>
                Go Back
              </a>
            </div>
          </div>
        </div>

        <!-- Form -->
        <div class="card-body pt-0">
          <div class="row g-5">

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" name="name" class="form-control"
                       value="{{ old('name') ?? $category->name }}">
                <label>Name</label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="number" name="price" class="form-control"
                       value="{{ old('price') ?? $category->price }}">
                <label>Price</label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <select name="status" class="form-select">
                  <option value="1" {{ $category->status ? 'selected' : '' }}>Active</option>
                  <option value="0" {{ !$category->status ? 'selected' : '' }}>Inactive</option>
                </select>
                <label>Status</label>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-floating form-floating-outline">
                <textarea name="description" class="form-control">{{ old('description') ?? $category->description }}</textarea>
                <label>Description</label>
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