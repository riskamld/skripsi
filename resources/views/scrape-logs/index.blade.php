@extends('layouts.app')

@section('page-title', 'Scrape Logs')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2>
            <i class="bi bi-journal-text me-2"></i>
            Scrape Logs
        </h2>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-check-circle-fill text-success fs-1 mb-2"></i>
                <h3 class="text-success">{{ number_format($statusCounts['success']) }}</h3>
                <p class="text-muted mb-0">Success</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-x-circle-fill text-danger fs-1 mb-2"></i>
                <h3 class="text-danger">{{ number_format($statusCounts['failed']) }}</h3>
                <p class="text-muted mb-0">Failed</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-pause-circle-fill text-warning fs-1 mb-2"></i>
                <h3 class="text-warning">{{ number_format($statusCounts['skipped']) }}</h3>
                <p class="text-muted mb-0">Skipped</p>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>Skipped</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from"
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to"
                       value="{{ request('date_to') }}">
            </div>

            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Place name or error...">
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-1"></i>
                    Filter
                </button>
                <a href="{{ route('scrape-logs.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>
                    Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th>Place</th>
                        <th>Message</th>
                        <th>Timestamp</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                @if($log->status === 'success')
                                    <i class="bi bi-check-circle-fill text-success fs-5" title="Success"></i>
                                @elseif($log->status === 'failed')
                                    <i class="bi bi-x-circle-fill text-danger fs-5" title="Failed"></i>
                                @else
                                    <i class="bi bi-pause-circle-fill text-warning fs-5" title="Skipped"></i>
                                @endif
                            </td>
                            <td>
                                @if($log->place)
                                    <div>
                                        <strong>{{ $log->place->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $log->place->place_id }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">Unknown Place</span>
                                @endif
                            </td>
                            <td>
                                @if($log->error_message)
                                    <span class="text-danger">{{ Str::limit($log->error_message, 50) }}</span>
                                @else
                                    <span class="text-success">Place saved successfully</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                                <br>
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <a href="{{ route('scrape-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-journal-x text-muted fs-1 mb-3"></i>
                                <h5 class="text-muted">No scrape logs found</h5>
                                <p class="text-muted">Try adjusting your filters or check if scraping has been performed.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($logs->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $logs->appends(request()->query())->links() }}
    </div>
@endif
@endsection
