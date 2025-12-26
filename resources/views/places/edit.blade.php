@extends('layouts.app')

@section('page-title', 'Edit Place')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('places.index') }}">Places</a></li>
                <li class="breadcrumb-item"><a href="{{ route('places.show', $place) }}">{{ $place->name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-pencil-square me-2"></i>
                    Edit Place: {{ $place->name }}
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('places.update', $place) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">Basic Information</h6>

                            <div class="mb-3">
                                <label for="place_id" class="form-label">Place ID</label>
                                <input type="text" class="form-control" id="place_id"
                                       value="{{ $place->place_id }}" readonly>
                                <div class="form-text text-muted">Place ID cannot be changed</div>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Place Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $place->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control @error('category') is-invalid @enderror"
                                       id="category" name="category" value="{{ old('category', $place->category) }}"
                                       placeholder="e.g., Restaurant, Hotel, Historical landmark">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="address" name="address" rows="3">{{ old('address', $place->address) }}</textarea>
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
                                               id="rating" name="rating" value="{{ old('rating', $place->rating) }}" step="0.1" min="0" max="5">
                                        @error('rating')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="review_count" class="form-label">Review Count</label>
                                        <input type="number" class="form-control @error('review_count') is-invalid @enderror"
                                               id="review_count" name="review_count" value="{{ old('review_count', $place->review_count) }}" min="0">
                                        @error('review_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $place->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror"
                                       id="website" name="website" value="{{ old('website', $place->website) }}"
                                       placeholder="https://example.com">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_valid" name="is_valid"
                                           value="1" {{ old('is_valid', $place->is_valid) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_valid">
                                        Mark as valid place
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('places.show', $place) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Back to Place Details
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            Update Place
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Place Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="mb-3">
                            <i class="bi bi-journal-text text-info fs-2"></i>
                            <h5 class="text-info mt-2">{{ $place->scrapeLogs->count() }}</h5>
                            <small class="text-muted">Scrape Logs</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <i class="bi bi-calendar-event text-success fs-2"></i>
                            <h5 class="text-success mt-2">{{ $place->created_at->diffForHumans() }}</h5>
                            <small class="text-muted">Created</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Danger Zone</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Once you delete this place, there is no going back. Please be certain.</p>
                <form action="{{ route('places.destroy', $place) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to permanently delete this place? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash me-1"></i>
                        Delete Place
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
