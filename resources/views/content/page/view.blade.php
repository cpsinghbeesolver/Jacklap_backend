@extends('layouts.contentNavbarLayout')

@section('title', 'View Page')

@section('content')
<div class="card">

    <div class="d-flex justify-content-between">
        <h5 class="card-header">Page Details</h5>
        <div class="m-3">
            <a href="{{ route('page') }}" class="btn btn-primary btn-sm">
                <i class="ri-arrow-left-line"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body">
        <p><b>Title:</b> {{ $page->title }}</p>
        <p><b>Slug:</b> {{ $page->slug }}</p>
        <p><b>Meta Title:</b> {{ $page->meta_title ?? '—' }}</p>
        <p><b>Meta Description:</b> {{ $page->meta_description ?? '—' }}</p>
        <p><b>Status:</b> {{ $page->is_active }}</p>
        <hr>
        <p><b>Content:</b></p>
        <div class="border rounded p-3">
            {!! $page->content !!}
        </div>
    </div>

</div>
@endsection