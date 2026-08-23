@extends('frontend.layouts.provider-layout')

@section('content')
    <!-- REQUEST TABS -->
    <section class="Request-section">
        <div class="container">
            <div class="tabs">
                <span class="active" onclick="selectTab(this)">Upcoming</span>
                <span onclick="selectTab(this)">In Progress</span>
                <span onclick="selectTab(this)">Completed Jobs</span>
            </div>

            <!-- REQUEST CARDS -->
            <div class="row g-4">
                <!-- CARD -->
                <div class="col-md-6">
                    <a href="{{ route('provider.upcoming.job') }}" class="provider-earn-card-link">
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
                    <a href="{{ route('provider.upcoming.job') }}" class="provider-earn-card-link">
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
                    <a href="{{ route('provider.upcoming.job') }}" class="provider-earn-card-link">
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
                    <a href="{{ route('provider.upcoming.job') }}" class="provider-earn-card-link">
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
@push('scripts')
  <script>
    function selectTab(element) {
        let tabs = document.querySelectorAll(".tabs span");
        tabs.forEach(tab => {
            tab.classList.remove("active");
        });
        element.classList.add("active");
    }
  </script>
@endpush