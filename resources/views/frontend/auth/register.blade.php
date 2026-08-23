@extends('frontend.layouts.login-layout')

@section('content')
  <!-- Login Form -->
  <div class="col-lg-7 col-md-12">
    <div class="login-form-">
      <div class="logo mb-3">
        <img src="{{ asset('frontend/images/logo.png')}}" class="logo" />
      </div>
      <h4 class="mb-4">Account Type</h4>


      <div class="form-check mb-3">
        <input
          class="form-check-input"
          type="radio"
          name="accountType"
          id="seeker"
        />
        <label class="form-check-label" for="seeker">
          Service Seeker
        </label>
      </div>

      <div class="form-check mb-4">
        <input
          class="form-check-input"
          type="radio"
          name="accountType"
          id="provider" checked
        />
        <label class="form-check-label" for="provider">
          Service Provider
        </label>
      </div>

      <button class="btn btn-login w-100 mb-3" onclick="goToPage()">
        Continue
      </button>

      <p class="text-center">
        Already have an account?
        <a href="{{ route('login') }}" class="text-success fw-semibold">Login</a>
      </p>
    </div>
  </div>
@endsection
@push('scripts')
  <script>
    function goToPage() {

      var seeker = document.getElementById("seeker");
      var provider = document.getElementById("provider");

      if (seeker.checked) {
        window.location.href = "{{ route('register.account') }}";
      } 
      else if (provider.checked) {
        window.location.href = "{{ route('register.account') }}";
      } 
      else {
        alert("Please select account type");
      }

    }
  </script>
@endpush
