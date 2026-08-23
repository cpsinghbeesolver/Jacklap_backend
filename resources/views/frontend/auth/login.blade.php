@extends('frontend.layouts.login-layout')

@section('content')
  <!-- Login Form -->
  <div class="col-lg-7 col-md-12">
    <div class="login-form-">
      <div class="logo mb-3">
        <img src="{{ asset('frontend/images/logo.png') }}" class="logo" />
      </div>

      <h2 class="mb-2">Login</h2>
      <p class="text-muted mb-4">
        Sign in to connect, collaborate, and grow.
      </p>

      <form>
        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <input type="email" class="form-control" />
        </div>

        <div class="mb-2">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" />
        </div>

        <div class="text-end mb-3">
          <a href="#" class="small text-muted">Forgot password?</a>
        </div>

        <button class="btn btn-login w-100 mb-3">Sign in</button>

        <div class="or-divider">or sign in with</div>

        <div class="row g-2 mt-2">
          <div class="col-4">
            <button class="social-btn w-100">
              <img
                src="{{ asset('frontend/images/google.svg') }}"
                class="social-icon"
              />
            </button>
          </div>

          <div class="col-4">
            <button class="social-btn w-100">
              <img
                src="{{ asset('frontend/images/apple.svg') }}"
                class="social-icon"
              />
            </button>
          </div>

          <div class="col-4">
            <button class="social-btn w-100">
              <img
                src="{{ asset('frontend/images/facebook.svg') }}"
              />
            </button>
          </div>
        </div>

        <p class="text-center mt-4">
          Don't have an account?
          <a href="{{ route('register') }}" class="text-success fw-semibold">Sign Up</a>
        </p>
      </form>
    </div>
  </div>
@endsection
