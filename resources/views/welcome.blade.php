@extends('layouts.app')

@section('page-title', 'Welcome')
@section('page-subtitle', 'Mafaza Fortuna Admin Panel')

@section('content')
<!-- Welcome Hero -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h1 class="text-white mb-3">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Welcome to Mafaza Fortuna
                        </h1>
                        <p class="text-white-50 mb-4">
                            Manage your places database, monitor scraping activities, and control API access from this comprehensive admin panel.
                        </p>
                        <div class="d-flex flex-wrap">
                            <a href="{{ route('places.index') }}" class="btn btn-light mr-3 mb-2">
                                <i class="fas fa-plus mr-2"></i>Add New Place
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-light mb-2">
                                <i class="fas fa-chart-line mr-2"></i>View Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-map-marked-alt fa-5x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $placesCount ?? 0 }}</h3>
                <p>Total Places</p>
            </div>
            <div class="icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <a href="{{ route('places.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $scrapeLogsCount ?? 0 }}</h3>
                <p>Scrape Logs</p>
            </div>
            <div class="icon">
                <i class="fas fa-history"></i>
            </div>
            <a href="{{ route('scrape-logs.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $apiTokensCount ?? 0 }}</h3>
                <p>API Tokens</p>
            </div>
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <a href="{{ route('api-tokens.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>99.9<small>%</small></h3>
                <p>System Health</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="small-box-footer">&nbsp;</div>
        </div>
    </div>
</div>

<!-- Feature Overview -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-star mr-2"></i>
                    Key Features
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="description-block">
                            <span class="description-header text-primary">
                                <i class="fas fa-map-marker-alt"></i> Places DB
                            </span>
                            <span class="description-text">MANAGE PLACES</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="description-block">
                            <span class="description-header text-success">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </span>
                            <span class="description-text">DIRECT CONTACT</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="description-block">
                            <span class="description-header text-info">
                                <i class="fas fa-history"></i> Logs
                            </span>
                            <span class="description-text">ACTIVITY TRACKING</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="description-block">
                            <span class="description-header text-warning">
                                <i class="fas fa-key"></i> API
                            </span>
                            <span class="description-text">TOKEN MANAGEMENT</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Getting Started
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-rocket fa-4x text-primary"></i>
                </div>
                <h5 class="text-center mb-3">Ready to get started?</h5>
                <p class="text-muted text-center mb-4">
                    Begin by adding your first place or exploring existing locations in your database.
                </p>
                <div class="text-center">
                    <a href="{{ route('places.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus mr-2"></i>
                        Add Your First Place
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-2"></i>
                    Recent Activity
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline timeline-inverse">
                    <div class="time-label">
                        <span class="bg-success">Today</span>
                    </div>
                    <div>
                        <i class="fas fa-plus bg-primary"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 2 mins ago</span>
                            <h3 class="timeline-header">New place added</h3>
                            <div class="timeline-body">
                                <strong>La Bella Vista Restaurant</strong> was successfully scraped and added to the database.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-key bg-warning"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 1 hour ago</span>
                            <h3 class="timeline-header">API token regenerated</h3>
                            <div class="timeline-body">
                                Mobile app API token was refreshed for enhanced security.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-cog bg-info"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 3 hours ago</span>
                            <h3 class="timeline-header">System maintenance</h3>
                            <div class="timeline-body">
                                Scheduled maintenance completed successfully. All systems operational.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
