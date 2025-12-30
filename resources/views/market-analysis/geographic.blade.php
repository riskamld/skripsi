@extends('layouts.app')

@section('page-title', 'Geographic Analysis')
@section('page-subtitle', 'AI-powered location-based market insights')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marked-alt mr-2"></i>
                    Geographic Market Density Analysis
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        <i class="fas fa-robot mr-1"></i>
                        AI analyzes market concentration and competition by geographic areas
                    </small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Area Coordinates</th>
                                <th>Total Places</th>
                                <th>Avg Rating</th>
                                <th>Total Reviews</th>
                                <th>Density Score</th>
                                <th>AI Assessment</th>
                                <th>Market Potential</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locationData as $location)
                            <tr>
                                <td>
                                    <strong>{{ $location['coordinates'] }}</strong>
                                    <br>
                                    <small class="text-muted">Geographic cluster</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $location['place_count'] }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-star"></i> {{ $location['avg_rating'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ number_format($location['total_reviews']) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $location['density_score'] >= 80 ? 'badge-danger' : ($location['density_score'] >= 60 ? 'badge-warning' : 'badge-success') }} mr-2">
                                            {{ $location['density_score'] }}
                                        </span>
                                        <div class="progress flex-grow-1" style="width: 60px;">
                                            <div class="progress-bar {{ $location['density_score'] >= 80 ? 'bg-danger' : ($location['density_score'] >= 60 ? 'bg-warning' : 'bg-success') }}"
                                                 style="width: {{ $location['density_score'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($location['density_score'] >= 80)
                                        <div class="text-danger">
                                            <strong>🏙️ HIGH DENSITY</strong><br>
                                            <small>Heavy competition area</small>
                                        </div>
                                    @elseif($location['density_score'] >= 60)
                                        <div class="text-warning">
                                            <strong>🏘️ MODERATE DENSITY</strong><br>
                                            <small>Balanced competition</small>
                                        </div>
                                    @else
                                        <div class="text-success">
                                            <strong>🌾 LOW DENSITY</strong><br>
                                            <small>Opportunity area</small>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($location['density_score'] >= 80)
                                        <div class="text-danger">
                                            <strong>⚠️ HIGH RISK</strong><br>
                                            <small>Market oversaturated</small>
                                        </div>
                                    @elseif($location['density_score'] >= 60)
                                        <div class="text-warning">
                                            <strong>🎯 MODERATE</strong><br>
                                            <small>Strategic positioning needed</small>
                                        </div>
                                    @else
                                        <div class="text-success">
                                            <strong>🚀 HIGH POTENTIAL</strong><br>
                                            <small>Prime location opportunity</small>
                                        </div>
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
</div>

<!-- Geographic Insights -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title text-white">
                    <i class="fas fa-map-pin mr-2"></i>
                    Best Location Opportunities
                </h3>
            </div>
            <div class="card-body">
                @php
                    $bestLocations = array_filter($locationData, function($loc) {
                        return $loc['density_score'] < 60 && $loc['avg_rating'] >= 3.5;
                    });
                    $bestLocations = array_slice($bestLocations, 0, 5);
                @endphp

                @forelse($bestLocations as $location)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong>{{ $location['coordinates'] }}</strong>
                        <br>
                        <small class="text-muted">
                            Places: {{ $location['place_count'] }} |
                            Rating: {{ $location['avg_rating'] }} |
                            Density: {{ $location['density_score'] }}
                        </small>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-success">High Potential</span>
                    </div>
                </div>
                @if(!$loop->last)
                <hr class="my-2">
                @endif
                @empty
                <div class="text-center text-muted">
                    <i class="fas fa-search mr-2"></i>
                    No optimal locations found with current data
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="card-title text-white">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    High Competition Areas
                </h3>
            </div>
            <div class="card-body">
                @php
                    $competitionAreas = array_filter($locationData, function($loc) {
                        return $loc['density_score'] >= 80;
                    });
                    $competitionAreas = array_slice($competitionAreas, 0, 5);
                @endphp

                @forelse($competitionAreas as $location)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong>{{ $location['coordinates'] }}</strong>
                        <br>
                        <small class="text-muted">
                            Places: {{ $location['place_count'] }} |
                            Rating: {{ $location['avg_rating'] }} |
                            Density: {{ $location['density_score'] }}
                        </small>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-danger">High Risk</span>
                    </div>
                </div>
                @if(!$loop->last)
                <hr class="my-2">
                @endif
                @empty
                <div class="text-center text-muted">
                    <i class="fas fa-shield-alt mr-2"></i>
                    No high-competition areas detected
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Geographic Trends -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area mr-2"></i>
                    Geographic Market Trends
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon"><i class="fas fa-city text-primary"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Urban Areas</span>
                                <span class="info-box-number">
                                    @php
                                        $urbanAreas = count(array_filter($locationData, fn($loc) => $loc['density_score'] >= 70));
                                    @endphp
                                    {{ $urbanAreas }}
                                </span>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" style="width: {{ count($locationData) > 0 ? ($urbanAreas / count($locationData)) * 100 : 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    High-density business areas
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon"><i class="fas fa-tree text-success"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Rural Areas</span>
                                <span class="info-box-number">
                                    @php
                                        $ruralAreas = count(array_filter($locationData, fn($loc) => $loc['density_score'] < 50));
                                    @endphp
                                    {{ $ruralAreas }}
                                </span>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: {{ count($locationData) > 0 ? ($ruralAreas / count($locationData)) * 100 : 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    Low-density opportunity areas
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon"><i class="fas fa-balance-scale text-warning"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Balanced Areas</span>
                                <span class="info-box-number">
                                    @php
                                        $balancedAreas = count(array_filter($locationData, fn($loc) => $loc['density_score'] >= 50 && $loc['density_score'] < 70));
                                    @endphp
                                    {{ $balancedAreas }}
                                </span>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: {{ count($locationData) > 0 ? ($balancedAreas / count($locationData)) * 100 : 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    Moderate competition zones
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Geographic Analysis Methodology -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-brain mr-2"></i>
                    AI Geographic Analysis Methodology
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-calculator text-primary"></i> Density Score Calculation</h5>
                        <p class="text-muted">
                            Density Score = (Place Count × 50) + (Average Rating × 50)
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-building text-primary"></i> <strong>Place Count (50%):</strong> Number of businesses in area</li>
                            <li><i class="fas fa-star text-warning"></i> <strong>Rating Factor (50%):</strong> Quality of businesses</li>
                            <li><i class="fas fa-map-marker-alt text-danger"></i> <strong>Geographic Clustering:</strong> Businesses grouped by coordinates</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-lightbulb text-success"></i> AI Location Insights</h5>
                        <p class="text-muted">
                            Our AI analyzes geographic patterns to identify market opportunities.
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-success"></i> <strong>Low Density + High Rating:</strong> Prime investment areas</li>
                            <li><i class="fas fa-arrow-right text-warning"></i> <strong>Medium Density:</strong> Strategic positioning opportunities</li>
                            <li><i class="fas fa-arrow-right text-danger"></i> <strong>High Density:</strong> Saturated markets (high risk)</li>
                        </ul>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-trophy text-success"></i> Best Location Strategy</h5>
                        <ol>
                            <li><strong>Target low-density areas</strong> with good ratings</li>
                            <li><strong>Avoid high-density areas</strong> unless you have differentiation</li>
                            <li><strong>Consider medium-density areas</strong> for strategic positioning</li>
                            <li><strong>Analyze local competition</strong> before investing</li>
                        </ol>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-rocket text-primary"></i> AI-Powered Recommendations</h5>
                        <div class="alert alert-info">
                            <strong>💡 Pro Tip:</strong> The AI identifies geographic clusters where businesses thrive. Use this data to make informed location decisions for your next business venture.
                        </div>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Real-time market density analysis</li>
                            <li><i class="fas fa-check text-success"></i> Competitive landscape assessment</li>
                            <li><i class="fas fa-check text-success"></i> Location-based opportunity scoring</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
