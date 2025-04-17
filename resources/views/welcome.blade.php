<!DOCTYPE html>
<html
  lang="{{ str_replace('_', '-', app()->getLocale()) }}"
  class="light-style"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('assets/') }}"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Click\'n Eat') }}</title>

    <meta name="description" content="Plateforme de commande de repas en ligne" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    
    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <!-- Config -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    
    <style>
      .hero-section {
        background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('{{ asset('assets/img/elements/food-bg.jpg') }}');
        background-size: cover;
        background-position: center;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
      }
      
      .feature-card {
        transition: transform 0.3s ease;
      }
      
      .feature-card:hover {
        transform: translateY(-10px);
      }
      
      .food-category {
        transition: transform 0.3s ease;
        cursor: pointer;
      }
      
      .food-category:hover {
        transform: scale(1.05);
      }
      
      .restaurant-card {
        transition: transform 0.3s ease;
      }
      
      .restaurant-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      }
      
      .restaurant-img {
        height: 180px;
        object-fit: cover;
        width: 100%;
      }
      
      .menu-item-card {
        transition: all 0.3s ease;
      }
      
      .menu-item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      }
    </style>
  </head>

  <body>
    <!-- Navbar -->
    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav align-items-center">
          <a class="nav-link" href="/">
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Click'n Eat</span>
          </a>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
          @if (Route::has('login'))
            @auth
              <li class="nav-item">
                <a class="nav-link" href="{{ url('/dashboard') }}">
                  <i class="bx bx-home-circle me-1"></i>
                  <span>Dashboard</span>
                </a>
              </li>
            @else
              <li class="nav-item">
                <a class="nav-link" href="{{ route('login') }}">
                  <i class="bx bx-log-in me-1"></i>
                  <span>Connexion</span>
                </a>
              </li>
              @if (Route::has('register'))
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('register') }}">
                    <i class="bx bx-user-plus me-1"></i>
                    <span>Inscription</span>
                  </a>
                </li>
              @endif
            @endauth
          @endif
        </ul>
      </div>
    </nav>
    <!-- / Navbar -->

    @yield('content')

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">Click'n Eat</h5>
                    <p>Votre plateforme de commande de repas en ligne. Découvrez les meilleurs restaurants près de chez vous et commandez en quelques clics.</p>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">Liens utiles</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('restaurants.index') }}" class="text-white-50">Restaurants</a></li>
                        @auth
                            <li class="mb-2"><a href="{{ route('dashboard') }}" class="text-white-50">Mon compte</a></li>
                        @else
                            <li class="mb-2"><a href="{{ route('login') }}" class="text-white-50">Connexion</a></li>
                            <li class="mb-2"><a href="{{ route('register') }}" class="text-white-50">Inscription</a></li>
                        @endauth
                    </ul>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">Contactez-nous</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bx bx-envelope me-2"></i> contact@clickneat.com</li>
                        <li class="mb-2"><i class="bx bx-phone me-2"></i> +33 1 23 45 67 89</li>
                        <li class="mb-2"><i class="bx bx-map me-2"></i> 123 Rue de la Gastronomie, Paris</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="text-white mb-3">Suivez-nous</h5>
                    <div class="d-flex">
                        <a href="#" class="text-white me-3 fs-4"><i class="bx bxl-facebook-circle"></i></a>
                        <a href="#" class="text-white me-3 fs-4"><i class="bx bxl-twitter"></i></a>
                        <a href="#" class="text-white me-3 fs-4"><i class="bx bxl-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; {{ date('Y') }} Click'n Eat. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white-50 me-3">Mentions légales</a>
                    <a href="#" class="text-white-50">Politique de confidentialité</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
  </body>
</html>
