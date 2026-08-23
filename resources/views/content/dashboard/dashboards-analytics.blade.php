@extends('layouts.contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
  @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
  @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')

  {{-- ── Row 1: Users & Categories ──────────────────────────────────────── --}}
  <div class="row g-4 mb-4">

    <div class="col-xl-3 col-sm-6">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="ri-group-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-black small">Total Users</p>
            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_users']) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success">
              <i class="ri-briefcase-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-black small">Providers</p>
            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_providers']) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info">
              <i class="ri-user-search-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-black small">Seekers</p>
            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_seekers']) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ri-apps-2-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-black small">Categories</p>
            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_categories']) }}</h4>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- ── Row 2: Bookings ─────────────────────────────────────────────────── --}}
  <div class="row g-4 mb-4">

    <div class="col-xl-3 col-sm-6">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="ri-calendar-check-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-black small">Total Bookings</p>
            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_bookings']) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ri-time-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-black small">Pending</p>
            <h4 class="mb-0 fw-bold">{{ number_format($stats['pending_bookings']) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success">
              <i class="ri-checkbox-circle-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-black small">Completed</p>
            <h4 class="mb-0 fw-bold">{{ number_format($stats['completed_bookings']) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="ri-close-circle-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-black small">Cancelled</p>
            <h4 class="mb-0 fw-bold">{{ number_format($stats['cancelled_bookings']) }}</h4>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- ── Row 3: Earnings ─────────────────────────────────────────────────── --}}
  <div class="row g-4 mb-4">

    <div class="col-xl-6 col-sm-6">
      <div class="card h-100 border-0" style="background: linear-gradient(135deg, #696cff 0%, #9055fd 100%);">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-white bg-opacity-25 text-black">
              <i class="ri-money-dollar-circle-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-white fs-4">Total Earnings <span class="ms-1 badge bg-white bg-opacity-25 text-black" style="font-size:13px;">Completed</span></p>
            <h4 class="mb-0 fw-bold text-white">₹ {{ number_format($stats['total_earnings'], 2) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-6 col-sm-6">
      <div class="card h-100 border-0" style="background: linear-gradient(135deg, #ffab00 0%, #ff7b00 100%);">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-white bg-opacity-25 text-black">
              <i class="ri-hourglass-line ri-24px"></i>
            </span>
          </div>
          <div>
            <p class="mb-0 text-white fs-4">Pending Earnings <span class="ms-1 badge bg-white bg-opacity-25 text-black" style="font-size:13px;">Awaiting</span></p>
            <h4 class="mb-0 fw-bold text-white">₹ {{ number_format($stats['pending_earnings'], 2) }}</h4>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- ── Weekly Overview Chart ───────────────────────────────────────────── --}}
  <div class="mb-5">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">Weekly Overview</h5>
            <small class="text-secondary">Slot bookings for current week</small>
          </div>
          <div class="dropdown">
            <button class="btn text-secondary p-0" type="button" id="weeklyOverviewDropdown" data-bs-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              <i class="ri-more-2-line ri-24px"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="weeklyOverviewDropdown">
              <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
              <a class="dropdown-item" href="javascript:void(0);">Share</a>
              <a class="dropdown-item" href="javascript:void(0);">Update</a>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="weeklyOverviewChart"></div>
        <div class="mt-3">
          <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0">{{ array_sum($weeklySeries) }}</h4>
            <p class="mb-0 text-secondary">total slot bookings this week</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  {{-- /Weekly Overview Chart --}}

  {{-- Data Tables --}}
  @include('_partials.userlist_table')
  {{-- /Data Tables --}}

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($weeklyLabels);
    const series = @json($weeklySeries);

    const options = {
      series: [{
        name: 'Slot Bookings',
        data: series
      }],
      chart: {
        type: 'bar',
        height: 280,
        toolbar: { show: false },
      },
      plotOptions: {
        bar: {
          borderRadius: 6,
          columnWidth: '40%',
        }
      },
      dataLabels: { enabled: false },
      xaxis: {
        categories: labels,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
          style: {
            fontSize: '15px' // Sets X-axis font size
          }
        }
      },
      yaxis: {
        labels: {
          formatter: val => Math.floor(val),
          style: {
            fontSize: '15px' // Sets X-axis font size
          }
        }
      },
      grid: {
        borderColor: 'rgba(0,0,0,0.05)',
        strokeDashArray: 4,
      },
      colors: ['#696cff'],
      tooltip: {
        y: {
          formatter: val => val + ' bookings'
        }
      }
    };

    const chart = new ApexCharts(document.querySelector('#weeklyOverviewChart'), options);
    chart.render();
  });
</script>
@endsection