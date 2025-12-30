@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome to your Mafaza Fortuna admin panel')

@section('content')
<!-- Info boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-map-marker-alt"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Total Places</span>
                <span class="info-box-number">
                    {{ $placesCount ?? 0 }}
                    <small>%</small>
                </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-history"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Scrape Logs</span>
                <span class="info-box-number">{{ $scrapeLogsCount ?? 0 }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->

    <!-- fix for small devices only -->
    <div class="clearfix hidden-md-up"></div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-key"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">API Tokens</span>
                <span class="info-box-number">{{ $apiTokensCount ?? 0 }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-cogs"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">System Status</span>
                <span class="info-box-number">Operational</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <div class="col-md-8">
        <!-- TABLE: LATEST ORDERS -->
        <div class="card">
            <div class="card-header border-transparent">
                <h3 class="card-title">Quick Actions</h3>

                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                        <tr>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><a href="{{ route('places.index') }}">Manage Places</a></td>
                            <td>View and manage all places in database</td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                        <tr>
                            <td><a href="{{ route('api-tokens.index') }}">API Tokens</a></td>
                            <td>Manage API access tokens</td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                        <tr>
                            <td><a href="{{ route('scrape-logs.index') }}">Scrape Logs</a></td>
                            <td>View scraping activity logs</td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                        <tr>
                            <td><a href="#">Settings</a></td>
                            <td>System configuration and preferences</td>
                            <td><span class="badge badge-warning">Coming Soon</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                <a href="{{ route('places.create') }}" class="btn btn-sm btn-info float-left">Add New Place</a>
                <a href="{{ route('scrape-logs.index') }}" class="btn btn-sm btn-secondary float-right">View All Logs</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->

    <div class="col-md-4">
        <!-- Info Boxes Style 2 -->
        <div class="info-box mb-3 bg-warning">
            <span class="info-box-icon"><i class="fas fa-tag"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Inventory</span>
                <span class="info-box-number">5,200</span>

                <div class="progress">
                    <div class="progress-bar" style="width: 50%"></div>
                </div>
                <span class="progress-description">
                    50% Increase in 30 Days
                </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
        <div class="info-box mb-3 bg-success">
            <span class="info-box-icon"><i class="far fa-heart"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Mentions</span>
                <span class="info-box-number">92,050</span>

                <div class="progress">
                    <div class="progress-bar" style="width: 20%"></div>
                </div>
                <span class="progress-description">
                    20% Increase in 30 Days
                </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
        <div class="info-box mb-3 bg-danger">
            <span class="info-box-icon"><i class="fas fa-cloud-download-alt"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Downloads</span>
                <span class="info-box-number">114,381</span>

                <div class="progress">
                    <div class="progress-bar" style="width: 70%"></div>
                </div>
                <span class="progress-description">
                    70% Increase in 30 Days
                </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
        <div class="info-box mb-3 bg-info">
            <span class="info-box-icon"><i class="far fa-comment"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Direct Messages</span>
                <span class="info-box-number">163,921</span>

                <div class="progress">
                    <div class="progress-bar" style="width: 40%"></div>
                </div>
                <span class="progress-description">
                    40% Increase in 30 Days
                </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->

        <!-- PRODUCT LIST -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recently Added Places</h3>

                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <a href="#" class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>La Bella Vista Restaurant</strong>
                                <small class="text-muted d-block">Added 2 minutes ago</small>
                            </div>
                            <span class="badge badge-success badge-pill">New</span>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="#" class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Central Park Cafe</strong>
                                <small class="text-muted d-block">Added 5 minutes ago</small>
                            </div>
                            <span class="badge badge-info badge-pill">Scraped</span>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="#" class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Mountain View Lodge</strong>
                                <small class="text-muted d-block">Added 10 minutes ago</small>
                            </div>
                            <span class="badge badge-warning badge-pill">Pending</span>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="#" class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Ocean Breeze Resort</strong>
                                <small class="text-muted d-block">Added 15 minutes ago</small>
                            </div>
                            <span class="badge badge-danger badge-pill">Error</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- /.card-body -->
            <div class="card-footer text-center">
                <a href="{{ route('places.index') }}" class="uppercase">View All Places</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
@endsection
