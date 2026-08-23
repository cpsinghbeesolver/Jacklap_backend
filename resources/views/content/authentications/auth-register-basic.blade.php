@extends('layouts.blankLayout')

@section('title', 'Register')

@section('page-style')
  @vite([
    'resources/assets/vendor/scss/pages/page-auth.scss'
  ])
@endsection


@section('content')
  <div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6 mx-4">

      <!-- Register Card -->
      <div class="card p-7">
      <!-- Logo -->
      <div class="app-brand justify-content-center mt-5">
        <a href="{{url('/')}}" class="app-brand-link gap-3">
        <span class="app-brand-logo demo">@include('_partials.macros', ["height" => 20])</span>
        <span class="app-brand-text demo text-heading fw-semibold">User Registeration</span>
        </a>
      </div>
      <!-- /Logo -->
      <div class="card-body mt-1">
         {{--  <h4 class="mb-1">Adventure starts here 🚀</h4>  --}}
        <!-- <p class="mb-5">Make your app management easy and fun!</p> -->

        <form id="formAuthentication" class="mb-5" action="{{ route('register') }}" method="POST">
        @csrf
        <div class="form-floating form-floating-outline mb-5">
          <input type="text" class="form-control @error('name') is-invalid @enderror" autocomplete="off" id="name" name="name"
          placeholder="Enter your name" autofocus value="{{ old('name') }}">
          <label for="name">Name</label>
          @error('name')
        <small class="text-danger">{{ $message }}</small>
      @enderror
        </div>
        <div class="form-floating form-floating-outline mb-5">
          <input type="text" class="form-control @error('email') is-invalid @enderror" autocomplete="off" id="email" name="email"
          placeholder="Enter your email" value="{{ old('email') }}">
          <label for="email">Email</label>
          @error('email')
        <small class="text-danger">{{ $message }}</small>
      @enderror
        </div>
        <div class="mb-5 form-password-toggle">
          <div class="input-group input-group-merge">
          <div class="form-floating form-floating-outline">


            <input type="password" id="password" autocomplete="off" class="form-control @error('password') is-invalid @enderror" name="password"
            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
            aria-describedby="password" />
            <label for="password">Password</label>
           
          </div>
          <span class="input-group-text cursor-pointer"><i class="ri-eye-off-line ri-20px"></i></span>
          </div>
           @error('password')
        <small class="text-danger">{{ $message }}</small>
        @enderror
        </div>

        <!-- Company Name -->
        <div class="form-floating form-floating-outline mb-5">
          <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" autocomplete="off" id="phone_number" name="phone_number"
          placeholder="Enter your phone number" value="{{ old('phone_number') }}">
          <label for="company_name">Phone Number</label>
          @error('phone_number')
        <small class="text-danger">{{ $message }}</small>
      @enderror
        </div>

        <div class="mb-5 py-2">
          <div class="form-check mb-0">
          <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms-conditions" name="terms" {{ old('terms') ? 'checked' : '' }}>
          <label class="form-check-label" for="terms-conditions">
            I agree to
            <a href="javascript:void(0);">privacy policy & terms</a>
          </label>
          </div>
           @error('terms')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>
        <button class="btn btn-primary d-grid w-100 mb-5" type="submit">
          Sign up
        </button>
        </form>

        <p class="text-center mb-5">
        <span>Already have an account?</span>
        <a href="{{url('auth/login-basic')}}">
          <span>Sign in instead</span>
        </a>
        </p>
      </div>
      </div>
      <!-- Register Card -->

      <img src="{{asset('assets/img/illustrations/tree-3.png')}}" alt="auth-tree
    " class="authentication-image-object-left d-none d-lg-block">
      <img src="{{asset('assets/img/illustrations/auth-basic-mask-light.png')}}
    " class="authentication-image d-none d-lg-block" height="172" alt="triangle-bg">
      <img src="{{asset('assets/img/illustrations/tree.png')}}" alt="auth-tree"
      class="authentication-image-object-right d-none d-lg-block">
    </div>
    </div>
  </div>
@endsection