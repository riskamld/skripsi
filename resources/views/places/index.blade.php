@extends('layouts.app')

@section('page-title', 'Places')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>
                <i class="bi bi-geo-alt me-2"></i>
                Places Management
            </h2>
            <div class="d-flex gap-2">
                @if($places->count() > 0)
                    <form action="{{ route('places.clear-all') }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to clear all places? This action cannot be undone.')">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            Clear All Places
                        </button>
                    </form>
                @endif
                <a href="{{ route('places.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add New Place
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Search by name, category, address...">
            </div>

            <div class="col-md-2">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="rating_min" class="form-label">Min Rating</label>
                <input type="number" class="form-control" id="rating_min" name="rating_min"
                       value="{{ request('rating_min') }}" step="0.1" min="0" max="5">
            </div>

            <div class="col-md-2">
                <label for="rating_max" class="form-label">Max Rating</label>
                <input type="number" class="form-control" id="rating_max" name="rating_max"
                       value="{{ request('rating_max') }}" step="0.1" min="0" max="5">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-1"></i>
                    Search
                </button>
                <a href="{{ route('places.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Showing {{ $places->firstItem() ?? 0 }} to {{ $places->lastItem() ?? 0 }}
                of {{ $places->total() }} places
            </small>

            <div class="d-flex align-items-center">
                <label class="me-2">Sort by:</label>
                <select class="form-select form-select-sm" style="width: auto;" onchange="changeSort(this.value)">
                    <option value="created_at_desc" {{ request('sort', 'created_at') === 'created_at' && request('direction', 'desc') === 'desc' ? 'selected' : '' }}>
                        Newest First
                    </option>
                    <option value="created_at_asc" {{ request('sort', 'created_at') === 'created_at' && request('direction') === 'asc' ? 'selected' : '' }}>
                        Oldest First
                    </option>
                    <option value="name_asc" {{ request('sort') === 'name' && request('direction') === 'asc' ? 'selected' : '' }}>
                        Name A-Z
                    </option>
                    <option value="name_desc" {{ request('sort') === 'name' && request('direction') === 'desc' ? 'selected' : '' }}>
                        Name Z-A
                    </option>
                    <option value="rating_desc" {{ request('sort') === 'rating' && request('direction') === 'desc' ? 'selected' : '' }}>
                        Highest Rating
                    </option>
                    <option value="review_count_desc" {{ request('sort') === 'review_count' && request('direction') === 'desc' ? 'selected' : '' }}>
                        Most Reviews
                    </option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Places Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Images</th>
                        <th>Rating</th>
                        <th>Reviews</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($places as $place)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $place->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $place->place_id }}</small>
                                </div>
                            </td>
                            <td>
                                @if($place->category)
                                    <span class="badge bg-secondary">{{ $place->category }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $imageCount = 0;
                                    for ($i = 1; $i <= 4; $i++) {
                                        if ($place->{'image_' . $i}) {
                                            $imageCount++;
                                        }
                                    }
                                @endphp
                                @if($imageCount > 0)
                                    <span class="badge bg-info">
                                        <i class="bi bi-images me-1"></i>
                                        {{ $imageCount }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($place->rating)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-star-fill me-1"></i>
                                        {{ number_format($place->rating, 1) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($place->review_count)
                                    {{ number_format($place->review_count) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($place->address)
                                    <small>{{ Str::limit($place->address, 30) }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($place->is_valid)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $place->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('places.show', $place) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('places.edit', $place) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('places.destroy', $place) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this place?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-geo-alt text-muted fs-1 mb-3"></i>
                                <h5 class="text-muted">No places found</h5>
                                <p class="text-muted">Try adjusting your search criteria or add a new place.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($places->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $places->appends(request()->query())->links() }}
    </div>
@endif

@push('scripts')
<script>
function changeSort(value) {
    const [sort, direction] = value.split('_');
    const url = new URL(window.location);

    if (sort) url.searchParams.set('sort', sort);
    if (direction) url.searchParams.set('direction', direction);

    window.location.href = url.toString();
}
</script>
@endpush
@endsection
