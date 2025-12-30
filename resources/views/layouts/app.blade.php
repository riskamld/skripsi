<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Mafaza Fortuna - AdminLTE')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Custom CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Toastr for notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">0 Notifications</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-info-circle mr-2"></i> No new notifications
                    </a>
                </div>
            </li>

            <!-- User Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">Admin User</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">Mafaza Fortuna</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <span class="text-white font-weight-bold">A</span>
                    </div>
                </div>
                <div class="info">
                    <a href="#" class="d-block">Admin User</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- Places -->
                    <li class="nav-item">
                        <a href="{{ route('places.index') }}" class="nav-link {{ request()->routeIs('places.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-map-marker-alt"></i>
                            <p>Places</p>
                        </a>
                    </li>

                    <!-- Scrape Logs -->
                    <li class="nav-item">
                        <a href="{{ route('scrape-logs.index') }}" class="nav-link {{ request()->routeIs('scrape-logs.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Scrape Logs</p>
                        </a>
                    </li>

                    <!-- API Tokens -->
                    <li class="nav-item">
                        <a href="{{ route('api-tokens.index') }}" class="nav-link {{ request()->routeIs('api-tokens.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-key"></i>
                            <p>API Tokens</p>
                        </a>
                    </li>

                    <!-- Product Prices -->
                    <li class="nav-item">
                        <a href="{{ url('/product-prices') }}" class="nav-link {{ request()->routeIs('product-prices.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-coins"></i>
                            <p>Product Prices</p>
                        </a>
                    </li>

                    <!-- Market Analysis -->
                    <li class="nav-item {{ request()->routeIs('market-analysis.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('market-analysis.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>
                                Market Analysis
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.index') }}" class="nav-link {{ request()->routeIs('market-analysis.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Overview</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.supply-demand') }}" class="nav-link {{ request()->routeIs('market-analysis.supply-demand') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Supply & Demand</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.category-insights') }}" class="nav-link {{ request()->routeIs('market-analysis.category-insights') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Category Insights</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.geographic') }}" class="nav-link {{ request()->routeIs('market-analysis.geographic') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Geographic Analysis</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('market-analysis.price-predictions') }}" class="nav-link {{ request()->routeIs('market-analysis.price-predictions') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Price Predictions</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Database Tools -->
                    <li class="nav-item">
                        <a href="{{ route('database.index') }}" class="nav-link {{ request()->routeIs('database.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>Database Tools</p>
                        </a>
                    </li>

                    <!-- System Section -->
                    <li class="nav-header">SYSTEM</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Settings</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-question-circle"></i>
                            <p>Help</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">@yield('page-title', 'Dashboard')</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <!-- Main Footer -->
    <footer class="main-footer">
        <strong>&copy; 2025 Mafaza Fortuna.</strong> All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>
</body>
</html>
