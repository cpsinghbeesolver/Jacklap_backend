@extends('frontend.layouts.provider-layout')

@section('content')

<div class="job-layout">
  <section class="Request-section">
    <!-- TABS -->
    <div class="tabs">
      <a href="jobs_page.html" class="tab active">Upcoming</a>
      <a href="inprogress.html" class="tab">In Progress</a>
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
            <span>Home Deep Cleaning</span>
            <span>$100</span>
          </div>
          <span class="green"> Added Services: </span>
          <div class="price">
            <span>General house cleaning</span>
            <span>$10</span>
          </div>
          <hr />

          <div class="price total">
            <span class="green">Total amount</span>
            <span class="green">$210</span>
          </div>
          <hr />
          <div class="price total">
            <span class="green">Amount to pay</span>
            <span class="green">$210</span>
          </div>
          <hr />
           <a href="{{ route('provider.job.start') }}">
            <button class="btn-main">Start Journey</button> 
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