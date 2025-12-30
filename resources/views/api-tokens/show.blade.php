@extends('layouts.app')

@section('page-title', 'API Token Details')
@section('page-subtitle', 'Detailed information about API token')

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('api-tokens.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tokens
        </a>
    </div>
</div>

<!-- API Token Details -->
<div class="row">
    <!-- Main Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-key mr-2"></i>
                    API Token Details
                </h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Token ID:</dt>
                    <dd class="col-sm-8">{{ $token->id }}</dd>

                    <dt class="col-sm-4">Token Name:</dt>
                    <dd class="col-sm-8">
                        <strong>{{ $token->name }}</strong>
                    </dd>

                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8">
                        @if($token->is_active)
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                <i class="fas fa-pause-circle"></i> Inactive
                            </span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Token:</dt>
                    <dd class="col-sm-8">
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $token->token }}" readonly id="tokenField">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyToken()">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="toggleTokenVisibility()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">This token grants API access to your application</small>
                    </dd>

                    <dt class="col-sm-4">Created:</dt>
                    <dd class="col-sm-8">{{ $token->created_at->format('M d, Y \a\t H:i:s') }}</dd>

                    <dt class="col-sm-4">Updated:</dt>
                    <dd class="col-sm-8">{{ $token->updated_at->format('M d, Y \a\t H:i:s') }}</dd>
                </dl>
            </div>
        </div>

        <!-- Usage Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Usage Information
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text">Last Used</span>
                                <span class="info-box-number">
                                    @if($token->last_used_at)
                                        {{ $token->last_used_at->diffForHumans() }}
                                    @else
                                        Never
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <span class="info-box-text">Last IP Address</span>
                                <span class="info-box-number">
                                    {{ $token->last_used_ip ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>
                    Token Actions
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('api-tokens.toggle-status', $token) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="btn {{ $token->is_active ? 'btn-warning' : 'btn-success' }} btn-block btn-sm">
                        <i class="fas fa-{{ $token->is_active ? 'pause' : 'play' }}"></i>
                        {{ $token->is_active ? 'Deactivate Token' : 'Activate Token' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('api-tokens.regenerate', $token) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-block btn-sm" onclick="return confirm('This will invalidate the current token. Continue?')">
                        <i class="fas fa-sync-alt"></i> Regenerate Token
                    </button>
                </form>

                <form method="POST" action="{{ route('api-tokens.destroy', $token) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block btn-sm" onclick="return confirm('Are you sure you want to delete this token?')">
                        <i class="fas fa-trash"></i> Delete Token
                    </button>
                </form>
            </div>
        </div>

        <!-- Token Security Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Security Information
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Important Security Notes</h5>
                    <ul class="mb-0">
                        <li>Keep your API tokens secure and never share them publicly</li>
                        <li>Regenerate tokens regularly for better security</li>
                        <li>Monitor token usage and deactivate unused tokens</li>
                        <li>Use different tokens for different applications</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- API Usage Guide -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book mr-2"></i>
                    API Usage
                </h3>
            </div>
            <div class="card-body">
                <h6>How to use this token:</h6>
                <pre class="bg-light p-2 rounded"><code>Authorization: Bearer {{ $token->token }}</code></pre>
                <small class="text-muted">Include this header in all API requests</small>
            </div>
        </div>
    </div>
</div>

<script>
function copyToken() {
    const tokenField = document.getElementById('tokenField');
    tokenField.select();
    document.execCommand('copy');

    // Show success message
    const button = event.target.closest('button');
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');

    setTimeout(() => {
        button.innerHTML = originalIcon;
        button.classList.remove('btn-success');
    }, 2000);
}

function toggleTokenVisibility() {
    const tokenField = document.getElementById('tokenField');
    const toggleIcon = document.getElementById('toggleIcon');

    if (tokenField.type === 'password') {
        tokenField.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        tokenField.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// Initialize token field as masked
document.addEventListener('DOMContentLoaded', function() {
    const tokenField = document.getElementById('tokenField');
    tokenField.type = 'password';
});
</script>
@endsection
