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
        <div class="col-lg-12">
          <div class="col-lg-12">
            <div class="provider-earn-card active" onclick="selectCard(this)">
              <h5>Home Deep Cleaning..</h5>
              <div class="provider-tags">
                <span><i class="bi bi-cash"></i> $1,200</span>
                <span><i class="bi bi-geo-alt"></i> Sector 21 Mohali..</span>
                <span><i class="bi bi-geo"></i> 2.3 miles away</span>
                <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
              </div>
            </div>
          </div>

          <!-- SECOND CARD -->
          <div class="col-lg-12">
            <div class="provider-earn-card" onclick="selectCard(this)">
              <h5>Home Deep Cleaning..</h5>
              <div class="provider-tags">
                <span><i class="bi bi-cash"></i> $1,200</span>
                <span><i class="bi bi-geo-alt"></i> Sector 21 Mohali..</span>
                <span><i class="bi bi-geo"></i> 2.3 miles away</span>
                <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
              </div>
            </div>
          </div>
          <!-- Third CARD -->
          <div class="col-lg-12">
            <div class="provider-earn-card" onclick="selectCard(this)">
              <h5>Home Deep Cleaning..</h5>
              <div class="provider-tags">
                <span><i class="bi bi-cash"></i> $1,200</span>
                <span><i class="bi bi-geo-alt"></i> Sector 21 Mohali..</span>
                <span><i class="bi bi-geo"></i> 2.3 miles away</span>
                <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT SIDE : JOB DETAILS -->
        <div class="col-lg-12">
          <div class="job-details">
            <h4><i class="fa-regular fa-calendar"></i> Booking Confirmed</h4>
            <p class="small">
              Your service professional will arrive at the scheduled time.
            </p>

            <div class="info">
              <p><i class="fa-solid fa-clock"></i> Fri, Dec 12 - 9:30 AM</p>
              <p>
                <i class="bi bi-geo-alt"></i> Sector 62, Sahibzada Ajit Singh
                Nagar...
              </p>
            </div>

            <hr />

            <p><i class="fa-solid fa-user"></i> <b>Added Services</b></p>
            <ul class="list-green-color">
              <li>
                <i class="fa-solid fa-circle-check"></i> General house
                cleaning
              </li>
              <li>
                <i class="fa-solid fa-circle-check"></i> Kitchen deep scrub
              </li>
            </ul>

            <hr />

            <p><i class="fa-solid fa-user"></i><b> Customer Details</b></p>
            <p>Riya Sharma</p>
            <p><i class="bi bi-star-fill"></i> 4.3 reviews</p>
            <p>9xxxxxxx</p>

            <hr />

            <p><i class="fa-solid fa-wallet"></i><b> Payment summary</b></p>

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
            <a href="{{ route('provider.inprogress.job') }}">
              <button class="btn-main">Continue</button>
            </a>
            <a href="#">
              <button class="btn-outline">Cancel the booking</button>
            </a>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
@push('scripts')
    <script>
        function selectCard(el) {
            document
                .querySelectorAll(".provider-earn-card")
                .forEach((c) => c.classList.remove("active"));
            el.classList.add("active");
        }
    </script>
@endpush