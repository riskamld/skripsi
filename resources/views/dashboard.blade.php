@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="bi bi-house-door me-2"></i>
            Dashboard Overview
        </h2>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-geo-alt-fill text-primary fs-1 mb-2"></i>
                <h3 class="text-primary">{{ number_format($stats['total_places']) }}</h3>
                <p class="text-muted mb-0">Total Places</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-journal-text text-success fs-1 mb-2"></i>
                <h3 class="text-success">{{ number_format($stats['total_scrape_logs']) }}</h3>
                <p class="text-muted mb-0">Scrape Logs</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-plus text-warning fs-1 mb-2"></i>
                <h3 class="text-warning">{{ number_format($stats['places_today']) }}</h3>
                <p class="text-muted mb-0">Places Today</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-star-fill text-info fs-1 mb-2"></i>
                <h3 class="text-info">{{ number_format($stats['avg_rating'], 1) }}</h3>
                <p class="text-muted mb-0">Average Rating</p>
            </div>
        </div>
    </div>
</div>

<!-- Top Categories -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    Top Categories
                </h5>
            </div>
            <div class="card-body">
                @if($stats['top_categories']->count() > 0)
                    @foreach($stats['top_categories'] as $category)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $category->category ?: 'Uncategorized' }}</span>
                            <span class="badge bg-primary">{{ $category->count }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ ($category->count / $stats['top_categories']->first()->count) * 100 }}%"></div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No categories available</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-activity me-2"></i>
                    Scrape Status Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="p-3">
                            <i class="bi bi-check-circle-fill text-success fs-2"></i>
                            <h4 class="text-success mt-2">{{ $stats['recent_logs']->where('status', 'success')->count() }}</h4>
                            <small class="text-muted">Success</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3">
                            <i class="bi bi-x-circle-fill text-danger fs-2"></i>
                            <h4 class="text-danger mt-2">{{ $stats['recent_logs']->where('status', 'failed')->count() }}</h4>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3">
                            <i class="bi bi-pause-circle-fill text-warning fs-2"></i>
                            <h4 class="text-warning mt-2">{{ $stats['recent_logs']->where('status', 'skipped')->count() }}</h4>
                            <small class="text-muted">Skipped</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Places & Logs -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Places
                </h5>
            </div>
            <div class="card-body">
                @if($stats['recent_places']->count() > 0)
                    @foreach($stats['recent_places'] as $place)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $place->name }}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $place->category ?: 'No category' }}
                                </small>
                                <div class="mt-1">
                                    @if($place->rating)
                                        <span class="badge bg-warning text-dark me-2">
                                            <i class="bi bi-star-fill me-1"></i>
                                            {{ number_format($place->rating, 1) }}
                                        </span>
                                    @endif
                                    @if($place->review_count)
                                        <small class="text-muted">{{ number_format($place->review_count) }} reviews</small>
                                    @endif
                                </div>
                            </div>
                            <small class="text-muted">{{ $place->created_at->diffForHumans() }}</small>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No places available</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-journal-text me-2"></i>
                    Recent Scrape Logs
                </h5>
            </div>
            <div class="card-body">
                @if($stats['recent_logs']->count() > 0)
                    @foreach($stats['recent_logs'] as $log)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="me-3">
                                @if($log->status === 'success')
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                @elseif($log->status === 'failed')
                                    <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                                @else
                                    <i class="bi bi-pause-circle-fill text-warning fs-5"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">
                                    {{ $log->place ? $log->place->name : 'Unknown Place' }}
                                </small>
                                <small class="text-muted">
                                    Status: <span class="badge bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($log->status) }}</span>
                                </small>
                            </div>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No scrape logs available</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
