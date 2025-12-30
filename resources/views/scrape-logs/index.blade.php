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

    @if(isset($logs) && $logs->hasPages())
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" role="status" aria-live="polite">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} entries
                </div>
            </div>
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    {{ $logs->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
<!-- /.card -->
@endsection
