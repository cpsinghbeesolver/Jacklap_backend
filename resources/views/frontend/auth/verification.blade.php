@extends('frontend.layouts.login-layout')

@section('content')
  <div class="col-lg-7 col-md-12">
    <div class="login-form-">
      <div class="logo mb-3">
        <img src="{{ asset('frontend/images/logo.png')}}" class="logo" />
      </div>

      <div class="verify-section">
        <h3 class="verify-title">Please Check your email</h3>

        <p class="verify-text">
          We have sent the code to
          <span class="verify-email">alma.lawson@gmail.com</span>
        </p>

        <div class="otp-wrapper">
          <input type="text" maxlength="1" />
          <input type="text" maxlength="1" />
          <input type="text" maxlength="1" />
          <input type="text" maxlength="1" />
          <input type="text" maxlength="1" />
        </div>

        <div class="verify-footer">
          <a href="{{route('register.account')}}" class="back-link">
            <i class="bi bi-chevron-left"></i> Back
          </a>

          <a href="{{route('register.profile')}}" class="btn btn-login px-5">Submit</a> 
        </div>
      </div>
    </div>
  </div>
@endsection
