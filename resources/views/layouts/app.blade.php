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
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #5a67d8, #6b46c1);
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .badge {
            font-size: 0.75em;
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

    @stack('scripts')
</body>
</html>
