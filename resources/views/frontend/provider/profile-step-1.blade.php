@extends('frontend.layouts.login-layout')

@section('content')
<!-- Login Form -->
<div class="col-lg-7 col-md-12">
  <div class="login-form-">
    <div class="logo mb-3">
      <img src="{{ asset('frontend/images/logo.png')}}" class="logo" />
    </div>

    <div class="service-setup">
      <div class="row">
        <div class="profile-header">
          <h2 class="profile-title">Set up your profile</h2>

          <p class="profile-desc">
            Set up your profile by adding the required details to get
            started.
          </p>

          <div class="steps-bar">
            <div class="step active"></div>
            <div class="step"></div>
            <div class="step"></div>
          </div>

          <p class="step-text">Step 1/3</p>

          <h3 class="service-title">Service Setup</h3>
        </div>

        <div class="setup-profile-scroll">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Service Category</label>
              <input type="text" class="form-control" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Experience</label>
              <input type="text" class="form-control" />
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label"
              >Describe what this service includes</label
            >
            <textarea class="form-control" rows="3"></textarea>
          </div>

          <div class="mb-2">
            <label class="form-label">Set your own price</label>
            <div class="price-slider">
              <input type="range" min="0" max="999" value="600" />
              <span class="max-price">₹999</span>
            </div>
          </div>

          <div class="final-price mb-4">
            <label>Final Price</label>
            <div class="price">$600</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Languages Known</label>
            <input type="text" class="form-control" />
          </div>

          <div class="add-service mb-4">
            <span>Add a Service</span>
            <span class="plus">+</span>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Service Name</label>
              <input type="text" class="form-control" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Service Price</label>
              <input type="text" class="form-control" />
            </div>
          </div>
        </div>

        <div 
          class="d-flex justify-content-between align-items-center mt-4"
        >
          <a href="{{route('verification')}}" class="text-success text-decoration-none">
            ← Back
          </a>

          <a href="{{ route('register.documents')}}" class="btn btn-login px-5">Continue</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
