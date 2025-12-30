@extends('layouts.app')

@section('page-title', 'Supply & Demand Analysis')
@section('page-subtitle', 'AI-powered supply-demand ratio analysis')

@section('content')
<!-- Supply & Demand Analysis -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-balance-scale mr-2"></i>
                    Supply vs Demand Analysis by Category
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        AI calculates demand based on rating × reviews × market factors
                    </small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Business Category</th>
                                <th>Supply (Places)</th>
                                <th>Demand Score</th>
                                <th>S/D Ratio</th>
                                <th>Market Status</th>
                                <th>AI Opportunity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplyDemandData as $data)
                            <tr>
                                <td>
                                    <strong>{{ $data['category'] }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $data['supply'] }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">{{ $data['demand'] }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $data['ratio'] > 2.0 ? 'badge-success' : ($data['ratio'] > 1.5 ? 'badge-primary' : ($data['ratio'] > 0.8 ? 'badge-warning' : 'badge-danger')) }}">
                                        {{ $data['ratio'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($data['status'] === 'high_demand_low_supply')
                                        <span class="badge badge-success">
                                            <i class="fas fa-arrow-up"></i> High Opportunity
                                        </span>
                                    @elseif($data['status'] === 'balanced')
                                        <span class="badge badge-primary">
                                            <i class="fas fa-balance-scale"></i> Balanced
                                        </span>
                                    @elseif($data['status'] === 'moderate_supply')
                                        <span class="badge badge-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Moderate
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-arrow-down"></i> Oversupply
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($data['status'] === 'high_demand_low_supply')
                                        <div class="text-success">
                                            <strong>🎯 PRIME OPPORTUNITY</strong><br>
                                            <small>High demand, low competition</small>
                                        </div>
                                    @elseif($data['status'] === 'balanced')
                                        <div class="text-primary">
                                            <strong>⚖️ STABLE MARKET</strong><br>
                                            <small>Balanced supply & demand</small>
                                        </div>
                                    @elseif($data['status'] === 'moderate_supply')
                                        <div class="text-warning">
                                            <strong>⚠️ COMPETITIVE</strong><br>
                                            <small>Moderate competition level</small>
                                        </div>
                                    @else
                                        <div class="text-danger">
                                            <strong>🚫 HIGH RISK</strong><br>
                                            <small>Market oversaturated</small>
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

<!-- Market Insights -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lightbulb text-warning mr-2"></i>
                    Top Opportunities
                </h3>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @php
                        $opportunities = array_filter($supplyDemandData, function($item) {
                            return $item['status'] === 'high_demand_low_supply';
                        });
                        $opportunities = array_slice($opportunities, 0, 5);
                    @endphp

                    @forelse($opportunities as $opp)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $opp['category'] }}</strong>
                                <br>
                                <small class="text-muted">S/D Ratio: {{ $opp['ratio'] }}</small>
                            </div>
                            <span class="badge badge-success">High Potential</span>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted">
                        <i class="fas fa-search mr-2"></i>
                        No high-opportunity categories found
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                    High Competition Areas
                </h3>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @php
                        $risks = array_filter($supplyDemandData, function($item) {
                            return $item['status'] === 'oversupply';
                        });
                        $risks = array_slice($risks, 0, 5);
                    @endphp

                    @forelse($risks as $risk)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $risk['category'] }}</strong>
                                <br>
                                <small class="text-muted">S/D Ratio: {{ $risk['ratio'] }}</small>
                            </div>
                            <span class="badge badge-danger">High Risk</span>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted">
                        <i class="fas fa-shield-alt mr-2"></i>
                        No oversaturated categories found
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Methodology Explanation -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-brain mr-2"></i>
                    AI Analysis Methodology
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h5><i class="fas fa-calculator text-primary"></i> How Demand is Calculated</h5>
                        <p class="text-muted">
                            Demand Score = (Average Rating × Total Reviews × 10) + Location Factor + Category Popularity
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-star text-warning"></i> Rating weight: Higher rating = higher demand</li>
                            <li><i class="fas fa-comments text-info"></i> Reviews weight: More reviews = proven demand</li>
                            <li><i class="fas fa-map-marker-alt text-danger"></i> Location factor: Urban areas have higher multipliers</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5><i class="fas fa-balance-scale text-success"></i> Supply vs Demand Ratio</h5>
                        <p class="text-muted">
                            S/D Ratio = Demand Score ÷ Number of Places
                        </p>
                        <ul class="list-unstyled">
                            <li><strong class="text-success">> 2.0:</strong> High demand, low supply (Opportunity)</li>
                            <li><strong class="text-primary">1.5 - 2.0:</strong> Balanced market</li>
                            <li><strong class="text-warning">0.8 - 1.5:</strong> Moderate competition</li>
                            <li><strong class="text-danger">< 0.8:</strong> Oversupply (High risk)</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5><i class="fas fa-robot text-info"></i> AI-Powered Insights</h5>
                        <p class="text-muted">
                            Our statistical AI continuously analyzes market patterns to identify opportunities.
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-chart-line text-success"></i> Real-time market trend analysis</li>
                            <li><i class="fas fa-magic text-primary"></i> Pattern recognition algorithms</li>
                            <li><i class="fas fa-lightbulb text-warning"></i> Automated opportunity detection</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Investment Recommendations -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title text-white">
                    <i class="fas fa-coins mr-2"></i>
                    AI Investment Recommendations
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h5><i class="icon fas fa-info"></i> Top Investment Opportunities</h5>
                    <p>Based on our AI analysis, here are the most promising business opportunities:</p>
                </div>

                <div class="row">
                    @php
                        $topOpportunities = array_slice(array_filter($supplyDemandData, function($item) {
                            return $item['status'] === 'high_demand_low_supply';
                        }), 0, 3);
                    @endphp

                    @forelse($topOpportunities as $index => $opp)
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">#{{ $index + 1 }} Priority Investment</h5>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">{{ $opp['category'] }}</h6>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border p-2 rounded">
                                            <strong class="text-primary">{{ $opp['supply'] }}</strong><br>
                                            <small class="text-muted">Current Places</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border p-2 rounded">
                                            <strong class="text-success">{{ $opp['demand'] }}</strong><br>
                                            <small class="text-muted">Demand Score</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: {{ min($opp['ratio'] * 25, 100) }}%"></div>
                                    </div>
                                    <small class="text-muted">Investment Potential: {{ $opp['ratio'] }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-search mr-2"></i>
                            No high-priority investment opportunities detected. The market appears balanced.
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
