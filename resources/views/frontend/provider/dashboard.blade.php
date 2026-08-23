@extends('frontend.layouts.provider-layout')

@section('content')
  <section class="provider-availability">
    <div class="container d-flex justify-content-between align-items-center">
      <!-- LEFT -->
      <div class="provider-availability-left">
        <div class="provider-availability-top d-flex align-items-center">
          <strong>Availability</strong>

          <!-- Toggle (same line) -->
          <label class="provider-switch">
            <input type="checkbox" />
            <span class="provider-slider"></span>
          </label>
        </div>

        <p>Enable Online to start working</p>
      </div>

      <!-- RIGHT -->
      <a href="#" class="provider-manage">
        Manage Availability <i class="bi bi-chevron-right"></i>
      </a>
    </div>
  </section>
  <section class="provider-banner">
    <div class="container">
      <div
        class="provider-banner-box d-flex align-items-center justify-content-between"
      >
        <!-- LEFT TEXT -->
        <div class="provider-banner-content">
          <h1>
            Welcome! Start <br />
            Receiving Request
          </h1>
          <p>Accept Request near you and grow your service business.</p>
          <button class="provider-btn">View Request</button>
        </div>

        <!-- RIGHT IMAGE -->
        <div class="provider-banner-img">
          <img src="{{ asset('frontend/images/provider_banner.svg') }}" alt="banner" />
        </div>
      </div>
    </div>
  </section>
  <section class="provider-summary">
    <div class="container">
      <!-- Heading -->
      <h2>Today’s Summary</h2>
      <p class="provider-summary-sub">
        Overview of today’s jobs, completed tasks & earnings.
      </p>

      <!-- Cards -->
      <div class="row">
        <!-- Card 1 -->
        <div class="col-md-4">
          <div class="provider-card">
            <h3>0</h3>

            <div class="provider-card-title">
              <i class="fa-solid fa-briefcase"></i>
              <span>Jobs Today</span>
            </div>

            <p class="provider-card-desc">Scheduled for today</p>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="col-md-4">
          <div class="provider-card">
            <h3>0</h3>

            <div class="provider-card-title">
              <i class="fa-solid fa-circle-check"></i>
              <span>Completed</span>
            </div>

            <p class="provider-card-desc">Finished so far</p>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="col-md-4">
          <div class="provider-card">
            <h3>1,234</h3>

            <div class="provider-card-title">
              <i class="fa-solid fa-wallet"></i>
              <span>Today’s Earnings</span>
            </div>

            <p class="provider-card-desc">Track your earnings</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="provider-earnings">
    <div class="container">
      <!-- TOP -->
      <div
        class="provider-earnings-top d-flex justify-content-between align-items-center"
      >
        <div>
          <h2>Earnings Overview</h2>
          <p>Monthly earnings for the year</p>
        </div>

        <div class="provider-dropdown">
          <span>This month <i class="bi bi-chevron-down"></i></span>
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
  </section>
  <section class="provider-tips">
    <div class="container">
      
      <h2 class="provider-tips-title">Tips to Get More Request</h2>
      <p class="provider-tips-sub">Maximize your chances of getting hired.</p>

      <ul class="provider-tips-list">
        <li><i class="bi bi-check-lg"></i> Stay online during peak hours to get more Request.</li>
        <li><i class="bi bi-check-lg"></i> Keep Your Profile Complete</li>
        <li><i class="bi bi-check-lg"></i> Respond quickly to boost your acceptance rate.</li>
        <li><i class="bi bi-check-lg"></i> Switch the toggle ON when you're ready to work.</li>
      </ul>
    </div>
  </section>
@endsection