@extends('layouts.app')

@section('page-title', 'Create New API Token')
@section('page-subtitle', 'Generate a new API access token')

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('api-tokens.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to API Tokens
        </a>
    </div>
</div>

<!-- Create API Token Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-plus mr-2"></i>
            Create New API Token
        </h3>
    </div>

    <form method="POST" action="{{ route('api-tokens.store') }}">
        @csrf

        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name">Token Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}"
                               placeholder="e.g., Mobile App, Web Scraper, etc." required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Give your token a descriptive name to identify its purpose
                        </small>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Important:</strong> After creating the token, make sure to copy and save the token value.
                It will only be displayed once for security reasons.
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>Create Token
            </button>
            <a href="{{ route('api-tokens.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>
@endsection
