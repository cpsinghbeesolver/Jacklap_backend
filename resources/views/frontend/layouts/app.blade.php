<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
 
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Noto+Serif:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/fav.png') }}" />

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Custom CSS (always last) -->
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css')}}" />
  <link rel="stylesheet" href="{{ asset('frontend/css/responsive.css')}}">
  </head>
  <body>
    <section class="header">
      <div class="container">
        <nav class="navbar login">
          <div class="logo">
            <img src="{{ asset('frontend/images/logo.png')}}" class="logo" />
          </div>
          <a href="{{ route('login') }}" class="login-btn">Login</a>
        </nav>
      </div>
    </section>
    @yield('content')
    @include('frontend.layouts.footer')
  </body>
  @stack('scripts')
</html>
