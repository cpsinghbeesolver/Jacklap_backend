@extends('layouts.contentNavbarLayout')

@section('title', 'View Document Type')

@section('content')
<div class="card">

    <div class="d-flex justify-content-between">
        <h5 class="card-header">Document Type Details</h5>
        <div class="m-3">
            <a href="{{ route('document-type') }}" class="btn btn-primary btn-sm">
                <i class="ri-arrow-left-line"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body">
        <p><b>Name:</b> {{ $type->name }}</p>
        <p><b>Total Documents:</b> {{ $type->total_documents }}</p>
        <p><b>Is Required:</b> {{ $type->is_required ? 'Yes' : 'No' }}</p>
    </div>

</div>
@endsection