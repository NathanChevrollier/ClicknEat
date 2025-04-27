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

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

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
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('dashboard') }}" class="app-brand-link">
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">Click'n Eat</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">
                    @if(\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->isAdmin())
                    <!-- Tableau de bord admin -->
                    <li class="menu-item {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.restaurants.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.items.*') || request()->routeIs('admin.menus.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div>Tableau de bord admin</div>
                        </a>
                    </li>
                    
                    <!-- Gestion utilisateurs -->
                    <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div>Gestion utilisateurs</div>
                        </a>
                    </li>
                    
                    <!-- Gestion des restaurants -->
                    <li class="menu-item {{ request()->routeIs('admin.restaurants.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.restaurants.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-store"></i>
                            <div>Gestion restaurants</div>
                        </a>
                    </li>
                    
                    <!-- Gestion des commandes -->
                    <li class="menu-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.orders.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-receipt"></i>
                            <div>Gestion commandes</div>
                        </a>
                    </li>
                    
                    <!-- Gestion des réservations -->
                    <li class="menu-item {{ request()->routeIs('admin.reservations') ? 'active' : '' }}">
                        <a href="{{ route('reservations.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-calendar"></i>
                            <div>Gestion réservations</div>
                        </a>
                    </li>
                    
                    <!-- Gestion des avis -->
                    <li class="menu-item {{ request()->routeIs('admin.reviews') ? 'active' : '' }}">
                        <a href="{{ route('restaurants.index') }}?filter=all_reviews" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-star"></i>
                            <div>Gestion avis</div>
                        </a>
                    </li>
                    @elseif(\Illuminate\Support\Facades\Auth::check())
                    <!-- Dashboard (pour non-admin) -->
                    <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div>Tableau de bord</div>
                        </a>
                    </li>
                    @endif

                    @if(\Illuminate\Support\Facades\Auth::check())
                    <!-- Restaurants -->
                    <li class="menu-item {{ request()->routeIs('restaurants.*') ? 'active' : '' }}">
                        <a href="{{ route('restaurants.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-store"></i>
                            <div>Restaurants</div>
                        </a>
                    </li>

                    <!-- Commandes -->
                    @if(\Illuminate\Support\Facades\Auth::user()->isRestaurateur())
                    <li class="menu-item {{ request()->routeIs('restaurateur.orders') ? 'active' : '' }}">
                        <a href="{{ route('restaurateur.orders') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-receipt"></i>
                            <div>Commandes</div>
                        </a>
                    </li>
                    @else
                    <li class="menu-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                        <a href="{{ route('orders.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-cart"></i>
                            <div>Mes commandes</div>
                        </a>
                    </li>
                    
                    <!-- Réservations -->
                    <li class="menu-item {{ request()->routeIs('reservations.*') ? 'active' : '' }}">
                        <a href="{{ route('reservations.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-calendar"></i>
                            <div>Mes réservations</div>
                        </a>
                    </li>
                    
                    <!-- Mes avis -->
                    <li class="menu-item {{ request()->routeIs('reviews.*') ? 'active' : '' }}">
                        <a href="{{ route('restaurants.index') }}?filter=reviewed" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-star"></i>
                            <div>Mes avis</div>
                        </a>
                    </li>
                    @endif

                    @if(\Illuminate\Support\Facades\Auth::user()->isRestaurateur())
                    <!-- Catégories (pour restaurateurs) -->
                    <li class="menu-item {{ request()->routeIs('restaurants.categories.*') ? 'active' : '' }}">
                        @if(\Illuminate\Support\Facades\Auth::user()->restaurants->count() > 0)
                            <a href="{{ route('restaurants.categories.index', \Illuminate\Support\Facades\Auth::user()->restaurants->first()->id) }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-category"></i>
                                <div>Catégories</div>
                            </a>
                        @else
                            <a href="{{ route('restaurants.create') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-category"></i>
                                <div>Catégories</div>
                            </a>
                        @endif
                    </li>

                    <!-- Menus (pour restaurateurs) -->
                    <li class="menu-item {{ request()->routeIs('restaurants.menus.*') ? 'active' : '' }}">
                        @if(\Illuminate\Support\Facades\Auth::user()->restaurants->count() > 0)
                            <a href="{{ route('restaurants.menus.index', \Illuminate\Support\Facades\Auth::user()->restaurants->first()->id) }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-food-menu"></i>
                                <div>Menus</div>
                            </a>
                        @else
                            <a href="{{ route('restaurants.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-food-menu"></i>
                                <div>Menus</div>
                            </a>
                        @endif
                    </li>

                    <!-- Items (pour restaurateurs) -->
                    <li class="menu-item {{ request()->routeIs('items.*') ? 'active' : '' }}">
                        <a href="{{ route('items.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-dish"></i>
                            <div>Plats</div>
                        </a>
                    </li>

                    <!-- Tables (pour restaurateurs) -->
                    <li class="menu-item {{ request()->routeIs('restaurants.tables.*') ? 'active' : '' }}">
                        @if(\Illuminate\Support\Facades\Auth::user()->restaurants->count() > 0)
                            <a href="{{ route('restaurants.tables.index', \Illuminate\Support\Facades\Auth::user()->restaurants->first()->id) }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-table"></i>
                                <div>Tables</div>
                            </a>
                        @else
                            <a href="{{ route('restaurants.create') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-table"></i>
                                <div>Tables</div>
                            </a>
                        @endif
                    </li>
                    
                    <!-- Réservations (pour restaurateurs) -->
                    <li class="menu-item {{ request()->routeIs('restaurant.reservations') ? 'active' : '' }}">
                        @if(\Illuminate\Support\Facades\Auth::user()->restaurants->count() > 0)
                            <a href="{{ route('restaurant.reservations', \Illuminate\Support\Facades\Auth::user()->restaurants->first()->id) }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                                <div>Réservations</div>
                            </a>
                        @else
                            <a href="{{ route('restaurants.create') }}" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                                <div>Réservations</div>
                            </a>
                        @endif
                    </li>
                    @endif
                    @endif
                </ul>
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- User -->
                            @if(\Illuminate\Support\Facades\Auth::check())
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar">
                                        <i class="bx bx-user fs-3"></i>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block">{{ \Illuminate\Support\Facades\Auth::user()->name }}</span>
                                                    <small class="text-muted">{{ \Illuminate\Support\Facades\Auth::user()->isRestaurateur() ? 'Restaurateur' : 'Client' }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="bx bx-user me-2"></i>
                                            <span class="align-middle">Mon profil</span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="bx bx-power-off me-2"></i>
                                                <span class="align-middle">Déconnexion</span>
                                            </a>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                            @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">
                                    <i class="bx bx-log-in me-2"></i>
                                    <span class="align-middle">Connexion</span>
                                </a>
                            </li>
                            @endif
                            <!--/ User -->
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

                        @yield('main')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                {{ date('Y') }}, Click'n Eat
                            </div>
                            <div>
                                <a href="{{ route('legal.notice') }}" class="footer-link me-4">Mentions légales</a>
                                <a href="{{ route('terms.of.service') }}" class="footer-link me-4">CGU</a>
                                <a href="{{ route('privacy.policy') }}" class="footer-link">Confidentialité</a>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->
    @yield('scripts')
</body>
</html>