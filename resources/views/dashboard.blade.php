@extends('layouts.app')

@section('page-title', 'Dasbor')
@section('page-subtitle', 'Selamat datang di panel admin Mafaza Fortuna')

@section('content')
<!-- Info boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-map-marker-alt"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Total Tempat</span>
                <span class="info-box-number">
                    {{ $stats['total_places'] ?? 0 }}
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
                <span class="info-box-text">Log Scraping</span>
                <span class="info-box-number">{{ $stats['total_scrape_logs'] ?? 0 }}</span>
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
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-star"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Rating Rata-rata</span>
                <span class="info-box-number">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-calendar-day"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Rating Rata-rata</span>
                <span class="info-box-number">{{ $stats['places_today'] ?? 0 }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

<!-- Charts Row -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Kategori Teratas
                </h3>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Aktivitas Mingguan
                </h3>
            </div>
            <div class="card-body">
                <canvas id="activityChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
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
                    Aktivitas Terbaru
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped m-0">
                        <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Aksi</th>
                            <th>Tempat</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['recent_logs'] ?? [] as $log)
                            <tr>
                                <td>{{ $log->created_at->diffForHumans() }}</td>
                                <td>{{ ucfirst($log->action ?? 'Scraped') }}</td>
                                <td>{{ $log->place->name ?? 'Unknown' }}</td>
                                <td>
                                    <span class="badge badge-{{ $log->status === 'success' ? 'success' : ($log->status === 'error' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($log->status ?? 'Unknown') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('scrape-logs.index') }}" class="btn btn-sm btn-primary">View All Logs</a>
            </div>
        </div>
    </div>
</div>

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <div class="col-md-8">
        <!-- TABLE: LATEST ORDERS -->
        <div class="card">
            <div class="card-header border-transparent">
                <h3 class="card-title">Fitur Utama</h3>

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
                            <th>Fitur</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><a href="{{ route('places.index') }}">Tempat</a></td>
                            <td>Kelola database tempat</td>
                            <td><span class="badge badge-success">Aktif</span></td>
                        </tr>
                        <tr>
                            <td><a href="{{ route('api-tokens.index') }}">Token API</a></td>
                            <td>Kelola akses API</td>
                            <td><span class="badge badge-success">Aktif</span></td>
                        </tr>
                        <tr>
                            <td><a href="{{ route('scrape-logs.index') }}">Log Scraping</a></td>
                            <td>Pantau aktivitas scraping</td>
                            <td><span class="badge badge-success">Aktif</span></td>
                        </tr>
                        <tr>
                            <td><a href="/extension-chrome-mafaza.zip" download>Ekstensi Chrome</a></td>
                            <td>Download ekstensi scraper</td>
                            <td><span class="badge badge-success">Tersedia</span></td>
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
</div>
<!-- /.row -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(function () {
    // Category Pie Chart
    var categoryData = @json($stats['top_categories'] ?? []);
    var categoryLabels = categoryData.map(function(item) { return item.category || 'Unknown'; });
    var categoryCounts = categoryData.map(function(item) { return item.count; });

    var categoryChartCanvas = $('#categoryChart').get(0).getContext('2d');
    var categoryChart = new Chart(categoryChartCanvas, {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryCounts,
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6f42c1'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Activity Line Chart (mock data for demonstration)
    var activityLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    var activityData = [12, 19, 3, 5, 2, 3, 9]; // Mock data

    var activityChartCanvas = $('#activityChart').get(0).getContext('2d');
    var activityChart = new Chart(activityChartCanvas, {
        type: 'line',
        data: {
            labels: activityLabels,
            datasets: [{
                label: 'Places Added',
                data: activityData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection
