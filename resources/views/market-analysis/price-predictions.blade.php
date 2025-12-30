@extends('layouts.app')

@section('page-title', 'Price Predictions')
@section('page-subtitle', 'AI-powered price forecasting for market intelligence')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-coins mr-2"></i>
                    AI Price Predictions (100% FREE Statistical Analysis)
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        <i class="fas fa-brain mr-1"></i>
                        Powered by statistical algorithms: Linear Regression, Trend Analysis, Seasonal Adjustments
                    </small>
                </div>
            </div>
            <div class="card-body">
                @if(count($predictions) > 0)
                <div class="alert alert-success">
                    <h5><i class="icon fas fa-info"></i> AI Price Analysis Results</h5>
                    <p>Predictions are based on historical price data, market trends, seasonal factors, and supply/demand analysis.</p>
                </div>

                <div class="row">
                    @foreach($predictions as $prediction)
                    <div class="col-md-6 mb-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-line mr-2"></i>
                                    {{ $prediction['product_name'] }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <div class="border p-2 rounded">
                                            <strong class="text-primary h4">Rp {{ number_format($prediction['current_avg_price']) }}</strong><br>
                                            <small class="text-muted">Current Price</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border p-2 rounded">
                                            <strong class="text-{{ $prediction['predicted_price'] > $prediction['current_avg_price'] ? 'danger' : 'success' }} h4">
                                                Rp {{ number_format($prediction['predicted_price']) }}
                                            </strong><br>
                                            <small class="text-muted">Predicted (30 days)</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Price Change:</span>
                                        <span class="badge badge-{{ $prediction['price_change_percent'] > 0 ? 'danger' : 'success' }} h6">
                                            <i class="fas fa-arrow-{{ $prediction['price_change_percent'] > 0 ? 'up' : 'down' }} mr-1"></i>
                                            {{ abs($prediction['price_change_percent']) }}%
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Trend:</span>
                                        <span class="badge badge-{{ $prediction['trend'] === 'up' ? 'danger' : ($prediction['trend'] === 'down' ? 'success' : 'secondary') }}">
                                            <i class="fas fa-arrow-{{ $prediction['trend'] === 'up' ? 'up' : ($prediction['trend'] === 'down' ? 'down' : 'right') }} mr-1"></i>
                                            {{ ucfirst($prediction['trend']) }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Confidence:</span>
                                        <span class="badge badge-{{ $prediction['confidence'] >= 80 ? 'success' : ($prediction['confidence'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ $prediction['confidence'] }}%
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Price Range (95% confidence):</small>
                                    <div class="d-flex justify-content-between">
                                        <small>Rp {{ number_format($prediction['price_range']['lower']) }}</small>
                                        <small>Rp {{ number_format($prediction['price_range']['upper']) }}</small>
                                    </div>
                                    <div class="progress mt-1" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: 100%"></div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <small class="text-muted">
                                        <i class="fas fa-database mr-1"></i>
                                        {{ $prediction['data_points'] }} data points |
                                        Updated {{ $prediction['last_updated'] }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-coins fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No Price Predictions Available</h4>
                    <p class="text-muted mb-4">
                        Price predictions require historical price data. Start by adding product prices to enable AI forecasting.
                    </p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPriceModal">
                        <i class="fas fa-plus mr-2"></i>
                        Add Product Price
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Price Trend Charts -->
@if(count($predictions) > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Price Trend Analysis
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        Historical price movements for products with available data
                    </small>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($predictions as $index => $prediction)
                        @if($index < 2) <!-- Show only first 2 products for demo -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-line mr-2"></i>
                                        {{ $prediction['product_name'] }} - Price History
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="priceChart{{ $index }}" style="max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                @if(count($predictions) > 2)
                <div class="text-center">
                    <small class="text-muted">Showing trends for top 2 products. Add more historical data to see trends for all products.</small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- AI Methodology Explanation -->
@if(count($predictions) > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>
                    Free AI Price Prediction Methodology
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-calculator text-primary"></i> Statistical Algorithms Used:</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-chart-line text-success"></i> <strong>Linear Regression:</strong> Trend analysis from historical data</li>
                            <li><i class="fas fa-wave-square text-info"></i> <strong>Moving Averages:</strong> Smooth price fluctuations</li>
                            <li><i class="fas fa-calendar-alt text-warning"></i> <strong>Seasonal Adjustments:</strong> Holiday and seasonal effects</li>
                            <li><i class="fas fa-balance-scale text-danger"></i> <strong>Supply/Demand Factors:</strong> Market equilibrium analysis</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-lightbulb text-success"></i> How Predictions Work:</h5>
                        <div class="timeline timeline-inverse">
                            <div class="time-label">
                                <span class="bg-success">Step 1</span>
                            </div>
                            <div>
                                <i class="fas fa-history bg-blue"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Collect Historical Data</h3>
                                    <div class="timeline-body">
                                        Gather 90 days of price history for accurate trend analysis
                                    </div>
                                </div>
                            </div>
                            <div class="time-label">
                                <span class="bg-info">Step 2</span>
                            </div>
                            <div>
                                <i class="fas fa-chart-line bg-green"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Calculate Trends</h3>
                                    <div class="timeline-body">
                                        Use linear regression to identify price movement patterns
                                    </div>
                                </div>
                            </div>
                            <div class="time-label">
                                <span class="bg-warning">Step 3</span>
                            </div>
                            <div>
                                <i class="fas fa-adjust bg-yellow"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Apply Adjustments</h3>
                                    <div class="timeline-body">
                                        Factor in seasonal effects and market supply/demand
                                    </div>
                                </div>
                            </div>
                            <div class="time-label">
                                <span class="bg-danger">Step 4</span>
                            </div>
                            <div>
                                <i class="fas fa-bullseye bg-red"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">Generate Prediction</h3>
                                    <div class="timeline-body">
                                        Produce 30-day price forecast with confidence intervals
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-question-circle text-info"></i> Understanding Confidence Levels:</h5>
                        <ul class="list-unstyled">
                            <li><strong class="text-success">80-95%:</strong> High confidence - Very reliable predictions</li>
                            <li><strong class="text-warning">60-79%:</strong> Medium confidence - Good for planning</li>
                            <li><strong class="text-danger">0-59%:</strong> Low confidence - Use with caution</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-exclamation-triangle text-warning"></i> Important Notes:</h5>
                        <div class="alert alert-warning">
                            <ul class="mb-0">
                                <li>Predictions are statistical estimates, not guarantees</li>
                                <li>External factors (weather, economy) may affect actual prices</li>
                                <li>More historical data = better prediction accuracy</li>
                                <li>Regular price updates improve AI learning</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Add Price Modal -->
<div class="modal fade" id="addPriceModal" tabindex="-1" role="dialog" aria-labelledby="addPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPriceModalLabel">
                    <i class="fas fa-plus mr-2"></i>
                    Add Product Price
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addPriceForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                        <small class="form-text text-muted">e.g., Mangga, Beras Premium, Ayam</small>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (Rp)</label>
                        <input type="number" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="place_id">Place</label>
                        <select class="form-control" id="place_id" name="place_id" required>
                            <option value="">Select Place</option>
                            @foreach($places as $place)
                            <option value="{{ $place->id }}">{{ $place->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Save Price
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery and Chart.js are loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery not loaded');
        return;
    }

    // Handle form submission
    $('#addPriceForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            product_name: $('#product_name').val(),
            price: $('#price').val(),
            place_id: $('#place_id').val(),
            source: 'manual',
            recorded_at: new Date().toISOString()
        };

        $.ajax({
            url: '/api/product-prices',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-API-TOKEN': '101d829f3ad04bf3ecb28e302edf886b48eea085c27efe8d1c0eb0c352af7d1b' // Default token
            },
            success: function(response) {
                $('#addPriceModal').modal('hide');
                $('#addPriceForm')[0].reset();

                // Show success message
                toastr.success('Product price added successfully!');

                // Reload the page to show new predictions
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Failed to add price';
                toastr.error(error);
            }
        });
    });

    // Price Trend Charts
    @if(count($predictions) > 0)
        @foreach($predictions as $index => $prediction)
            @if($index < 2 && isset($chartData[$prediction['product_name']]))
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded for price charts');
            } else {
                var canvas{{ $index }} = document.getElementById('priceChart{{ $index }}');
                if (canvas{{ $index }}) {
                    var ctxPrice{{ $index }} = canvas{{ $index }}.getContext('2d');
                    var priceChart{{ $index }} = new Chart(ctxPrice{{ $index }}, {
                type: 'line',
                data: {
                    labels: @json($chartData[$prediction['product_name']]['labels']),
                    datasets: [{
                        label: 'Historical Price (Rp)',
                        data: @json($chartData[$prediction['product_name']]['prices']),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxTicksLimit: 10
                            }
                        }
                    }
                }
            });
                } else {
                    console.error('Canvas element not found for price chart {{ $index }}');
                }
            }
            @endif
        @endforeach
    @endif
});
</script>
@endsection
