@extends('layouts.app')

@section('page-title', 'Product Price Details')
@section('page-subtitle', 'Detailed view of product price record')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-eye mr-2"></i>
                    Product Price Details
                </h3>
                <div class="card-tools">
                    <a href="{{ route('product-prices.edit', $productPrice) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                    </a>
                    <a href="{{ route('product-prices.index') }}" class="btn btn-secondary btn-sm ml-1">
                        <i class="fas fa-list mr-1"></i>
                        Back to List
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text">Product Name</span>
                                <span class="info-box-number">{{ $productPrice->product_name }}</span>
                                @if($productPrice->product_category)
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: 100%">{{ $productPrice->product_category }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box {{ $productPrice->price > ($productPrice->original_price ?: $productPrice->price) ? 'bg-danger' : 'bg-success' }}">
                            <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Current Price</span>
                                <span class="info-box-number">Rp {{ number_format($productPrice->price, 0, ',', '.') }}</span>
                                @if($productPrice->original_price)
                                    <div class="text-muted">
                                        <small><s>Rp {{ number_format($productPrice->original_price, 0, ',', '.') }}</s></small>
                                        <span class="badge badge-warning ml-2">
                                            {{ round((($productPrice->original_price - $productPrice->price) / $productPrice->original_price) * 100, 1) }}% OFF
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Unit:</dt>
                            <dd class="col-sm-8">{{ $productPrice->unit }}</dd>

                            <dt class="col-sm-4">Source:</dt>
                            <dd class="col-sm-8">
                                <span class="badge badge-{{ $productPrice->source === 'manual' ? 'primary' : ($productPrice->source === 'scraped' ? 'success' : 'info') }}">
                                    {{ ucfirst($productPrice->source) }}
                                </span>
                            </dd>

                            <dt class="col-sm-4">Recorded At:</dt>
                            <dd class="col-sm-8">{{ $productPrice->recorded_at->format('d F Y, H:i') }}</dd>

                            <dt class="col-sm-4">Place:</dt>
                            <dd class="col-sm-8">
                                <a href="#" class="text-decoration-none">
                                    {{ $productPrice->place->name ?? 'N/A' }}
                                </a>
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Confidence Score:</dt>
                            <dd class="col-sm-7">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $productPrice->confidence_score * 100 }}%">
                                        {{ number_format($productPrice->confidence_score * 100, 1) }}%
                                    </div>
                                </div>
                            </dd>

                            @if($productPrice->supply_index !== null)
                            <dt class="col-sm-5">Supply Index:</dt>
                            <dd class="col-sm-7">{{ $productPrice->supply_index }}/100</dd>
                            @endif

                            @if($productPrice->demand_index !== null)
                            <dt class="col-sm-5">Demand Index:</dt>
                            <dd class="col-sm-7">{{ $productPrice->demand_index }}/100</dd>
                            @endif

                            @if($productPrice->season)
                            <dt class="col-sm-5">Season:</dt>
                            <dd class="col-sm-7">
                                <span class="badge badge-{{ $productPrice->season === 'peak' ? 'success' : ($productPrice->season === 'low' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($productPrice->season) }}
                                </span>
                            </dd>
                            @endif

                            <dt class="col-sm-5">Holiday Season:</dt>
                            <dd class="col-sm-7">
                                <i class="fas fa-{{ $productPrice->is_holiday_season ? 'check text-success' : 'times text-muted' }}"></i>
                                {{ $productPrice->is_holiday_season ? 'Yes' : 'No' }}
                            </dd>
                        </dl>
                    </div>
                </div>

                @if($productPrice->notes)
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label><i class="fas fa-sticky-note mr-1"></i> Notes:</label>
                            <div class="border p-3 bg-light rounded">
                                {{ $productPrice->notes }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <div class="card border-info">
                            <div class="card-header bg-info">
                                <h5 class="card-title text-white mb-0">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Metadata Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Database ID: {{ $productPrice->id }}</small><br>
                                        <small class="text-muted">Created: {{ $productPrice->created_at->format('d/m/Y H:i:s') }}</small><br>
                                        <small class="text-muted">Updated: {{ $productPrice->updated_at->format('d/m/Y H:i:s') }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        @if($productPrice->lat && $productPrice->lng)
                                        <small class="text-muted">Coordinates: {{ $productPrice->lat }}, {{ $productPrice->lng }}</small><br>
                                        @endif
                                        <small class="text-muted">Data Version: V1.0</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        @if($relatedPrices->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Price History for "{{ $productPrice->product_name }}"
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($relatedPrices as $relatedPrice)
                    <div class="list-group-item px-3 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center">
                                    <strong class="text-primary h5 mb-0">Rp {{ number_format($relatedPrice->price, 0, ',', '.') }}</strong>
                                    @if($relatedPrice->id === $productPrice->id)
                                        <span class="badge badge-primary ml-2">Current</span>
                                    @endif
                                </div>
                                <small class="text-muted d-block">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $relatedPrice->place->name ?? 'N/A' }}
                                </small>
                                <small class="text-muted d-block">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $relatedPrice->recorded_at->format('d F Y, H:i') }}
                                </small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $relatedPrice->source === 'manual' ? 'primary' : 'secondary' }} mb-1">
                                    {{ ucfirst($relatedPrice->source) }}
                                </span>
                                <br>
                                <a href="{{ route('product-prices.show', $relatedPrice) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('product-prices.index', ['product_name' => $productPrice->product_name]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-list mr-1"></i>
                    View All Prices for "{{ $productPrice->product_name }}"
                </a>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Quick Actions
                </h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('product-prices.edit', $productPrice) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit mr-2"></i>
                        Edit This Price
                    </a>

                    <a href="{{ route('product-prices.create', ['product_name' => $productPrice->product_name, 'place_id' => $productPrice->place_id]) }}" class="btn btn-success btn-block">
                        <i class="fas fa-plus mr-2"></i>
                        Add Similar Price
                    </a>

                    <a href="{{ route('market-analysis.price-predictions') }}" class="btn btn-info btn-block">
                        <i class="fas fa-brain mr-2"></i>
                        View AI Predictions
                    </a>
                </div>
            </div>
        </div>

        @if($productPrice->metadata)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-database mr-2"></i>
                    Additional Metadata
                </h3>
            </div>
            <div class="card-body">
                <pre class="bg-light p-2 rounded small">{{ json_encode($productPrice->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
$(document).ready(function() {
    // Show success/error messages
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error('{{ $error }}');
        @endforeach
    @endif
});
</script>
@endsection
