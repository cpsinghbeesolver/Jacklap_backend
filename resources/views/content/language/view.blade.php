@extends('layouts.contentNavbarLayout')

@section('title', 'View Language')

@section('content')
<div class="card">
    <div class="justify-content-between d-flex">
        <h5 class="card-header">Language Details</h5>
        <div class="m-4">
            <a href="{{ route('language')}}" class="btn btn-sm btn-primary">
            <i class="ri-arrow-left-line me-1"></i>
            Go Back
            </a>
        </div>
    </div>
    <div class="card-body">

        <p><b>Name:</b> {{ $language->name }}</p>


    </div>
</div>
@endsection