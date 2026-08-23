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
            <div class="step"></div>
            <div class="step"></div>
            <div class="step active"></div>
          </div>

          <p class="step-text">Step 3/3</p>
        </div>
        <div class="bank-details">
          <h3 class="service-title">Bank Details</h3>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Account Holder Name</label>
              <input type="text" class="form-control" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Bank Name</label>
              <input type="text" class="form-control" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Account Number</label>
              <input type="text" class="form-control" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">IFSC</label>
              <input type="text" class="form-control" />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Account Type</label>
              <select class="form-control">
                <option>Select Account Type</option>
                <option>Savings</option>
                <option>Current</option>
              </select>
            </div>
          </div>
        </div>
        <div
          class="d-flex justify-content-between align-items-center mt-4"
        >
          <a href="{{ route('register.documents')}}" class="text-success text-decoration-none">
            ← Back
          </a>

          <a href="{{ route('provider.dashboard')}}" class="btn btn-login px-5">Continue</a> 
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
