@extends('frontend.layouts.provider-layout')

@section('content')
<div class="job-layout">
    <section class="Request-section">
      <!-- TABS -->
      <div class="tabs">
        <a href="{{ route('provider.upcoming.job') }}" class="tab {{ request()->routeIs('provider.upcoming.job*') ? 'active' : '' }}">Upcoming</a>
        <a href="{{ route('provider.inprogress') }}" class="tab {{ request()->routeIs('provider.inprogress*') ? 'active' : '' }}">In Progress</a>
        <a href="#" class="tab">Completed Jobs</a>
      </div>

      <!-- TWO COLUMN LAYOUT -->
      <div class="jobs-container">
        <!-- LEFT SIDE : JOB LIST -->
        <div class="job-list">
          <div class="job-card active">
            <h3>Home Deep Cleaning..</h3>

            <div class="tags">
              <span><i class="bi bi-cash"></i> $1,200</span>
              <span><i class="bi bi-geo-alt"></i> Sector 21 Mohali...</span>
              <span><i class="bi bi-pin-map"></i> 2.3 miles away</span>
              <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
            </div>
          </div>

          <div class="job-card">
            <h3>Home Deep Cleaning..</h3>

            <div class="tags">
              <span><i class="bi bi-cash"></i> $1,200</span>
              <span><i class="bi bi-geo-alt"></i> Sector 21 Mohali...</span>
              <span><i class="bi bi-pin-map"></i> 2.3 miles away</span>
              <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
            </div>
          </div>
        </div>

        <!-- RIGHT SIDE : JOB DETAILS -->
        <div class="job-details">
          <h3>Booking Confirmed</h3>
          <p class="small">
            Your service professional will arrive at the scheduled time.
          </p>

          <div class="info">
            <p><i class="bi bi-calendar"></i> Fri, Dec 12 - 9:30 AM</p>
            <p>
              <i class="bi bi-geo-alt"></i> Sector 62, Sahibzada Ajit Singh
              Nagar...
            </p>
          </div>

          <hr />

          <h4>Added Services</h4>
          <ul>
            <li>✔ General house cleaning</li>
            <li>✔ Kitchen deep scrub</li>
          </ul>

          <hr />

          <h4>Customer Details</h4>
          <p>Riya Sharma</p>
          <p>4.3 reviews</p>
          <p>9xxxxxxx</p>

          <hr />

          <h4>Payment summary</h4>

          <div class="price">
            <span>Item total</span>
            <span>$200</span>
          </div>

          <div class="price">
            <span>Taxes and Fee</span>
            <span>$10</span>
          </div>

          <div class="price total">
            <span>Total amount</span>
            <span>$210</span>
          </div>

          <div class="price total">
            <span>Amount to pay</span>
            <span>$210</span>
          </div>

          <button class="btn-main">
            <a href="provider_job_start.html">Continue</a>
          </button>

          <button class="btn-outline">
            <a href="home_page.html">Cancel the booking</a>
          </button>
        </div>
      </div>
    </section>
  </div>
@endsection