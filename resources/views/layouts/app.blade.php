<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mafaza Fortuna - Place Scraper')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Ultra compact sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 220px;
        }

        .sidebar .d-flex.flex-column.p-3 {
            padding: 1rem 0.75rem !important;
        }

        .sidebar h5 {
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s ease;
            font-size: 0.8rem;
            padding: 0.4rem 0.75rem;
            margin-bottom: 0.1rem;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .sidebar small {
            font-size: 0.7rem;
        }

        /* Compact main content */
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }

        /* Compact navbar */
        .navbar {
            padding: 0.5rem 1rem;
            height: 50px;
        }

        .navbar-brand {
            font-size: 1.1rem;
        }

        /* Compact container */
        .container-fluid.p-4 {
            padding: 1rem !important;
        }

        /* Ultra compact cards */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.08);
        }

        /* Compact buttons */
        .btn {
            font-size: 0.8rem;
            padding: 0.3rem 0.75rem;
            border-radius: 0.375rem;
        }

        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Compact table */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .badge {
            font-size: 0.7rem;
        }

        /* Compact pagination (global) */
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            margin: 0 1px;
        }

        /* Smaller headings */
        h1, h2, h3, h4, h5, h6 {
            font-size: calc(1.375rem - 0.1rem);
            margin-bottom: 0.75rem;
        }

        h2 {
            font-size: 1.15rem;
        }

        /* Compact spacing */
        .mb-4 {
            margin-bottom: 1rem !important;
        }

        .mb-3 {
            margin-bottom: 0.75rem !important;
        }

        /* Ultra compact global styles for all pages */
        .table th, .table td {
            padding: 0.25rem 0.375rem;
            vertical-align: middle;
            font-size: 0.8rem;
            line-height: 1.2;
        }

        .table th {
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.375rem 0.375rem;
        }

        .badge {
            font-size: 0.65rem !important;
            padding: 0.2rem 0.375rem;
            line-height: 1;
        }

        .btn-group .btn, .btn {
            font-size: 0.8rem;
            padding: 0.3rem 0.75rem;
            border-radius: 0.375rem;
        }

        .btn-sm {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
        }

        .form-control, .form-select {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .form-label {
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .pagination .page-link {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
            margin: 0 1px;
        }

        .card-body {
            padding: 0.75rem;
        }

        .table-responsive {
            border-radius: 8px;
        }

        .mb-4 {
            margin-bottom: 1rem !important;
        }

        .mb-3 {
            margin-bottom: 0.75rem !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .navbar {
                padding: 0.25rem 0.5rem;
                height: 45px;
            }

            .container-fluid.p-4 {
                padding: 0.5rem !important;
            }

            .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }

            .table th, .table td {
                padding: 0.2rem;
                font-size: 0.75rem;
            }

            .table th {
                font-size: 0.7rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <h5 class="mb-4">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        Mafaza Fortuna
                    </h5>

                    <nav class="nav nav-pills flex-column">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-house-door me-2"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('places.index') }}" class="nav-link {{ request()->routeIs('places.*') ? 'active' : '' }}">
                            <i class="bi bi-geo-alt me-2"></i>
                            Places
                        </a>
                        <a href="{{ route('scrape-logs.index') }}" class="nav-link {{ request()->routeIs('scrape-logs.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-text me-2"></i>
                            Scrape Logs
                        </a>
                        <a href="{{ route('api-tokens.index') }}" class="nav-link {{ request()->routeIs('api-tokens.*') ? 'active' : '' }}">
                            <i class="bi bi-key me-2"></i>
                            API Tokens
                        </a>
                    </nav>

                    <hr class="my-4">

                    <div class="mt-auto">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Place Scraper v1.0
                        </small>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-0 main-content">
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">@yield('page-title', 'Dashboard')</span>

                        <div class="d-flex">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show mb-0 me-3" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                        </div>
                    </div>
                </nav>

                <div class="container-fluid p-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery for AJAX functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('scripts')
</body>
</html>
