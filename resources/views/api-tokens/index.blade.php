@extends('layouts.app')

@section('page-title', __('messages.api_tokens_title'))
@section('page-subtitle', __('messages.api_tokens_subtitle'))

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('api-tokens.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> 
        </a>
    </div>
</div>

<!-- API Tokens Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"></h3>

        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <input type="text" name="table_search" class="form-control float-right" placeholder="">

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
                    <th style="width: 20%;"></th>
                    <th style="width: 10%;"></th>
                    <th style="width: 20%;"></th>
                    <th style="width: 15%;"></th>
                    <th style="width: 35%;"></th>
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
                            <span class="text-muted" style="font-size: 0.875rem;"></span>
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
                                <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('{{ addslashes(__('messages.confirm_regenerate')) }}')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('api-tokens.destroy', $token) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ addslashes(__('messages.confirm_delete_token')) }}')">
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
                            <h5></h5>
                            <p style="font-size: 0.875rem;"></p>
                            <a href="{{ route('api-tokens.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> 
                            </a>
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
            <span class="sr-only"></span>
        </div>
        <small class="text-muted ml-2"></small>
    </div>

    <!-- End of Results Indicator -->
    <div id="end-indicator" class="text-center py-3" style="display: none;">
        <small class="text-muted"></small>
    </div>
</div>
<!-- /.card -->

<script>
$(document).ready(function() {
    // Infinite Scroll Variables
    let currentPage = {{ $tokens->currentPage() }};
    let isLoading = false;
    let hasMorePages = {{ $tokens->hasMorePages() ? 'true' : 'false' }};

    // Infinite Scroll Implementation
    $(window).on('scroll', function() {
        if (isLoading || !hasMorePages) return;

        const scrollTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        const documentHeight = $(document).height();

        // Load more when user is 200px from bottom
        if (scrollTop + windowHeight >= documentHeight - 200) {
            loadMoreTokens();
        }
    });

    function loadMoreTokens() {
        if (isLoading || !hasMorePages) return;

        isLoading = true;
        currentPage++;

        // Show loading indicator
        $('#loading-indicator').show();

        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', currentPage);

        $.ajax({
            url: '{{ route("api-tokens.index") }}',
            type: 'GET',
            data: urlParams.toString(),
            success: function(response) {
                if (response.tokens && response.tokens.length > 0) {
                    // Append new tokens to table
                    const tbody = $('tbody');
                    response.tokens.forEach(function(token) {
                        const rowHtml = generateTokenRow(token);
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

    function generateTokenRow(token) {
        // Generate HTML for a token row
        let row = '<tr style="height: 35px;">';

        // Token Name column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<div style="font-size: 0.875rem; font-weight: 600;">' + escapeHtml(token.name.substring(0, 15)) + (token.name.length > 15 ? '...' : '') + '</div>';
        row += '<small class="text-muted" style="font-size: 0.75rem;">' + token.token.substring(-8) + '</small>';
        row += '</td>';

        // Status column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        if (token.is_active) {
            row += '<span class="badge badge-success" style="font-size: 0.75rem;"><i class="fas fa-check-circle"></i></span>';
        } else {
            row += '<span class="badge badge-secondary" style="font-size: 0.75rem;"><i class="fas fa-pause-circle"></i></span>';
        }
        row += '</td>';

        // Last Used column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        if (token.last_used_at) {
            row += '<span title="' + token.last_used_at + '" style="font-size: 0.875rem;">Just now</span>';
        } else {
            row += '<span class="text-muted" style="font-size: 0.875rem;">Never</span>';
        }
        row += '</td>';

        // Created column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<span title="' + token.created_at + '" style="font-size: 0.875rem;">' + new Date(token.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + '</span>';
        row += '</td>';

        // Actions column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<div class="btn-group btn-group-sm">';
        row += '<a href="/api-tokens/' + token.id + '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>';
        row += '<form method="POST" action="/api-tokens/' + token.id + '/toggle-status" class="d-inline">';
        row += '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">';
        row += '<button type="submit" class="btn ' + (token.is_active ? 'btn-warning' : 'btn-success') + ' btn-sm">';
        row += '<i class="fas fa-' + (token.is_active ? 'pause' : 'play') + '"></i>';
        row += '</button>';
        row += '</form>';
        row += '<form method="POST" action="/api-tokens/' + token.id + '/regenerate" class="d-inline">';
        row += '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">';
        row += '<button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm(\'This will invalidate the current token. Continue?\')">';
        row += '<i class="fas fa-sync-alt"></i>';
        row += '</button>';
        row += '</form>';
        row += '<form method="POST" action="/api-tokens/' + token.id + '" class="d-inline">';
        row += '<input type="hidden" name="_method" value="DELETE">';
        row += '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">';
        row += '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this token?\')">';
        row += '<i class="fas fa-trash"></i>';
        row += '</button>';
        row += '</form>';
        row += '</div>';
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
