@extends('frontend.layouts.provider-layout')

@section('content')
    <!-- REQUEST TABS -->
    <section class="Request-section">
      <div class="container">
        <!-- REQUEST CARDS -->
        <div class="row g-4">
          <!-- CARD -->
          <div class="col-md-6">
            <a href="{{ route('provider.job.detail')}}" class="provider-earn-card-link">
              <div class="provider-earn-card">
                <h5>Home Deep Cleaning..</h5>

                <div class="provider-tags">
                  <span><i class="bi bi-cash"></i> $1,200</span>
                  <span><i class="bi bi-geo-alt"></i> Sector 21 Mohali..</span>
                  <span><i class="bi bi-geo"></i> 2.3 miles away</span>
                  <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
                </div>
              </div>
            </a>
          </div>

          <!-- COPY SAME CARD -->
          <div class="col-md-6">
            <a href="{{ route('provider.job.detail')}}" class="provider-earn-card-link">
              <div class="provider-earn-card">
                <h5>Home Deep Cleaning..</h5>

                <div class="provider-tags">
                  <span><i class="bi bi-cash"></i> $1,200</span>
                  <span><i class="bi bi-geo-alt"></i> Sector 21 Mohali..</span>
                  <span><i class="bi bi-geo"></i> 2.3 miles away</span>
                  <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
                </div>
              </div>
            </a>
          </div>

          <div class="col-md-6">
            <a href="{{ route('provider.job.detail')}}" class="provider-earn-card-link">
              <div class="provider-earn-card">
                <h5>Home Deep Cleaning..</h5>

                <div class="provider-tags">
                  <span><i class="bi bi-cash"></i> $1,200</span>
                  <span><i class="bi bi-geo-alt"></i> Sector 21 Mohali..</span>
                  <span><i class="bi bi-geo"></i> 2.3 miles away</span>
                  <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
                </div>
              </div>
            </a>
          </div>
          <div class="col-md-6">
            <a href="{{ route('provider.job.detail')}}" class="provider-earn-card-link">
              <div class="provider-earn-card">
                <h5>Home Deep Cleaning..</h5>

                <div class="provider-tags">
                  <span><i class="bi bi-cash"></i> $1,200</span>
                  <span><i class="bi bi-geo-alt"></i> Sector 21 Mohali..</span>
                  <span><i class="bi bi-geo"></i> 2.3 miles away</span>
                  <span><i class="bi bi-clock"></i> Today, 3:00 pm</span>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </section>
@endsection
