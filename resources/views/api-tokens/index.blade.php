@extends('layouts.app')

@section('page-title', 'API Tokens')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <i class="bi bi-key me-2"></i>
                API Tokens
            </h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTokenModal">
                <i class="bi bi-plus-circle me-2"></i>
                Generate New Token
            </button>
        </div>
        <p class="text-muted mt-2">Manage API tokens for accessing Mafaza Fortuna API endpoints</p>
    </div>
</div>

<!-- Success Message -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- New Token Alert -->
@if(session('new_token'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-start">
        <div class="flex-grow-1">
            <h6 class="alert-heading mb-2">
                <i class="bi bi-key-fill me-2"></i>
                New API Token Generated!
            </h6>
            <p class="mb-2">Copy this token and paste it into your Chrome extension settings:</p>
            <div class="input-group mb-2">
                <input type="text" class="form-control" id="newTokenValue" value="{{ session('new_token') }}" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('newTokenValue')">
                    <i class="bi bi-clipboard me-1"></i>
                    Copy
                </button>
            </div>
            <small class="text-muted">
                <i class="bi bi-exclamation-triangle me-1"></i>
                This token will only be shown once. Make sure to copy it now!
            </small>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Tokens List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>
            Your API Tokens
        </h5>
    </div>
    <div class="card-body">
        @if($tokens->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Last Used</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tokens as $token)
                        <tr>
                            <td>
                                <strong>{{ $token->name }}</strong>
                            </td>
                            <td>
                                @if($token->is_active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-pause-circle me-1"></i>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($token->last_used_at)
                                    <small class="text-muted">
                                        {{ $token->last_used_at->diffForHumans() }}
                                        @if($token->last_used_ip)
                                            <br>IP: {{ $token->last_used_ip }}
                                        @endif
                                    </small>
                                @else
                                    <small class="text-muted">Never used</small>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $token->created_at->format('M d, Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <form action="{{ route('api-tokens.toggle-status', $token->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm {{ $token->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                onclick="return confirm('{{ $token->is_active ? 'Deactivate' : 'Activate' }} this token?')">
                                            <i class="bi {{ $token->is_active ? 'bi-pause' : 'bi-play' }} me-1"></i>
                                            {{ $token->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('api-tokens.regenerate', $token->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm btn-outline-primary"
                                                onclick="return confirm('Regenerate this token? The old token will stop working.')">
                                            <i class="bi bi-arrow-clockwise me-1"></i>
                                            Regenerate
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteToken({{ $token->id }}, '{{ $token->name }}')">
                                        <i class="bi bi-trash me-1"></i>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{ $tokens->links() }}
        @else
            <div class="text-center py-5">
                <i class="bi bi-key text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No API Tokens Yet</h5>
                <p class="text-muted">Create your first API token to start using the Mafaza Fortuna API.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTokenModal">
                    <i class="bi bi-plus-circle me-2"></i>
                    Generate First Token
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Create Token Modal -->
<div class="modal fade" id="createTokenModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    Generate New API Token
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('api-tokens.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Token Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                               placeholder="e.g., Chrome Extension, Mobile App, etc." required>
                        <div class="form-text">
                            Give your token a descriptive name to remember its purpose.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key me-2"></i>
                        Generate Token
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTokenModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Delete API Token
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the token "<strong id="deleteTokenName"></strong>"?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. Any applications using this token will lose access to the API.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteTokenForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>
                        Delete Token
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices

    navigator.clipboard.writeText(element.value).then(function() {
        // Show success feedback
        const button = element.nextElementSibling;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');

        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy to clipboard');
    });
}

function deleteToken(tokenId, tokenName) {
    document.getElementById('deleteTokenName').textContent = tokenName;
    document.getElementById('deleteTokenForm').action = `/api-tokens/${tokenId}`;
    new bootstrap.Modal(document.getElementById('deleteTokenModal')).show();
}
</script>
@endpush
