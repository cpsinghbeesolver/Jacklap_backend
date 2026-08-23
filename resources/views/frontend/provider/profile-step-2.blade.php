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
            <div class="step active"></div>
            <div class="step"></div>
          </div>

          <p class="step-text">Step 2/3</p>

          <h3 class="service-title">Verification</h3>
        </div>
        <div class="setup-profile-scroll">
          <div class="mb-3">
            <label class="form-label">ID Proof</label>
            <select class="form-control">
              <option>Select ID Proof</option>
              <option>Aadhar Card</option>
              <option>PAN Card</option>
              <option>Driving License</option>
              <option>Passport</option>
            </select>
          </div>

          <div class="row upload-row">
            <div class="col-md-6 mb-4">
              <div class="upload-box">
                <i class="bi bi-upload"></i>
                <p>
                  Drag & Drop or choose file to upload<br />Front ID
                  proof
                </p>
                <input type="file" />
              </div>
            </div>

            <div class="col-md-6 mb-4">
              <div class="upload-box">
                <i class="bi bi-upload"></i>
                <p>
                  Drag & Drop or choose file to upload<br />Back ID
                  proof
                </p>
                <input type="file" />
              </div>
            </div>

            <div class="col-md-6 mb-4">
              <label class="form-label"
                >Certificates (Optional)</label
              >
              <div class="upload-box">
                <i class="bi bi-upload"></i>
                <p>Drag & Drop or choose file to upload</p>
                <input type="file" />
              </div>
            </div>

            <div class="col-md-6 mb-4">
              <label class="form-label">Profile Photo</label>
              <div class="upload-box">
                <i class="bi bi-upload"></i>
                <p>Drag & Drop or choose file to upload</p>
                <input type="file" />
              </div>
            </div>
          </div>
        </div>
        <div
          class="d-flex justify-content-between align-items-center mt-4"
        >
          <a href="{{ route('register.profile')}}" class="text-success text-decoration-none">
            ← Back
          </a>

          <a href="{{ route('register.bank-details')}}" class="btn btn-login px-5">Continue</a> 
        </div>
      </div>
    </div>
  </div>
</div> 
@endsection
