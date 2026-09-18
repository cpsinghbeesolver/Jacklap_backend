@extends('layouts.contentNavbarLayout')

@section('title', 'View Service Use Case')

@section('content')
<div class="card">
    <div class="justify-content-between d-flex">
        <h5 class="card-header">Service Use Case Details</h5>
        <div class="m-4">
            <a href="{{ route('service-use-case')}}" class="btn btn-sm btn-primary">
            <i class="ri-arrow-left-line me-1"></i>
            Go Back
            </a>
        </div>
    </div>
    <div class="card-body">

        <p><b>Title:</b> {{ $useCase->title }}</p>

        <p><b>Full Day:</b> {{ $useCase->is_full_day ? 'Yes' : 'No' }}</p>

    </div>
</div>
@endsection