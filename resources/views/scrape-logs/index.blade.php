@extends('layouts.app')

@section('page-title', 'Scrape Logs')
@section('page-subtitle', 'View scraping activity logs')

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <form method="POST" action="{{ route('scrape-logs.clear-all') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to clear all logs? This action cannot be undone.')">
                <i class="fas fa-trash"></i> Clear All Logs
            </button>
        </form>
    </div>
</div>

<!-- Scrape Logs Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Scrape Logs</h3>

        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <input type="text" name="table_search" class="form-control float-right" placeholder="Search logs">

                <div class="input-group-append">
                    <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th style="width: 20%;">Place Name</th>
                    <th style="width: 15%;">Status</th>
                    <th style="width: 40%;">Message</th>
                    <th style="width: 15%;">Created</th>
                    <th style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs ?? [] as $log)
                <tr style="height: 45px;">
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <div style="font-size: 0.875rem; font-weight: 600;">{{ Str::limit($log->place->name ?? 'Unknown Place', 18) }}</div>
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($log->status === 'success')
                            <span class="badge badge-success" style="font-size: 0.75rem;">
                                <i class="fas fa-check"></i> Success
                            </span>
                        @elseif($log->status === 'error')
                            <span class="badge badge-danger" style="font-size: 0.75rem;">
                                <i class="fas fa-times"></i> Error
                            </span>
                        @elseif($log->status === 'warning')
                            <span class="badge badge-warning" style="font-size: 0.75rem;">
                                <i class="fas fa-exclamation-triangle"></i> Warning
                            </span>
                        @else
                            <span class="badge badge-secondary" style="font-size: 0.75rem;">
                                <i class="fas fa-clock"></i> {{ ucfirst($log->status ?? 'pending') }}
                            </span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <span title="{{ $log->message ?? '' }}" style="font-size: 0.875rem;">
                            {{ Str::limit($log->message ?? 'No message', 40) }}
                        </span>
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <span title="{{ $log->created_at->format('Y-m-d H:i:s') }}" style="font-size: 0.875rem;">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <a href="{{ route('scrape-logs.show', $log) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-history fa-2x mb-2"></i>
                            <h5>No scrape logs found</h5>
                            <p style="font-size: 0.875rem;">No scraping activities have been logged yet.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->

    <!-- Infinite Scroll Loading Indicator -->
    <div id="loading-indicator" class="text-center py-3" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <small class="text-muted ml-2">Loading more logs...</small>
    </div>

    <!-- End of Results Indicator -->
    <div id="end-indicator" class="text-center py-3" style="display: none;">
        <small class="text-muted">No more logs to load</small>
    </div>
</div>
<!-- /.card -->

<script>
$(document).ready(function() {
    // Infinite Scroll Variables
    let currentPage = {{ $logs->currentPage() }};
    let isLoading = false;
    let hasMorePages = {{ $logs->hasMorePages() ? 'true' : 'false' }};

    // Infinite Scroll Implementation
    $(window).on('scroll', function() {
        if (isLoading || !hasMorePages) return;

        const scrollTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        const documentHeight = $(document).height();

        // Load more when user is 200px from bottom
        if (scrollTop + windowHeight >= documentHeight - 200) {
            loadMoreLogs();
        }
    });

    function loadMoreLogs() {
        if (isLoading || !hasMorePages) return;

        isLoading = true;
        currentPage++;

        // Show loading indicator
        $('#loading-indicator').show();

        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', currentPage);

        $.ajax({
            url: '{{ route("scrape-logs.index") }}',
            type: 'GET',
            data: urlParams.toString(),
            success: function(response) {
                if (response.logs && response.logs.length > 0) {
                    // Append new logs to table
                    const tbody = $('tbody');
                    response.logs.forEach(function(log) {
                        const rowHtml = generateLogRow(log);
                        tbody.append(rowHtml);
                    });

                    hasMorePages = response.has_more;
                } else {
                    hasMorePages = false;
                }
            },
            error: function(xhr, status, error) {
                console.error('Infinite scroll error:', error);
                hasMorePages = false;
            },
            complete: function() {
                isLoading = false;
                $('#loading-indicator').hide();

                if (!hasMorePages) {
                    $('#end-indicator').show();
                }
            }
        });
    }

    function generateLogRow(log) {
        // Generate HTML for a log row
        let row = '<tr style="height: 35px;">';

        // Place Name column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<div style="font-size: 0.875rem; font-weight: 600;">' + escapeHtml((log.place ? log.place.name : 'Unknown Place').substring(0, 18)) + ((log.place ? log.place.name : 'Unknown Place').length > 18 ? '...' : '') + '</div>';
        row += '</td>';

        // Status column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        let statusClass = 'secondary';
        let statusIcon = 'clock';
        let statusText = 'Pending';

        if (log.status === 'success') {
            statusClass = 'success';
            statusIcon = 'check';
            statusText = 'Success';
        } else if (log.status === 'error') {
            statusClass = 'danger';
            statusIcon = 'times';
            statusText = 'Error';
        } else if (log.status === 'warning') {
            statusClass = 'warning';
            statusIcon = 'exclamation-triangle';
            statusText = 'Warning';
        }

        row += '<span class="badge badge-' + statusClass + '" style="font-size: 0.75rem;">';
        row += '<i class="fas fa-' + statusIcon + '"></i> ' + statusText;
        row += '</span>';
        row += '</td>';

        // Message column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<span title="' + escapeHtml(log.message || '') + '" style="font-size: 0.875rem;">';
        row += escapeHtml((log.message || 'No message').substring(0, 40)) + ((log.message || 'No message').length > 40 ? '...' : '');
        row += '</span>';
        row += '</td>';

        // Created column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<span title="' + log.created_at + '" style="font-size: 0.875rem;">';
        row += 'Just now'; // For simplicity, show "Just now" for new entries
        row += '</span>';
        row += '</td>';

        // Actions column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<a href="/scrape-logs/' + log.id + '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>';
        row += '</td>';

        row += '</tr>';
        return row;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endsection
