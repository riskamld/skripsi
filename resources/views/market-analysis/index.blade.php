@extends('layouts.app')

@section('page-title', 'Market Analysis Overview')
@section('page-subtitle', 'AI-powered market intelligence and insights')

@section('content')
<!-- Market Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['total_places']) }}</h3>
                <p>Total Places</p>
            </div>
            <div class="icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <a href="{{ route('places.index') }}" class="small-box-footer">
                View Details <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['active_categories']) }}</h3>
                <p>Active Categories</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>
            <a href="#categories" class="small-box-footer">
                View Categories <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($stats['avg_rating'], 1) }}<sup style="font-size: 20px">★</sup></h3>
                <p>Average Rating</p>
            </div>
            <div class="icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="small-box-footer">
                &nbsp;
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($stats['places_with_high_rating']) }}</h3>
                <p>High-Rated Places (4.0+)</p>
            </div>
            <div class="icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="small-box-footer">
                &nbsp;
            </div>
        </div>
    </div>
</div>

<!-- Market Saturation Analysis -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Market Saturation Analysis
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box {{ $saturationData['level'] === 'high' ? 'bg-danger' : ($saturationData['level'] === 'medium' ? 'bg-warning' : 'bg-success') }}">
                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Market Status</span>
                                <span class="info-box-number">{{ ucfirst($saturationData['level']) }}</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $saturationData['level'] === 'high' ? '90' : ($saturationData['level'] === 'medium' ? '60' : '30') }}%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $saturationData['message'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-lightbulb"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">AI Insights</span>
                                <span class="info-box-number">Market Intelligence</span>
                                <p style="margin-top: 10px; font-size: 0.9em;">
                                    Our AI analyzes {{ number_format($stats['total_places']) }} places across {{ $stats['active_categories'] }} categories to provide market insights.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-robot mr-2"></i>
                    AI-Powered Features
                </h3>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="{{ route('market-analysis.supply-demand') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-balance-scale mr-2 text-primary"></i>
                        Supply & Demand Analysis
                    </a>
                    <a href="{{ route('market-analysis.category-insights') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar mr-2 text-success"></i>
                        Category Insights
                    </a>
                    <a href="{{ route('market-analysis.geographic') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marked-alt mr-2 text-warning"></i>
                        Geographic Analysis
                    </a>
                    <a href="{{ route('market-analysis.price-predictions') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-coins mr-2 text-info"></i>
                        Price Prediction AI
                    </a>
                    <div class="list-group-item">
                        <i class="fas fa-shopping-cart mr-2 text-danger"></i>
                        <strong>Coming Soon:</strong> Product Recommendations
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Market Trends Overview Chart -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area mr-2"></i>
                    Market Trends Overview
                </h3>
            </div>
            <div class="card-body">
                <canvas id="marketTrendsChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trophy mr-2"></i>
                    Quick Insights
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong class="text-success">🏆 Top Performer:</strong>
                    <br><small>{{ $topCategories[0]['category'] ?? 'N/A' }} ({{ $topCategories[0]['count'] ?? 0 }} places)</small>
                </div>

                <div class="mb-3">
                    <strong class="text-primary">📊 Market Health:</strong>
                    <br><small>{{ $saturationData['level'] }} saturation</small>
                    <div class="progress mt-1" style="height: 8px;">
                        <div class="progress-bar {{ $saturationData['level'] === 'high' ? 'bg-danger' : ($saturationData['level'] === 'medium' ? 'bg-warning' : 'bg-success') }}"
                             style="width: {{ $saturationData['level'] === 'high' ? '90' : ($saturationData['level'] === 'medium' ? '60' : '30') }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <strong class="text-info">🎯 Opportunity Areas:</strong>
                    @php
                        $lowCategories = array_filter($topCategories, fn($c) => $c['count'] < 10);
                    @endphp
                    <br><small>{{ count($lowCategories) }} categories with low competition</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Categories by Supply -->
<div class="row" id="categories">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-crown mr-2"></i>
                    Top Categories by Supply (AI Analysis)
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Places</th>
                            <th>Market Share</th>
                            <th>AI Assessment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCategories as $category)
                        <tr>
                            <td>
                                <strong>{{ $category['category'] ?: 'Uncategorized' }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $category['count'] }}</span>
                            </td>
                            <td>
                                <div class="progress" style="width: 100px;">
                                    <div class="progress-bar bg-success" style="width: {{ ($category['count'] / $stats['total_places']) * 100 }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format(($category['count'] / $stats['total_places']) * 100, 1) }}%</small>
                            </td>
                            <td>
                                @if($category['count'] > 50)
                                    <span class="badge badge-danger">High Competition</span>
                                @elseif($category['count'] > 20)
                                    <span class="badge badge-warning">Moderate Competition</span>
                                @else
                                    <span class="badge badge-success">Low Competition</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- AI Insights Summary -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-brain mr-2"></i>
                    AI Market Intelligence Summary
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-chart-line text-success"></i> Market Trends</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> {{ $stats['active_categories'] }} business categories identified</li>
                            <li><i class="fas fa-check text-success"></i> {{ number_format($stats['places_with_high_rating']) }} high-performing businesses</li>
                            <li><i class="fas fa-check text-success"></i> Average rating: {{ number_format($stats['avg_rating'], 1) }}/5.0</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-lightbulb text-warning"></i> AI Recommendations</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary"></i> Focus on {{ $saturationData['level'] }} saturation areas</li>
                            <li><i class="fas fa-arrow-right text-primary"></i> Monitor top {{ count($topCategories) }} categories</li>
                            <li><i class="fas fa-arrow-right text-primary"></i> Explore supply-demand analysis for opportunities</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded');
        return;
    }

    // Market Trends Overview Chart
    var ctx = document.getElementById('marketTrendsChart');
    if (!ctx) {
        console.error('Chart canvas not found');
        return;
    }
    ctx = ctx.getContext('2d');

    @php
        $top5Categories = array_slice($topCategories, 0, 5);
        $categories = array_column($top5Categories, 'category');
        $places = array_column($top5Categories, 'count');
        $ratings = array_map(function($cat) {
            return round($cat['avg_rating'] ?? 0, 1);
        }, $top5Categories);
    @endphp

    var marketTrendsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($categories),
            datasets: [{
                label: 'Number of Places',
                data: @json($places),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Average Rating',
                data: @json($ratings),
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                yAxisID: 'y1',
                type: 'line',
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Number of Places'
                    },
                    beginAtZero: true
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Average Rating'
                    },
                    min: 0,
                    max: 5,
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            if (context.datasetIndex === 1) {
                                return context.parsed.y + ' ★ stars';
                            }
                            return context.parsed.y + ' places';
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Top 5 Categories: Places vs Rating Performance'
                }
            }
        }
    });
});
</script>
