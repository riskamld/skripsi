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

        /* Responsive table with proper horizontal scrolling */
        .table-responsive {
            border-radius: 8px;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
        }

        .table {
            min-width: 100%;
            table-layout: auto; /* Let browser determine column widths */
            white-space: nowrap;
        }

        /* Set reasonable column widths to prevent overlapping */
        .table th:nth-child(1), .table td:nth-child(1) { /* Name */
            min-width: 200px;
            max-width: 300px;
            white-space: normal; /* Allow wrapping for names */
        }

        .table th:nth-child(2), .table td:nth-child(2) { /* Phone */
            min-width: 120px;
            width: 120px;
        }

        .table th:nth-child(3), .table td:nth-child(3) { /* Category */
            min-width: 100px;
            width: 100px;
        }

        .table th:nth-child(4), .table td:nth-child(4) { /* Images */
            min-width: 80px;
            width: 80px;
        }

        .table th:nth-child(5), .table td:nth-child(5) { /* Rating */
            min-width: 80px;
            width: 80px;
        }

        .table th:nth-child(6), .table td:nth-child(6) { /* Reviews */
            min-width: 100px;
            width: 100px;
        }

        .table th:nth-child(7), .table td:nth-child(7) { /* Navigate */
            min-width: 60px;
            width: 60px;
        }

        .table th:nth-child(8), .table td:nth-child(8) { /* Location */
            min-width: 200px;
            max-width: 300px;
            white-space: normal; /* Allow wrapping for addresses */
        }

        .table th:nth-child(9), .table td:nth-child(9) { /* Created */
            min-width: 100px;
            width: 100px;
        }

        .table th:nth-child(10), .table td:nth-child(10) { /* Actions */
            min-width: 120px;
            width: 120px;
        }

        /* Ensure total table width allows scrolling when needed */
        @media (min-width: 1200px) {
            .table {
                min-width: 1400px; /* Allow scrolling on very wide screens */
            }
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

        /* Enhanced responsive design for all devices */
        @media (max-width: 1200px) {
            .sidebar {
                width: 220px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            /* Mobile navbar */
            .navbar-toggler {
                display: block !important;
            }

            .navbar-brand {
                font-size: 1rem;
            }
        }

        @media (max-width: 768px) {
            /* Ultra compact mobile layout */
            .sidebar {
                width: 100%;
                position: fixed;
                top: 0;
                left: -100%;
                z-index: 1050;
                transition: left 0.3s ease;
            }

            .navbar {
                padding: 0.25rem 0.75rem;
                height: 50px;
            }

            .navbar-brand {
                font-size: 0.9rem;
            }

            .container-fluid.p-4 {
                padding: 0.5rem !important;
            }

            /* Mobile table adjustments */
            .table-responsive {
                font-size: 0.75rem;
                margin: 0 -0.5rem;
            }

            .table {
                min-width: 800px; /* Ensure horizontal scrolling works */
            }

            .table th, .table td {
                padding: 0.4rem 0.3rem;
                font-size: 0.7rem;
                white-space: nowrap;
                vertical-align: middle;
            }

            .table th {
                font-size: 0.65rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                position: sticky;
                top: 0;
                background: #f8f9fa;
                z-index: 10;
            }

            /* Compact buttons on mobile */
            .btn {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
                border-radius: 0.25rem;
            }

            .btn-group .btn {
                padding: 0.15rem 0.2rem;
                font-size: 0.65rem;
            }

            /* Compact pagination on mobile */
            .pagination .page-link {
                padding: 0.15rem 0.3rem;
                font-size: 0.7rem;
                margin: 0 1px;
            }

            /* Stack form elements vertically on mobile */
            .row.g-2 > div, .row.g-3 > div {
                margin-bottom: 0.75rem;
            }

            /* Make form controls more compact on mobile */
            .form-control, .form-select {
                font-size: 0.75rem;
                padding: 0.375rem 0.5rem;
                margin-bottom: 0.25rem;
            }

            /* Compact search forms on mobile */
            .card-body form .row {
                --bs-gutter-x: 0.5rem;
            }

            .card-body form .col-md-2,
            .card-body form .col-md-3,
            .card-body form .col-md-4 {
                margin-bottom: 0.5rem;
            }

            /* Better button layout on mobile */
            .d-flex.gap-1 {
                gap: 0.25rem !important;
            }

            .d-flex.gap-2 {
                gap: 0.375rem !important;
            }

            /* Compact cards on mobile */
            .card-body {
                padding: 0.5rem;
            }

            /* Smaller headings on mobile */
            h1, h2, h3, h4, h5, h6 {
                font-size: calc(1.1rem - 0.2rem);
                margin-bottom: 0.5rem;
            }

            h2 {
                font-size: 1rem;
            }

            /* Compact alerts */
            .alert {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            /* Mobile table scrollbar */
            .table-responsive::-webkit-scrollbar {
                height: 6px;
            }

            .table-responsive::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 3px;
            }

            .table-responsive::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 3px;
            }

            .table-responsive::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }
        }

        @media (max-width: 576px) {
            /* Extra small screens */
            .sidebar {
                width: 100%;
            }

            .navbar-brand {
                font-size: 0.8rem;
            }

            .table th, .table td {
                padding: 0.2rem 0.15rem;
                font-size: 0.65rem;
                max-width: 60px;
            }

            .btn {
                font-size: 0.65rem;
                padding: 0.15rem 0.3rem;
            }

            /* Hide more columns on very small screens */
            .table th:nth-child(4),
            .table td:nth-child(4),
            .table th:nth-child(5),
            .table td:nth-child(5) {
                display: none;
            }
        }

        /* Mobile menu toggle */
        .navbar-toggler {
            display: none;
            border: none;
            background: none;
            color: #6c757d;
            font-size: 1.2rem;
            padding: 0.25rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }

        .sidebar-overlay.show {
            display: block;
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
                        <button class="navbar-toggler me-3" type="button" onclick="toggleSidebar()">
                            <i class="bi bi-list"></i>
                        </button>
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

                <!-- Mobile sidebar overlay -->
                <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

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

    <script>
        // Mobile sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            if (sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            } else {
                sidebar.classList.add('show');
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        // Close sidebar when clicking on nav links (mobile)
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        toggleSidebar();
                    }
                });
            });

            // Close sidebar on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && document.querySelector('.sidebar.show')) {
                    toggleSidebar();
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
