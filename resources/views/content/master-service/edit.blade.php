@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Master Service')

@section('content')
<div class="row">
<div class="col-12">

<form method="POST" action="{{ route('master-service.update',$service->id) }}">
@csrf

<div class="card">

<!-- HEADER -->
<div class="card-body d-flex justify-content-between">
    <h5>Edit Service</h5>

    <a href="{{ route('master-service') }}" class="btn btn-primary btn-sm">
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
            value="{{ old('name', $service->name) }}">

        <label>Name</label>

        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- CATEGORY -->
<div class="col-md-6">
    <div class="form-floating form-floating-outline">
        <select name="service_category_id"
            class="form-select @error('service_category_id') is-invalid @enderror">

            <option value="">Select Category</option>

            @foreach($categories as $cat)
                <option value="{{ $cat->id }}"
                    {{ old('service_category_id', $service->service_category_id) == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>

        <label>Category</label>

        @error('service_category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- TYPE -->
<div class="col-md-6">
    <div class="form-floating form-floating-outline">
        <select name="type"
            class="form-select @error('type') is-invalid @enderror">

            <option value="">Select Type</option>
            <option value="service" {{ old('type',$service->type)=='service' ? 'selected':'' }}>Service</option>
            <option value="subject" {{ old('type',$service->type)=='subject' ? 'selected':'' }}>Subject</option>
            <option value="skill" {{ old('type',$service->type)=='skill' ? 'selected':'' }}>Skill</option>
        </select>

        <label>Type</label>

        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- INPUT TYPE -->
<div class="col-md-6">
    <div class="form-floating form-floating-outline">
        <select name="input_type"
            class="form-select @error('input_type') is-invalid @enderror">

            <option value="">Select Type</option>
            <option value="radio" {{ old('input_type',$service->input_type)=='radio' ? 'selected':'' }}>Radio</option>
            <option value="checkbox" {{ old('input_type',$service->input_type)=='checkbox' ? 'selected':'' }}>Checkbox</option>
            <option value="input" {{ old('input_type',$service->input_type)=='' ? 'selected':'' }}>Input</option>
        </select>

        <label>Type</label>

        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- STATUS -->
<div class="col-md-6">
    <div class="form-floating form-floating-outline">
        <select name="status"
            class="form-select @error('status') is-invalid @enderror">

            <option value="1" {{ old('status',$service->status)==1 ? 'selected':'' }}>Active</option>
            <option value="0" {{ old('status',$service->status)==0 ? 'selected':'' }}>Inactive</option>
        </select>

        <label>Status</label>

        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="col-md-6">
    <div class="form-floating form-floating-outline">
      <input type="number" id="price_limit" name="price_limit" class="form-control  @error('price_limit') is-invalid @enderror" value="{{ old('price_limit',$service->price_limit) }}">
      <label>Price Limit</label>
    </div>
    @error('price_limit')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<!-- DESCRIPTION -->
<div class="col-md-12">
    <div class="form-floating form-floating-outline">
        <textarea name="description"
            class="form-control @error('description') is-invalid @enderror"
            style="height:100px">{{ old('description', $service->description) }}</textarea>

        <label>Description</label>

        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

</div>

<!-- BUTTON -->
<div class="mt-4">
    <button class="btn btn-primary">Update</button>
    <button type="reset" class="btn btn-outline-secondary" onclick="window.location.reload();">
        Reset
    </button>
</div>

</div>
</div>

</form>
</div>
</div>
@endsection