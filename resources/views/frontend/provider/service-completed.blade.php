@extends('frontend.layouts.provider-layout')

@section('content')
<!-- PROGRESS -->
<div class="progress-wrapper">
    <div class="progress-circle">
        <div class="progress-text">
            <h3>01:20:37</h3>
            <span>Service Done</span>
        </div>
    </div>
</div>

<!-- CARD -->
<div class="card">

    <h4>Home Cleaning Done</h4>
    <p class="small"><i class="bi bi-geo-alt"></i> Sector 62, sahibzada ajit singh nagar....</p>

    <div class="info">
        <p><i class="bi bi-clock"></i> Fri, Dec 12 - 9:30 AM</p>
    </div>

    <hr>

    <div class="section">
        <h4><i class="bi bi-person"></i> Added Services</h4>
        <ul class="provider-list">
        <li>
          <i class="bi bi-check-circle-fill"></i> General house cleaning
        </li>
        <li><i class="bi bi-check-circle-fill"></i> Kitchen deep scrub</li>
      </ul>
    </div>

    <hr>

    <div class="section">
        <h4><i class="bi bi-cash"></i> Payment summary</h4>

        <div class="price">
            <span>Home Deep Cleaning</span>
            <span>$100</span>
        </div>

        <div class="price">
            <span>General house cleaning</span>
            <span>$100</span>
        </div>

        <div class="price total">
            <span>Total amount</span>
            <span>$210</span>
        </div>

        <div class="price total">
            <span>Amount to pay</span>
            <span>$210</span>
        </div>
    </div>

    <hr>

    <div class="section">
        <h4><i class="bi bi-person"></i> Customer Details</h4>
        <p>Riya Sharma</p>
        <p>⭐ 4.3 reviews</p>
        <p>9xxxxxxxxx</p>
    </div>

</div>
@endsection