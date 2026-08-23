@extends('frontend.layouts.login-layout')

@section('content')
  <div class="col-lg-7 col-md-12">
    <div class="login-form-">
      <div class="logo mb-3">
        <img src="{{ asset('frontend/images/logo.png') }}" class="logo" />
      </div>

      <div class="login-form">
        <h2 class="mb-2">Register your account</h2>
        <p class="text-muted mb-4">
          Create your account to get started quickly and access all
          features.
        </p>

        <h5 class="mb-3">Basic Details</h5>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" />
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Gender</label>
            <select class="form-control">
              <option>Select Gender</option>
              <option>Male</option>
              <option>Female</option>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">DOB</label>
            <input type="date" class="form-control" />
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" />
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" />
          </div>

          <div class="col-md-6 mb-4">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" />
          </div>
        </div>

        <div
          class="d-flex justify-content-between align-items-center mt-4"
        >
          <a href="{{route('register')}}" class="text-success text-decoration-none">
            ← Back
          </a>

          <a href="{{ route('verification')}}" class="btn btn-login px-5">Continue</a> 
        </div>
      </div>
    </div>
  </div>
@endsection