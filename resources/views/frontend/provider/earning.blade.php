@extends('frontend.layouts.provider-layout')

@section('content')
<section class="view_profile_section">
    <div class="container">
      <div class="row">
        <!-- LEFT SIDEBAR -->
        <div class="col-md-5">
          <div class="view_profile_sidebar">
            <h6>Profile</h6>
            <a href="{{ route('provider.edit.profile')}}" class="{{ request()->routeIs('provider.edit.prof*') ? 'active' : '' }}">Edit Profile</a>

            <hr />

            <h6>Earnings</h6>
            <a href="{{ route('provider.earnings')}}" class="{{ request()->routeIs('provider.earning*') ? 'active' : '' }}">My Earnings</a>
          </div>
        </div>

        <!-- RIGHT CONTENT -->
        <!-- TOP -->
        <div class="col-md-7 brdr">
          <div
            class="provider-earnings-top d-flex justify-content-between align-items-center"
          >
            <div>
              <h3>Earnings Overview</h3>
              <p>Monthly earnings for the year</p>
            </div>
          </div>

          <!-- GRAPH -->
          <div class="provider-chart">
            <!-- Y Labels -->
            <div class="y-label y1">50k</div>
            <div class="y-label y2">20k</div>
            <div class="y-label y3">10k</div>
            <div class="y-label y4">0</div>

            <!-- Lines -->
            <div class="grid g1"></div>
            <div class="grid g2"></div>
            <div class="grid g3"></div>

            <!-- Value Tag -->
            <div class="price-tag">$20,000</div>

            <!-- SVG Line -->
            <svg viewBox="0 0 600 200" class="graph-line">
              <path
                d="M10 160 
                   C80 200, 120 100, 180 140 
                   S280 80, 330 70 
                   S420 90, 480 80"
                stroke="#1f5d2e"
                stroke-width="3"
                fill="none"
              />
            </svg>

            <!-- Months -->
            <div class="months">
              <span class="active">Jan</span>
              <span>Feb</span>
              <span>Mar</span>
              <span>Jun</span>
              <span>Jul</span>
              <span>Aug</span>
              <span>Sept</span>
              <span>Oct</span>
              <span>Nov</span>
              <span>Dec</span>
            </div>
          </div>
          
        </div>
        
        
      </div>
    </div>
  </section>
@endsection