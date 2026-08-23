<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Jacklap Dashboard</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/fav.png') }}" />

    <link
      href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Noto+Serif:ital,wght@0,100..900;1,100..900&display=swap"
      rel="stylesheet"
    />
    <!-- Bootstrap -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
    />
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/css/in-progress.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/css/progress-bar.css') }}" />
  </head>
  <body>
<header class="provider-header">
  <div class="container d-flex align-items-center justify-content-between">
    <!-- LEFT -->
    <div class="provider-left d-flex align-items-center">
      <!-- Logo -->
      <a href="{{route('provider.dashboard')}}" class="provider-logo">
        <img src="{{ asset('frontend/images/logo.png') }}" alt="logo" />
      </a>

      <!-- Nav -->
      <nav class="provider-nav">
        <a href="{{route('provider.dashboard')}}" class="{{ request()->routeIs('provider.dashboard*') ? 'active' : '' }}">Home</a>
        <a href="{{route('provider.request')}}" class="{{ request()->routeIs('provider.request*') ? 'active' : '' }}">Request</a>
        <a href="{{route('provider.job')}}" class="{{ request()->routeIs('provider.job*') ? 'active' : '' }}">Jobs</a>
      </nav>
    </div>

    <!-- RIGHT -->
    <div class="provider-right d-flex align-items-center">
      <i class="bi bi-bell"></i>
      
      <!-- User Button with Photo -->
      <div class="user-menu">
        <button class="user-btn" id="userBtn">
          <i class="bi bi-person"></i>
        
        </button>
        
        <!-- Dropdown Menu -->
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="{{route('provider.edit.profile')}}" class="dropdown-item">
            <i class="bi bi-person"></i>
            Edit Profile
          </a>
          <a href="{{route('provider.earnings')}}" class="dropdown-item">
            <i class="bi bi-gear"></i>
            Earnings
          </a>
        </div>
      </div>
    </div>
  </div>
</header>
    @yield('content')
    @include('frontend.layouts.footer')
</body>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userBtn = document.getElementById('userBtn');
            const dropdownMenu = document.getElementById('dropdownMenu');
    
            // Toggle dropdown on button click
            userBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
                userBtn.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
                userBtn.classList.remove('active');
                }
            });
            
            // Close dropdown when clicking on a dropdown item
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                dropdownMenu.classList.remove('show');
                userBtn.classList.remove('active');
                });
            });
        });
    </script>
    @stack('scripts')
</html>
