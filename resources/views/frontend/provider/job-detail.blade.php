@extends('frontend.layouts.provider-layout')

@section('content')
<section class="provider-job">
    <div class="container border-job-provider">
  
      <h2 class="provider-job-title">Home Deep Cleaning</h2>
      <p class="provider-job-location">Sector 21, Mohali</p>
  
      <div class="provider-job-tags">
        <span><i class="bi bi-cash"></i> $1,200</span>
        <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
        <span><i class="bi bi-geo-alt"></i> 2.3 miles away</span>
      </div>
          <h2 class="provider-job-about">About This Job</h2>
      <p class="provider-job-desc">
        A full deep cleaning service required for a 2BHK home. Includes dusting,
        mopping, kitchen cleaning, and bathroom sanitization.
      </p>
      <div class="provider-task">
  
    <h4 class="provider-task-title">Task included</h4>
  
    <ul class="provider-task-list">
      <li><i class="fa-solid fa-circle-check"></i> General house cleaning</li>
      <li><i class="fa-solid fa-circle-check"></i> Kitchen deep scrub</li>
      <li><i class="fa-solid fa-circle-check"></i> Bathroom sanitization</li>
      <li><i class="fa-solid fa-circle-check"></i> Dusting & mopping</li>
      <li><i class="fa-solid fa-circle-check"></i> Waste disposal</li>
    </ul>
     <hr />
  
    <!-- LOCATION -->
    <h4 class="provider-location-title">Location</h4>
  
    <div class="provider-map">
        <iframe
      src="https://maps.google.com/maps?q=mohali&t=&z=13&ie=UTF8&iwloc=&output=embed"
      width="100%"
      height="250"
      style="border:0; border-radius:12px;"
      allowfullscreen=""
      loading="lazy">
    </iframe>
    </div>
  
    <p class="provider-direction">
      <i class="bi bi-geo-alt"></i> Tap to get directions
    </p>
  
    <hr />
  
    <!-- CUSTOMER -->
    <div class="provider-customer">
      <h4>Customer Info</h4>
  
      <p><strong>Name:</strong> Riya Sharma</p>
      <p><strong>Phone:</strong> 9xxxxxxxxx</p>
  
      <p class="provider-instruction-title">Instructions:</p>
      <p class="provider-instruction-text">
        Please call on arrival. Pets at home <br />
        —don’t worry, they’re friendly.
      </p>
    </div>
  <div class="provider-earnings container">
  
    <h4 class="provider-earnings-title">Earnings</h4>
  
    <div class="provider-earnings-wrapper">
  
      <!-- LEFT SIDE -->
      <div class="provider-earnings-left">
  
        <div class="provider-row">
          <span>Home Cleaning:</span>
          <span>$1,200</span>
        </div>
  
        <div class="provider-row">
          <span>Service Added Fee:</span>
          <span></span>
        </div>
  
        <div class="provider-row">
          <span>Fridge/oven cleaning</span>
          <span>$1,200</span>
        </div>
  
        <div class="provider-row">
          <span>Taxes</span>
          <span>$1,200</span>
        </div>
  
        <div class="provider-row total">
          <span>Your Earnings:</span>
          <span>$1,140</span>
        </div>
  
      </div>
  
      <!-- RIGHT BUTTON -->
      <div class="provider-earnings-right">
        <button class="provider-accept-btn">Accept</button>
      </div>
  
    </div>
  
  </div>
  </div>
  
  </div>
    </div>
  </section>
@endsection