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
                <select class="form-select form-select-sm ajax-sort" style="width: auto;" data-url="{{ route('places.index') }}" onchange="changeSort(this.value)">
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
                        <th>Phone</th>
                        <th>Category</th>
                        <th>Images</th>
                        <th>Rating</th>
                        <th>Reviews</th>
                        <th>Navigate</th>
                        <th>Location</th>
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
                                @if($place->phone)
                                    @php
                                        // Clean phone number and create WhatsApp link
                                        $cleanPhone = preg_replace('/[^\d+]/', '', $place->phone);
                                        // Remove leading + if present for WhatsApp link
                                        $whatsappPhone = ltrim($cleanPhone, '+');
                                        $whatsappUrl = "https://wa.me/{$whatsappPhone}";
                                    @endphp
                                    <a href="{{ $whatsappUrl }}" target="_blank" class="text-decoration-none" title="Chat via WhatsApp">
                                        <code>{{ $place->phone }}</code>
                                        <i class="bi bi-whatsapp ms-1 text-success small"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
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
                                    <div class="d-flex align-items-center">
                                        {{ number_format($place->review_count) }}
                                        @if($place->review_count >= 100)
                                            <i class="bi bi-star-fill text-warning ms-1" title="High Review Count"></i>
                                        @elseif($place->review_count >= 50)
                                            <i class="bi bi-star-half text-warning ms-1" title="Good Review Count"></i>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($place->maps_url)
                                    <a href="{{ $place->maps_url }}" target="_blank" class="btn btn-sm btn-outline-success" title="Navigate to Google Maps">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </a>
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
                            <td colspan="10" class="text-center py-4">
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

<!-- AJAX Pagination -->
<div id="ajax-pagination-container" class="mt-4">
    @if($places->hasPages())
        <div class="row">
            <div class="col-12">
                <nav aria-label="Places pagination">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Page Info -->
                        <div class="text-muted small">
                            Page {{ $places->currentPage() }} of {{ $places->lastPage() }}
                            ({{ $places->total() }} total places)
                        </div>

                        <!-- Pagination Links -->
                        <ul class="pagination pagination-lg mb-0 ajax-pagination">
                            {{-- Previous Page Link --}}
                            @if ($places->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo; Previous</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link ajax-page-link" href="{{ $places->previousPageUrl() }}" rel="prev" data-page="{{ $places->currentPage() - 1 }}">&laquo; Previous</a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($places->getUrlRange(1, $places->lastPage()) as $page => $url)
                                @if ($page == $places->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link ajax-page-link" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($places->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link ajax-page-link" href="{{ $places->nextPageUrl() }}" rel="next" data-page="{{ $places->currentPage() + 1 }}">Next &raquo;</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">Next &raquo;</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
// AJAX Pagination and Sorting System
$(document).ready(function() {
    let isLoading = false;

    // Handle AJAX pagination clicks
    $(document).on('click', '.ajax-page-link', function(e) {
        e.preventDefault();
        if (isLoading) return;

        const url = $(this).attr('href');
        const page = $(this).data('page');

        loadPage(url, page);
    });

    // Handle AJAX sorting
    $('.ajax-sort').on('change', function() {
        if (isLoading) return;

        const value = $(this).val();
        const [sort, direction] = value.split('_');
        const url = new URL($(this).data('url'), window.location.origin);

        // Preserve existing query parameters
        const currentUrl = new URL(window.location);
        for (let [key, value] of currentUrl.searchParams) {
            url.searchParams.set(key, value);
        }

        // Update sort parameters
        if (sort) url.searchParams.set('sort', sort);
        if (direction) url.searchParams.set('direction', direction);

        // Reset to page 1 when sorting
        url.searchParams.set('page', '1');

        loadPage(url.toString(), 1);
    });

    // AJAX page loading function
    function loadPage(url, pageNumber) {
        if (isLoading) return;

        isLoading = true;
        showLoadingSpinner();

        // Update URL without page reload
        history.pushState({page: pageNumber}, '', url);

        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
            },
            success: function(response) {
                // Extract table content and pagination from response
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');

                // Update table content
                const newTable = doc.querySelector('.table-responsive');
                if (newTable) {
                    $('.table-responsive').replaceWith(newTable);
                }

                // Update pagination
                const newPagination = doc.querySelector('#ajax-pagination-container');
                if (newPagination) {
                    $('#ajax-pagination-container').replaceWith(newPagination);
                }

                // Update results summary
                const newSummary = doc.querySelector('.row.mb-3');
                if (newSummary) {
                    $('.row.mb-3').replaceWith(newSummary);
                }

                // Scroll to top of table smoothly
                $('html, body').animate({
                    scrollTop: $('.card').offset().top - 20
                }, 300);

                // Update page title if needed
                const newTitle = doc.querySelector('title');
                if (newTitle) {
                    document.title = newTitle.textContent;
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showErrorMessage('Failed to load page. Please try again.');

                // Fallback to regular navigation
                setTimeout(() => {
                    window.location.href = url;
                }, 2000);
            },
            complete: function() {
                isLoading = false;
                hideLoadingSpinner();
            }
        });
    }

    // Loading spinner functions
    function showLoadingSpinner() {
        if (!$('#ajax-loading-spinner').length) {
            const spinner = `
                <div id="ajax-loading-spinner" class="position-fixed" style="
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 9999;
                    background: rgba(255,255,255,0.9);
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                ">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span>Loading page...</span>
                    </div>
                </div>
            `;
            $('body').append(spinner);
        }
        $('#ajax-loading-spinner').show();
    }

    function hideLoadingSpinner() {
        $('#ajax-loading-spinner').fadeOut(200, function() {
            $(this).remove();
        });
    }

    function showErrorMessage(message) {
        // Remove existing error messages
        $('.ajax-error-message').remove();

        const errorDiv = `
            <div class="ajax-error-message alert alert-danger alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 10000; min-width: 300px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(errorDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            $('.ajax-error-message').fadeOut();
        }, 5000);
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.page) {
            const url = new URL(window.location);
            loadPage(url.toString(), event.state.page);
        }
    });

    // Keyboard shortcuts for pagination
    $(document).on('keydown', function(e) {
        // Left arrow for previous page
        if (e.keyCode === 37 && !$('.page-item:first-child').hasClass('disabled')) {
            $('.ajax-page-link[rel="prev"]').click();
        }
        // Right arrow for next page
        if (e.keyCode === 39 && !$('.page-item:last-child').hasClass('disabled')) {
            $('.ajax-page-link[rel="next"]').click();
        }
    });
});
</script>
@endpush
@endsection
