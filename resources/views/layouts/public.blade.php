<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Click'n Eat</title>

    <meta name="description" content="Plateforme de commande de repas en ligne" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Icons - Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar layout-without-menu">
        <div class="layout-container">
            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <!-- Logo -->
                        <div class="navbar-nav align-items-center">
                            <a class="nav-link" href="{{ url('/') }}">
                                <span class="app-brand-text demo menu-text fw-bolder ms-2">Click'n Eat</span>
                            </a>
                        </div>
                        <!-- /Logo -->

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- Navigation Links -->
                            @auth
                                <li class="nav-item me-2 me-xl-0">
                                    <a class="nav-link" href="{{ url('/dashboard') }}">
                                        <i class="bx bx-home-circle me-1"></i> Tableau de bord
                                    </a>
                                </li>
                                <li class="nav-item me-2 me-xl-0">
                                    <a class="nav-link" href="{{ url('/profile') }}">
                                        <i class="bx bx-user me-1"></i> Profil
                                    </a>
                                </li>
                                <li class="nav-item me-2 me-xl-0">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <a class="nav-link" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="bx bx-power-off me-1"></i> Déconnexion
                                        </a>
                                    </form>
                                </li>
                            @else
                                <li class="nav-item me-2 me-xl-0">
                                    <a class="nav-link" href="{{ url('/login') }}">
                                        <i class="bx bx-log-in me-1"></i> Connexion
                                    </a>
                                </li>
                                <li class="nav-item me-2 me-xl-0">
                                    <a class="nav-link" href="{{ url('/register') }}">
                                        <i class="bx bx-user-plus me-1"></i> Inscription
                                    </a>
                                </li>
                            @endauth
                        </ul>
                    </div>
                </nav>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                {{ date('Y') }}, Click'n Eat
                            </div>
                            <div>
                                <a href="#" class="footer-link me-4">Mentions légales</a>
                                <a href="#" class="footer-link me-4">CGU</a>
                                <a href="#" class="footer-link">Confidentialité</a>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
    </div>
    <!-- / Layout wrapper -->


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
