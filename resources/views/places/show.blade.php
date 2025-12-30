@extends('layouts.app')

@section('page-title', 'Place Details')
@section('page-subtitle', '{{ $place->name }}')

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('places.edit', $place) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit Place
        </a>
        <a href="{{ route('places.index') }}" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Place Details Cards -->
<div class="row">
    <!-- Basic Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>Basic Information
                </h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Name:</dt>
                    <dd class="col-sm-8">{{ $place->name }}</dd>

                    <dt class="col-sm-4">Address:</dt>
                    <dd class="col-sm-8">{{ $place->address }}</dd>

                    <dt class="col-sm-4">Phone:</dt>
                    <dd class="col-sm-8">
                        @if($place->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $place->phone) }}"
                               target="_blank"
                               class="btn btn-success btn-sm">
                                <i class="fab fa-whatsapp"></i> {{ $place->phone }}
                            </a>
                        @else
                            <span class="text-muted">Not available</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Website:</dt>
                    <dd class="col-sm-8">
                        @if($place->website)
                            <a href="{{ $place->website }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt"></i> Visit Website
                            </a>
                        @else
                            <span class="text-muted">Not available</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Additional Details -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>Additional Details
                </h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Rating:</dt>
                    <dd class="col-sm-8">
                        @if($place->rating)
                            <span class="badge badge-warning">
                                <i class="fas fa-star"></i> {{ $place->rating }}/5.0
                            </span>
                        @else
                            <span class="badge badge-secondary">Not rated</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Opening Hours:</dt>
                    <dd class="col-sm-8">{{ $place->opening_hours ?? 'Not available' }}</dd>

                    <dt class="col-sm-4">Images:</dt>
                    <dd class="col-sm-8">
                        @if($place->images)
                            <span class="badge badge-info">{{ count(json_decode($place->images, true)) }} images</span>
                        @else
                            <span class="text-muted">No images</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Created:</dt>
                    <dd class="col-sm-8">{{ $place->created_at->format('M d, Y \a\t H:i') }}</dd>

                    <dt class="col-sm-4">Updated:</dt>
                    <dd class="col-sm-8">{{ $place->updated_at->format('M d, Y \a\t H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Images Section -->
@if($place->images)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-images mr-2"></i>Place Images
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach(json_decode($place->images, true) as $image)
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="{{ $image }}" class="card-img-top" alt="Place image" style="height: 200px; object-fit: cover;">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
