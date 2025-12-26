@extends('layouts.app')

@section('page-title', 'Place Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('places.index') }}">Places</a></li>
                <li class="breadcrumb-item active">{{ $place->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <!-- Place Details -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        {{ $place->name }}
                    </h4>
                    <div>
                        <a href="{{ route('places.edit', $place) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>
                            Edit
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Basic Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Place ID:</strong></td>
                                <td><code>{{ $place->place_id }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Category:</strong></td>
                                <td>{{ $place->category ?: 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($place->is_valid)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Source:</strong></td>
                                <td><span class="badge bg-info">{{ $place->source ?: 'Unknown' }}</span></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6>Rating & Reviews</h6>
                        <table class="table table-sm">
                            @if($place->rating)
                                <tr>
                                    <td><strong>Rating:</strong></td>
                                    <td>
                                        <span class="badge bg-warning text-dark fs-6">
                                            <i class="bi bi-star-fill me-1"></i>
                                            {{ number_format($place->rating, 1) }}/5.0
                                        </span>
                                    </td>
                                </tr>
                            @endif
                            @if($place->review_count)
                                <tr>
                                    <td><strong>Reviews:</strong></td>
                                    <td>{{ number_format($place->review_count) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Last Scraped:</strong></td>
                                <td>{{ $place->last_scraped_at ? $place->last_scraped_at->diffForHumans() : 'Never' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($place->address || $place->phone || $place->website)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Contact Information</h6>
                            <div class="row">
                                @if($place->address)
                                    <div class="col-md-6">
                                        <strong>Address:</strong><br>
                                        {{ $place->address }}
                                    </div>
                                @endif
                                @if($place->phone || $place->website)
                                    <div class="col-md-6">
                                        @if($place->phone)
                                            <strong>Phone:</strong> {{ $place->phone }}<br>
                                        @endif
                                        @if($place->website)
                                            <strong>Website:</strong>
                                            <a href="{{ $place->website }}" target="_blank" rel="noopener">
                                                {{ $place->website }}
                                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($place->raw_text || $place->raw_html)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Raw Data</h6>
                            <div class="accordion" id="rawDataAccordion">
                                @if($place->raw_text)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rawText">
                                                Raw Text ({{ strlen($place->raw_text) }} characters)
                                            </button>
                                        </h2>
                                        <div id="rawText" class="accordion-collapse collapse" data-bs-parent="#rawDataAccordion">
                                            <div class="accordion-body">
                                                <pre class="bg-light p-2 rounded"><code>{{ $place->raw_text }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($place->raw_html)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rawHtml">
                                                Raw HTML ({{ strlen($place->raw_html) }} characters)
                                            </button>
                                        </h2>
                                        <div id="rawHtml" class="accordion-collapse collapse" data-bs-parent="#rawDataAccordion">
                                            <div class="accordion-body">
                                                <pre class="bg-light p-2 rounded"><code>{{ htmlspecialchars($place->raw_html) }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Scrape History -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Scrape History
                </h6>
            </div>
            <div class="card-body">
                @if($place->scrapeLogs->count() > 0)
                    @foreach($place->scrapeLogs->sortByDesc('created_at')->take(10) as $log)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
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
                                <small class="text-muted d-block">{{ $log->created_at->format('M d, Y H:i') }}</small>
                                <small>
                                    Status: <span class="badge bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </small>
                                @if($log->error_message)
                                    <br><small class="text-danger">{{ $log->error_message }}</small>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if($place->scrapeLogs->count() > 10)
                        <div class="text-center">
                            <small class="text-muted">And {{ $place->scrapeLogs->count() - 10 }} more entries...</small>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-journal-x text-muted fs-1 mb-2"></i>
                        <p class="text-muted mb-0">No scrape history available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Timestamps -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Timestamps</h6>
            </div>
            <div class="card-body">
                <small>
                    <strong>Created:</strong><br>
                    {{ $place->created_at->format('M d, Y \a\t H:i:s') }}<br>
                    <em class="text-muted">{{ $place->created_at->diffForHumans() }}</em>
                </small>
                <hr>
                <small>
                    <strong>Last Updated:</strong><br>
                    {{ $place->updated_at->format('M d, Y \a\t H:i:s') }}<br>
                    <em class="text-muted">{{ $place->updated_at->diffForHumans() }}</em>
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
