@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Page')

@section('content')
<div class="row">
<div class="col-12">

<form method="POST" action="{{ route('page.update', $page->id) }}">
@csrf

<div class="card">

    <div class="card-body d-flex justify-content-between">
        <h5>Edit Page</h5>
        <a href="{{ route('page') }}" class="btn btn-primary btn-sm">
            <i class="ri-arrow-left-line"></i> Back
        </a>
    </div>

    <div class="card-body pt-0">
        <div class="row g-4">

            <!-- TITLE -->
            <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                    <input type="text" name="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $page->title) }}">
                    <label>Title</label>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- SLUG -->
            <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                    <input type="text" name="slug"
                        class="form-control @error('slug') is-invalid @enderror"
                        value="{{ old('slug', $page->slug) }}">
                    <label>Slug</label>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- META TITLE -->
            <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                    <input type="text" name="meta_title"
                        class="form-control @error('meta_title') is-invalid @enderror"
                        value="{{ old('meta_title', $page->meta_title) }}">
                    <label>Meta Title</label>
                    @error('meta_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- IS ACTIVE -->
            <div class="col-md-6">
                @php
                    $currentStatus = old('is_active', $page->getAttributes()['is_active']);
                @endphp
                <div class="form-floating form-floating-outline">
                    <select name="is_active"
                        class="form-select @error('is_active') is-invalid @enderror">
                        <option value="">Select</option>
                        <option value="1" {{ (string) $currentStatus === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ (string) $currentStatus === '0' ? 'selected' : '' }}>In-Active</option>
                    </select>
                    <label>Status</label>
                    @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- META DESCRIPTION -->
            <div class="col-12">
                <div class="form-floating form-floating-outline">
                    <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror"
                        style="height:100px;">{{ old('meta_description', $page->meta_description) }}</textarea>
                    <label>Meta Description</label>
                    @error('meta_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- CONTENT (Rich Text Editor) -->
            <div class="col-12">
                <label class="form-label">Content</label>
                <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror">{{ old('content', $page->content) }}</textarea>
                @error('content')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <div class="mt-4">
            <button class="btn btn-primary">Update</button>
            <button type="reset" class="btn btn-outline-secondary" onclick="window.location.reload();">Reset</button>
        </div>
    </div>

</div>
</form>
</div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#content'))
        .catch(error => console.error(error));
</script>
@endsection