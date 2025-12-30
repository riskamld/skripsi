@extends('layouts.app')

@section('page-title', 'Category Insights')
@section('page-subtitle', 'AI-powered category performance analysis')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Category Performance Analysis
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        <i class="fas fa-robot mr-1"></i>
                        AI analyzes category performance based on rating, volume, and consistency
                    </small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Total Places</th>
                                <th>Avg Rating</th>
                                <th>Total Reviews</th>
                                <th>Performance Score</th>
                                <th>AI Assessment</th>
                                <th>Recommended Products</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryInsights as $category)
                            <tr>
                                <td>
                                    <strong>{{ $category['category'] }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $category['total_places'] }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-star"></i> {{ $category['avg_rating'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ number_format($category['total_reviews']) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $category['performance_score'] >= 80 ? 'badge-success' : ($category['performance_score'] >= 60 ? 'badge-warning' : 'badge-danger') }} mr-2">
                                            {{ $category['performance_score'] }}
                                        </span>
                                        <div class="progress flex-grow-1" style="width: 60px;">
                                            <div class="progress-bar {{ $category['performance_score'] >= 80 ? 'bg-success' : ($category['performance_score'] >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                 style="width: {{ $category['performance_score'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($category['performance_score'] >= 80)
                                        <div class="text-success">
                                            <strong>🏆 EXCELLENT</strong><br>
                                            <small>High performance category</small>
                                        </div>
                                    @elseif($category['performance_score'] >= 60)
                                        <div class="text-warning">
                                            <strong>👍 GOOD</strong><br>
                                            <small>Solid performance</small>
                                        </div>
                                    @else
                                        <div class="text-danger">
                                            <strong>⚠️ NEEDS IMPROVEMENT</strong><br>
                                            <small>Low performance category</small>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        @foreach(array_slice($category['recommended_products'], 0, 3) as $product)
                                        <button type="button" class="btn btn-outline-secondary btn-sm" style="font-size: 0.75rem; padding: 2px 6px;">
                                            {{ Str::limit($product, 15) }}
                                        </button>
                                        @endforeach
                                        @if(count($category['recommended_products']) > 3)
                                        <button type="button" class="btn btn-outline-info btn-sm" style="font-size: 0.7rem; padding: 1px 4px;">
                                            +{{ count($category['recommended_products']) - 3 }} more
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Performance Chart -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Category Performance Comparison
                </h3>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="categoryChart" style="min-height: 400px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Performing Categories -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title text-white">
                    <i class="fas fa-trophy mr-2"></i>
                    Top Performing Categories
                </h3>
            </div>
            <div class="card-body">
                @php
                    $topCategories = array_slice($categoryInsights, 0, 5);
                @endphp

                @foreach($topCategories as $index => $category)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong>#{{ $index + 1 }} {{ $category['category'] }}</strong>
                        <br>
                        <small class="text-muted">
                            Score: {{ $category['performance_score'] }} |
                            Places: {{ $category['total_places'] }} |
                            Rating: {{ $category['avg_rating'] }}
                        </small>
                    </div>
                    <div class="text-right">
                        <div class="badge badge-success">{{ $category['performance_score'] }}</div>
                    </div>
                </div>
                @if(!$loop->last)
                <hr class="my-2">
                @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title text-white">
                    <i class="fas fa-chart-line mr-2"></i>
                    Category Trends
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Rating Distribution</strong>
                    <div class="mt-2">
                        @php
                            $ratingGroups = [
                                '5.0' => count(array_filter($categoryInsights, fn($c) => $c['avg_rating'] >= 4.5)),
                                '4.0-4.4' => count(array_filter($categoryInsights, fn($c) => $c['avg_rating'] >= 4.0 && $c['avg_rating'] < 4.5)),
                                '3.0-3.9' => count(array_filter($categoryInsights, fn($c) => $c['avg_rating'] >= 3.0 && $c['avg_rating'] < 4.0)),
                                '< 3.0' => count(array_filter($categoryInsights, fn($c) => $c['avg_rating'] < 3.0))
                            ];
                        @endphp

                        @foreach($ratingGroups as $range => $count)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small>{{ $range }} stars</small>
                            <div class="d-flex align-items-center">
                                <div class="progress mr-2" style="width: 60px;">
                                    <div class="progress-bar bg-info" style="width: {{ count($categoryInsights) > 0 ? ($count / count($categoryInsights)) * 100 : 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ $count }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <hr>

                <div>
                    <strong>Size Distribution</strong>
                    <div class="mt-2">
                        @php
                            $sizeGroups = [
                                'Large (50+)' => count(array_filter($categoryInsights, fn($c) => $c['total_places'] >= 50)),
                                'Medium (20-49)' => count(array_filter($categoryInsights, fn($c) => $c['total_places'] >= 20 && $c['total_places'] < 50)),
                                'Small (5-19)' => count(array_filter($categoryInsights, fn($c) => $c['total_places'] >= 5 && $c['total_places'] < 20)),
                                'Micro (<5)' => count(array_filter($categoryInsights, fn($c) => $c['total_places'] < 5))
                            ];
                        @endphp

                        @foreach($sizeGroups as $size => $count)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small>{{ $size }}</small>
                            <span class="badge badge-secondary">{{ $count }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Performance Score Methodology -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-brain mr-2"></i>
                    AI Performance Score Calculation
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h5><i class="fas fa-star text-warning"></i> Rating Score (40%)</h5>
                        <p class="text-muted">
                            Score = (Average Rating / 5.0) × 40
                        </p>
                        <small class="text-muted">
                            Higher ratings indicate better customer satisfaction and demand.
                        </small>
                    </div>
                    <div class="col-md-4">
                        <h5><i class="fas fa-comments text-info"></i> Volume Score (30%)</h5>
                        <p class="text-muted">
                            Score = min((Total Reviews / 1000) × 30, 30)
                        </p>
                        <small class="text-muted">
                            More reviews indicate proven market demand and popularity.
                        </small>
                    </div>
                    <div class="col-md-4">
                        <h5><i class="fas fa-balance-scale text-success"></i> Consistency Score (30%)</h5>
                        <p class="text-muted">
                            Score = ((5.0 - Rating Range) / 5.0) × 30
                        </p>
                        <small class="text-muted">
                            Consistent ratings across places indicate stable category performance.
                        </small>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-trophy text-success"></i> Performance Score Ranges</h5>
                        <ul class="list-unstyled">
                            <li><strong class="text-success">80-100:</strong> Excellent performance category</li>
                            <li><strong class="text-warning">60-79:</strong> Good performance with room for improvement</li>
                            <li><strong class="text-danger">0-59:</strong> Needs significant improvement</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-lightbulb text-primary"></i> AI Business Insights</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-success"></i> High-performing categories are market leaders</li>
                            <li><i class="fas fa-arrow-right text-warning"></i> Medium performers have growth potential</li>
                            <li><i class="fas fa-arrow-right text-info"></i> Study top performers to identify success patterns</li>
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
    // Ensure jQuery and Chart.js are loaded
    if (typeof $ === 'undefined' || typeof Chart === 'undefined') {
        console.error('jQuery or Chart.js not loaded');
        return;
    }

    // Category Performance Chart
    var ctx = document.getElementById('categoryChart');
    if (!ctx) {
        console.error('Chart canvas not found');
        return;
    }
    ctx = ctx.getContext('2d');

    @php
        $chartData = array_slice($categoryInsights, 0, 10); // Top 10 categories for chart
        $categories = array_column($chartData, 'category');
        $scores = array_column($chartData, 'performance_score');
        $places = array_column($chartData, 'total_places');
        $ratings = array_column($chartData, 'avg_rating');
    @endphp

    var categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($categories),
            datasets: [{
                label: 'Performance Score',
                data: @json($scores),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Total Places',
                data: @json($places),
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                yAxisID: 'y1'
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
                        text: 'Performance Score'
                    },
                    max: 100,
                    min: 0
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Total Places'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            var dataIndex = context.dataIndex;
                            var rating = @json($ratings)[dataIndex];
                            return 'Avg Rating: ' + rating + ' ⭐';
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Category Performance Overview'
                }
            }
        }
    });
});
</script>
