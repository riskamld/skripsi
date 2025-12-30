@extends('layouts.app')

@section('page-title', 'Edit Place')
@section('page-subtitle', 'Update place information')

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('places.show', $place) }}" class="btn btn-info">
            <i class="fas fa-eye"></i> View Place
        </a>
        <a href="{{ route('places.index') }}" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Edit Place Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-edit mr-2"></i>
            Edit Place: {{ $place->name }}
        </h3>
    </div>

    <form method="POST" action="{{ route('places.update', $place) }}">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-info-circle mr-2"></i>Basic Information
                    </h5>

                    <div class="form-group">
                        <label for="name">Place Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $place->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" class="form-control @error('category') is-invalid @enderror"
                               id="category" name="category" value="{{ old('category', $place->category) }}">
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="address">Address *</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="3" required>{{ old('address', $place->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone" value="{{ old('phone', $place->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" class="form-control @error('website') is-invalid @enderror"
                               id="website" name="website" value="{{ old('website', $place->website) }}">
                        @error('website')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="col-md-6">
                    <h5 class="text-success mb-3">
                        <i class="fas fa-chart-line mr-2"></i>Additional Details
                    </h5>

                    <div class="form-group">
                        <label for="rating">Rating</label>
                        <input type="number" step="0.1" min="0" max="5" class="form-control @error('rating') is-invalid @enderror"
                               id="rating" name="rating" value="{{ old('rating', $place->rating) }}">
                        @error('rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="review_count">Review Count</label>
                        <input type="number" class="form-control @error('review_count') is-invalid @enderror"
                               id="review_count" name="review_count" value="{{ old('review_count', $place->review_count) }}">
                        @error('review_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="opening_hours">Opening Hours</label>
                        <textarea class="form-control @error('opening_hours') is-invalid @enderror"
                                  id="opening_hours" name="opening_hours" rows="3">{{ old('opening_hours', $place->opening_hours) }}</textarea>
                        @error('opening_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="maps_url">Google Maps URL</label>
                        <input type="url" class="form-control @error('maps_url') is-invalid @enderror"
                               id="maps_url" name="maps_url" value="{{ old('maps_url', $place->maps_url) }}">
                        @error('maps_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="source">Source</label>
                        <input type="text" class="form-control @error('source') is-invalid @enderror"
                               id="source" name="source" value="{{ old('source', $place->source) }}">
                        @error('source')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Coordinates -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="lat">Latitude</label>
                        <input type="number" step="any" class="form-control @error('lat') is-invalid @enderror"
                               id="lat" name="lat" value="{{ old('lat', $place->lat) }}">
                        @error('lat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="lng">Longitude</label>
                        <input type="number" step="any" class="form-control @error('lng') is-invalid @enderror"
                               id="lng" name="lng" value="{{ old('lng', $place->lng) }}">
                        @error('lng')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="text-info mb-3">
                        <i class="fas fa-images mr-2"></i>Images
                    </h5>
                    <div class="row">
                        @for($i = 1; $i <= 4; $i++)
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="image_{{ $i }}">Image {{ $i }}</label>
                                <input type="url" class="form-control @error('image_' . $i) is-invalid @enderror"
                                       id="image_{{ $i }}" name="image_{{ $i }}" value="{{ old('image_' . $i, $place->{'image_' . $i}) }}">
                                @error('image_' . $i)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_valid" name="is_valid" value="1"
                                   {{ old('is_valid', $place->is_valid) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_valid">Is Valid</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="parser_version">Parser Version</label>
                        <input type="text" class="form-control @error('parser_version') is-invalid @enderror"
                               id="parser_version" name="parser_version" value="{{ old('parser_version', $place->parser_version) }}">
                        @error('parser_version')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>Update Place
            </button>
            <a href="{{ route('places.show', $place) }}" class="btn btn-secondary ml-2">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>
@endsection
