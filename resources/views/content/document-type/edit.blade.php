@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Document Type')

@section('content')
<div class="row">
<div class="col-12">

<form method="POST" action="{{ route('document-type.update', $type->id) }}">
@csrf

<div class="card">

    <div class="card-body d-flex justify-content-between">
        <h5>Edit Document Type</h5>
        <a href="{{ route('document-type') }}" class="btn btn-primary btn-sm">
            <i class="ri-arrow-left-line"></i> Back
        </a>
    </div>

    <div class="card-body pt-0">
        <div class="row g-4">

            <!-- NAME -->
            <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $type->name) }}">
                    <label>Name</label>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- TOTAL DOCUMENTS -->
            <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                    <input type="number" name="total_documents"
                        class="form-control @error('total_documents') is-invalid @enderror"
                        value="{{ old('total_documents', $type->total_documents) }}" min="1">
                    <label>Total Documents</label>
                    @error('total_documents')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- IS REQUIRED -->
            <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                    <select name="is_required"
                        class="form-select @error('is_required') is-invalid @enderror">
                        <option value="">Select</option>
                        <option value="1" {{ old('is_required', $type->is_required) == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('is_required', $type->is_required) == '0' ? 'selected' : '' }}>No</option>
                    </select>
                    <label>Is Required</label>
                    @error('is_required')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
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