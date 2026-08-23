@extends('layouts.contentNavbarLayout')

@section('title', 'View License Type')

@section('content')
<div class="card">
    <div class="justify-content-between d-flex">
        <h5 class="card-header">License Type Details</h5>
        <div class="m-4">
            <a href="{{ route('license-type')}}" class="btn btn-sm btn-primary">
            <i class="ri-arrow-left-line me-1"></i>
            Go Back
            </a>
        </div>
    </div>
    <div class="card-body">

        <p><b>Name:</b> {{ $licenseType->name }}</p>

        <p><b>Description:</b> {{ $licenseType->description ?? 'NA' }}</p>

    </div>
</div>
@endsection