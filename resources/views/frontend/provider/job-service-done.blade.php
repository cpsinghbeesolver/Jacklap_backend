@extends('frontend.layouts.provider-layout')

@section('content')
<section class="provider-service-section py-5">
    <div class="container">
      <!-- TIMER -->
      <div class="provider-circle">
        <svg class="newsvg"width="150" height="150">
          <circle cx="80" cy="80" r="62" class="bg"></circle>
          <circle cx="80" cy="80" r="62" class="progress-service-done"></circle>
        </svg>

        <div class="text">
          <span>01:20:57</span>
          <p>Service Done</p>
        </div>
      </div>

      <!-- CARD -->
      <div class="provider-service-card">
        <h5>Home Cleaning</h5>
        <p class="muted">
          <i class="bi bi-geo-alt"></i> Sector 62, sahibzada ajit singh
          nagar...
        </p>
        <p class="muted"><i class="bi bi-clock"></i> Fri, Dec 12 - 9:30 AM</p>

        <hr />

        <!-- SERVICES -->
        <h6><i class="fa-solid fa-user"></i> Added Services</h6>
        <ul class="provider-list">
          <li>
            <i class="fa-solid fa-circle-check"></i> General house cleaning
          </li>
          <li><i class="fa-solid fa-circle-check"></i> Kitchen deep scrub</li>
        </ul>

        <hr />

        <!-- PAYMENT -->
        <h6><i class="fa-solid fa-wallet"></i> Payment summary</h6>

        <div class="row-line">
          <span>Home Deep Cleaning</span>
          <span>$100</span>
        </div>

        <div class="row-line">
          <span class="green">Added Services:</span>
          <span></span>
        </div>

        <div class="row-line">
          <span>General house cleaning</span>
          <span>$100</span>
        </div>

        <div class="row-line total">
          <span>Total amount</span>
          <span>$210</span>
        </div>

        <div class="row-line total">
          <span>Amount to pay</span>
          <span>$210</span>
        </div>

        <hr />

        <!-- CUSTOMER -->
        <h6><i class="fa-solid fa-user"></i> Customer Details</h6>
        <p>Riya Sharma</p>
        <p class="muted"><i class="bi bi-star-fill"></i> 4.3 reviews</p>
        <p class="muted">9xxxxxxxxx</p>

        <!-- BUTTONS -->
        <div class="provider-actions">
            <a href="{{ route('provider.job.service.completed') }}" class="start">Complete Service</a>
        </div>
      </div>
    </div>
  </section>
@endsection