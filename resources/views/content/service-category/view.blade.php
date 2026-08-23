@extends('layouts.contentNavbarLayout')

@section('title', 'View Service Categories')


@section('content')
<div class="card">
    <div class="justify-content-between d-flex">
        <h5 class="card-header">Service Category Details</h5>
        <div class="m-4">
            <a href="{{ route('category-list')}}" class="btn btn-sm btn-primary">
            <i class="ri-arrow-left-line me-1"></i>
            Go Back
            </a>
        </div>
    </div>
    <div class="card-body">

        <p><b>Name:</b> {{ $category->name }}</p>

        <p><b>Slug:</b> {{ $category->slug }}</p>

        <p><b>Price:</b> {{ $category->price }}</p>

        <p><b>Status:</b> {{ $category->status ? 'Active' : 'Inactive' }}</p>

        <p><b>Description:</b> {{ $category->description ?? 'NA' }}</p>

        <img src="{{ asset($category->image)}}" width="120">

    </div>
</div>
@endsection