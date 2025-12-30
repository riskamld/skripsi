@extends('layouts.app')

@section('page-title', 'Scrape Log Details')
@section('page-subtitle', 'Detailed information about scraping activity')

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('scrape-logs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Logs
        </a>
    </div>
</div>

<!-- Scrape Log Details -->
<div class="row">
    <!-- Main Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt mr-2"></i>
                    Scrape Log Details
                </h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Log ID:</dt>
                    <dd class="col-sm-8">{{ $scrapeLog->id }}</dd>

                    <dt class="col-sm-4">Place:</dt>
                    <dd class="col-sm-8">
                        @if($scrapeLog->place)
                            <a href="{{ route('places.show', $scrapeLog->place) }}" class="text-primary">
                                {{ $scrapeLog->place->name }}
                            </a>
                        @else
                            <span class="text-muted">Place not found</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8">
                        @if($scrapeLog->status === 'success')
                            <span class="badge badge-success">
                                <i class="fas fa-check"></i> Success
                            </span>
                        @elseif($scrapeLog->status === 'error')
                            <span class="badge badge-danger">
                                <i class="fas fa-times"></i> Error
                            </span>
                        @elseif($scrapeLog->status === 'warning')
                            <span class="badge badge-warning">
                                <i class="fas fa-exclamation-triangle"></i> Warning
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                <i class="fas fa-clock"></i> {{ ucfirst($scrapeLog->status ?? 'pending') }}
                            </span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Created:</dt>
                    <dd class="col-sm-8">{{ $scrapeLog->created_at->format('M d, Y \a\t H:i:s') }}</dd>

                    <dt class="col-sm-4">Updated:</dt>
                    <dd class="col-sm-8">{{ $scrapeLog->updated_at->format('M d, Y \a\t H:i:s') }}</dd>
                </dl>
            </div>
        </div>

        <!-- Error Message -->
        @if($scrapeLog->error_message)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>
                    Error Message
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <pre class="mb-0">{{ $scrapeLog->error_message }}</pre>
                </div>
            </div>
        </div>
        @endif

        <!-- Raw Payload -->
        @if($scrapeLog->raw_payload)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-code mr-2"></i>
                    Raw Payload
                </h3>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded"><code>{{ json_encode($scrapeLog->raw_payload, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar Information -->
    <div class="col-md-4">
        <!-- Place Information -->
        @if($scrapeLog->place)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    Place Information
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <strong>{{ $scrapeLog->place->name }}</strong>
                </div>
                <p class="text-muted small">{{ Str::limit($scrapeLog->place->address, 100) }}</p>

                @if($scrapeLog->place->phone)
                <div class="mt-3">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $scrapeLog->place->phone) }}"
                       target="_blank"
                       class="btn btn-success btn-sm btn-block">
                        <i class="fab fa-whatsapp"></i> Contact via WhatsApp
                    </a>
                </div>
                @endif

                <div class="mt-3">
                    <a href="{{ route('places.show', $scrapeLog->place) }}" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-eye"></i> View Full Details
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Log Statistics -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Log Statistics
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="h5 mb-0">{{ $scrapeLog->created_at->diffForHumans() }}</div>
                    <small class="text-muted">Time since log created</small>
                </div>
                <hr>
                <div class="text-center">
                    <div class="h5 mb-0">{{ $scrapeLog->updated_at->diffInSeconds($scrapeLog->created_at) }}s</div>
                    <small class="text-muted">Processing time</small>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>
                    Actions
                </h3>
            </div>
            <div class="card-body">
                <button class="btn btn-warning btn-sm btn-block mb-2" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Log
                </button>
                <button class="btn btn-info btn-sm btn-block" onclick="copyToClipboard()">
                    <i class="fas fa-copy"></i> Copy Details
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const text = `Log ID: {{ $scrapeLog->id }}\nStatus: {{ $scrapeLog->status }}\nPlace: {{ $scrapeLog->place ? $scrapeLog->place->name : 'N/A' }}\nCreated: {{ $scrapeLog->created_at }}`;
    navigator.clipboard.writeText(text).then(function() {
        alert('Log details copied to clipboard!');
    });
}
</script>
@endsection
