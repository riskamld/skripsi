@extends('layouts.app')

@section('page-title', 'API Tokens')
@section('page-subtitle', 'Manage your API access tokens')

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('api-tokens.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Token
        </a>
    </div>
</div>

<!-- API Tokens Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All API Tokens</h3>

        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <input type="text" name="table_search" class="form-control float-right" placeholder="Search tokens">

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
                    <th style="width: 20%;">Token Name</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 20%;">Last Used</th>
                    <th style="width: 15%;">Created</th>
                    <th style="width: 35%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tokens ?? [] as $token)
                <tr style="height: 45px;">
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <div style="font-size: 0.875rem; font-weight: 600;">{{ Str::limit($token->name, 15) }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">{{ substr($token->token, -8) }}</small>
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($token->is_active)
                            <span class="badge badge-success" style="font-size: 0.75rem;">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        @else
                            <span class="badge badge-secondary" style="font-size: 0.75rem;">
                                <i class="fas fa-pause-circle"></i>
                            </span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($token->last_used_at)
                            <span title="{{ $token->last_used_at->format('Y-m-d H:i:s') }}" style="font-size: 0.875rem;">
                                {{ $token->last_used_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-muted" style="font-size: 0.875rem;">Never</span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <span title="{{ $token->created_at->format('Y-m-d H:i:s') }}" style="font-size: 0.875rem;">
                            {{ $token->created_at->format('M d, Y') }}
                        </span>
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('api-tokens.show', $token) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('api-tokens.toggle-status', $token) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn {{ $token->is_active ? 'btn-warning' : 'btn-success' }} btn-sm">
                                    <i class="fas fa-{{ $token->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('api-tokens.regenerate', $token) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('This will invalidate the current token. Continue?')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('api-tokens.destroy', $token) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this token?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-key fa-2x mb-2"></i>
                            <h5>No API tokens found</h5>
                            <p style="font-size: 0.875rem;">Create your first API token to access the system programmatically.</p>
                            <a href="{{ route('api-tokens.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Create Token
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- /.card-body -->

    @if(isset($tokens) && $tokens->hasPages())
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" role="status" aria-live="polite">
                    Showing {{ $tokens->firstItem() }} to {{ $tokens->lastItem() }} of {{ $tokens->total() }} entries
                </div>
            </div>
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    {{ $tokens->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
<!-- /.card -->
@endsection
