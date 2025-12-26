@extends('layouts.app')

@section('page-title', 'Scrape Log Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">
                    <i class="bi bi-journal-text me-2"></i>
                    Scrape Log Details
                </h2>
                <p class="text-muted mt-1">ID: {{ $scrapeLog->id }}</p>
            </div>
            <a href="{{ route('scrape-logs.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Logs
            </a>
        </div>
    </div>
</div>

<!-- Status Card -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Log Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <div>
                                @if($scrapeLog->status === 'success')
                                    <span class="badge bg-success fs-6">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Success
                                    </span>
                                @elseif($scrapeLog->status === 'failed')
                                    <span class="badge bg-danger fs-6">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Failed
                                    </span>
                                @else
                                    <span class="badge bg-warning fs-6">
                                        <i class="bi bi-pause-circle me-1"></i>
                                        {{ ucfirst($scrapeLog->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Place</label>
                            <div>
                                @if($scrapeLog->place)
                                    <a href="{{ route('places.show', $scrapeLog->place) }}" class="text-decoration-none">
                                        {{ $scrapeLog->place->name ?: 'Unnamed Place' }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $scrapeLog->place->address }}</small>
                                @else
                                    <span class="text-muted">Place data not available</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Place ID</label>
                            <div>
                                <code>{{ $scrapeLog->place_id }}</code>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created At</label>
                            <div>
                                {{ $scrapeLog->created_at->format('M d, Y H:i:s') }}
                                <br>
                                <small class="text-muted">{{ $scrapeLog->created_at->diffForHumans() }}</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Updated At</label>
                            <div>
                                {{ $scrapeLog->updated_at->format('M d, Y H:i:s') }}
                                <br>
                                <small class="text-muted">{{ $scrapeLog->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if($scrapeLog->error_message)
                <div class="mb-3">
                    <label class="form-label fw-bold text-danger">Error Message</label>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ $scrapeLog->error_message }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($scrapeLog->place)
                        <a href="{{ route('places.show', $scrapeLog->place) }}" class="btn btn-primary">
                            <i class="bi bi-eye me-1"></i>
                            View Place
                        </a>
                        <a href="{{ route('places.edit', $scrapeLog->place) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i>
                            Edit Place
                        </a>
                    @endif

                    <form action="{{ route('scrape-logs.destroy', $scrapeLog) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this scrape log?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i>
                            Delete Log
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Raw Payload -->
@if($scrapeLog->raw_payload)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-code me-2"></i>
                    Raw Payload Data
                </h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded"><code>{{ json_encode(json_decode($scrapeLog->raw_payload), JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
