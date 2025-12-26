@extends('layouts.app')

@section('page-title', 'Create New Place')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('places.index') }}">Places</a></li>
                <li class="breadcrumb-item active">Create New Place</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>
                    Create New Place
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('places.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">Basic Information</h6>

                            <div class="mb-3">
                                <label for="place_id" class="form-label">Place ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('place_id') is-invalid @enderror"
                                       id="place_id" name="place_id" value="{{ old('place_id') }}" required>
                                <div class="form-text">Unique Google Place ID</div>
                                @error('place_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Place Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control @error('category') is-invalid @enderror"
                                       id="category" name="category" value="{{ old('category') }}"
                                       placeholder="e.g., Restaurant, Hotel, Historical landmark">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Rating & Contact -->
                        <div class="col-md-6">
                            <h6 class="mb-3">Rating & Contact</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rating" class="form-label">Rating</label>
                                        <input type="number" class="form-control @error('rating') is-invalid @enderror"
                                               id="rating" name="rating" value="{{ old('rating') }}" step="0.1" min="0" max="5">
                                        @error('rating')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="review_count" class="form-label">Review Count</label>
                                        <input type="number" class="form-control @error('review_count') is-invalid @enderror"
                                               id="review_count" name="review_count" value="{{ old('review_count') }}" min="0">
                                        @error('review_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror"
                                       id="website" name="website" value="{{ old('website') }}"
                                       placeholder="https://example.com">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_valid" name="is_valid" value="1" {{ old('is_valid') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_valid">
                                        Mark as valid place
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('places.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Back to Places
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            Create Place
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Help & Tips</h6>
            </div>
            <div class="card-body">
                <h6>How to get Place ID:</h6>
                <ol class="mb-3">
                    <li>Go to Google Maps</li>
                    <li>Search for the place</li>
                    <li>Copy the place ID from the URL or use Google Places API</li>
                </ol>

                <h6>Field Guidelines:</h6>
                <ul>
                    <li><strong>Place ID:</strong> Must be unique across all places</li>
                    <li><strong>Name:</strong> Official place name</li>
                    <li><strong>Rating:</strong> 0.0 to 5.0 scale</li>
                    <li><strong>Website:</strong> Must include http:// or https://</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
