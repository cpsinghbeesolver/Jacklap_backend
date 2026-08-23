@extends('layouts.contentNavbarLayout')

@section('title', 'View Service')

@section('content')
<div class="card">

<div class="d-flex justify-content-between">
<h5 class="card-header">Service Details</h5>

<div class="m-3">
<a href="{{ route('master-service') }}" class="btn btn-primary btn-sm">
Back
</a>
</div>
</div>

<div class="card-body">

<p><b>Name:</b> {{ $service->name }}</p>
<p><b>Category:</b> {{ $service->category?->name }}</p>
<p><b>Type:</b> {{ ucfirst($service->type) }}</p>
<p><b>Status:</b> {{ $service->status ? 'Active':'Inactive' }}</p>
<p><b>Price Limit:</b> {{ $service->price_limit }}</p>
<p><b>Description:</b> {{ $service->description ?? 'NA' }}</p>

</div>
</div>
@endsection