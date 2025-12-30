@extends('layouts.app')

@section('page-title', 'Places')
@section('page-subtitle', 'Manage your places database')

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('places.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Place
        </a>
        <form method="POST" action="{{ route('places.clear-all') }}" class="d-inline" style="margin-left: 10px;">
            @csrf
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to clear all places?')">
                <i class="fas fa-trash"></i> Clear All Places
            </button>
        </form>
    </div>
</div>

<!-- Places table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Places</h3>

        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search places (min 4 chars)">
                <div class="input-group-append">
                    <button type="button" id="clearSearch" class="btn btn-outline-secondary" style="display: none;">
                        <i class="fas fa-times"></i>
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
                    <th style="width: 35%;">Name</th>
                    <th style="width: 12%;">Address</th>
                    <th style="width: 8%;">Phone</th>
                    <th style="width: 8%;">Website</th>
                    <th style="width: 6%;">
                        <a href="{{ route('places.index', array_merge(request()->query(), ['sort' => 'rating', 'direction' => (request('sort') === 'rating' && request('direction') === 'desc') ? 'asc' : 'desc'])) }}" class="text-decoration-none">
                            Rating
                            @if(request('sort') === 'rating')
                                <i class="fas fa-sort-{{ request('direction') === 'desc' ? 'down' : 'up' }}"></i>
                            @else
                                <i class="fas fa-sort text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width: 10%;">
                        <a href="{{ route('places.index', array_merge(request()->query(), ['sort' => 'review_count', 'direction' => (request('sort') === 'review_count' && request('direction') === 'desc') ? 'asc' : 'desc'])) }}" class="text-decoration-none">
                            Reviews
                            @if(request('sort') === 'review_count')
                                <i class="fas fa-sort-{{ request('direction') === 'desc' ? 'down' : 'up' }}"></i>
                            @else
                                <i class="fas fa-sort text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width: 10%;">
                        <a href="{{ route('places.index', array_merge(request()->query(), ['sort' => 'last_scraped_at', 'direction' => (request('sort') === 'last_scraped_at' && request('direction') === 'desc') ? 'asc' : 'desc'])) }}" class="text-decoration-none">
                            Scraped
                            @if(request('sort') === 'last_scraped_at')
                                <i class="fas fa-sort-{{ request('direction') === 'desc' ? 'down' : 'up' }}"></i>
                            @else
                                <i class="fas fa-sort text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($places ?? [] as $place)
                <tr style="height: 45px;">
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <div style="font-size: 0.875rem; font-weight: 600;">{{ Str::limit($place->name, 30) }}</div>
                        <small class="text-info" style="font-size: 0.75rem;">{{ $place->category ? Str::limit($place->category, 25) : '-' }}</small>
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <div style="font-size: 0.875rem;">{{ Str::limit($place->address, 25) }}</div>
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($place->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $place->phone) }}"
                               target="_blank"
                               class="btn btn-sm btn-success"
                               title="Chat via WhatsApp">
                                <i class="fab fa-whatsapp"></i> {{ Str::limit($place->phone, 12) }}
                            </a>
                        @else
                            <span style="font-size: 0.875rem;">N/A</span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($place->website)
                            <a href="{{ $place->website }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        @else
                            <span style="font-size: 0.875rem;">N/A</span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($place->rating)
                            <span class="badge badge-warning" style="font-size: 0.75rem;">
                                <i class="fas fa-star"></i> {{ $place->rating }}
                            </span>
                        @else
                            <span class="badge badge-secondary" style="font-size: 0.75rem;">-</span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($place->review_count)
                            <span class="badge badge-info" style="font-size: 0.75rem;">
                                <i class="fas fa-comments"></i> {{ number_format($place->review_count) }}
                            </span>
                        @else
                            <span class="badge badge-secondary" style="font-size: 0.75rem;">-</span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        @if($place->last_scraped_at)
                            <div style="font-size: 0.875rem; font-weight: 600;">{{ $place->last_scraped_at->format('M d') }}</div>
                            <small title="{{ $place->last_scraped_at->format('Y-m-d H:i:s') }}" class="text-muted" style="font-size: 0.75rem;">
                                {{ $place->last_scraped_at->diffForHumans() }}
                            </small>
                        @else
                            <span class="text-muted" style="font-size: 0.875rem;">Never</span>
                        @endif
                    </td>
                    <td style="padding: 8px 12px; vertical-align: middle;">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('places.show', $place) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('places.edit', $place) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('places.destroy', $place) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                            <h5>No places found</h5>
                            <p style="font-size: 0.875rem;">Get started by adding your first place to the database.</p>
                            <a href="{{ route('places.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Place
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
            <span class="sr-only">Loading...</span>
        </div>
        <small class="text-muted ml-2">Loading more places...</small>
    </div>

    <!-- End of Results Indicator -->
    <div id="end-indicator" class="text-center py-3" style="display: none;">
        <small class="text-muted">No more places to load</small>
    </div>
</div>
<!-- /.card -->

<script>
$(document).ready(function() {
    let searchTimeout;
    const searchInput = $('#searchInput');
    const clearSearchBtn = $('#clearSearch');

    // Real-time search with 4+ characters
    searchInput.on('input', function() {
        const query = $(this).val().trim();

        // Show/hide clear button
        clearSearchBtn.toggle(query.length > 0);

        // Clear previous timeout
        clearTimeout(searchTimeout);

        if (query.length >= 4) {
            // Debounce search - wait 300ms after user stops typing
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 300);
        } else if (query.length === 0) {
            // Clear search if input is empty
            clearSearch();
        }
    });

    // Clear search button
    clearSearchBtn.on('click', function() {
        searchInput.val('');
        clearSearchBtn.hide();
        clearSearch();
    });

    function performSearch(query) {
        // Show loading indicator
        searchInput.prop('disabled', true);
        searchInput.css('opacity', '0.6');

        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);

        // Update search parameter
        urlParams.set('search', query);

        // Remove pagination and sorting params for fresh search
        urlParams.delete('page');

        // Make AJAX request
        $.ajax({
            url: '{{ route("places.index") }}',
            type: 'GET',
            data: urlParams.toString(),
            success: function(response) {
                // Update the table content
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(response, 'text/html');
                const newTable = newDoc.querySelector('.table-responsive');
                const newPagination = newDoc.querySelector('.card-footer');

                $('.table-responsive').replaceWith(newTable);
                $('.card-footer').replaceWith(newPagination || '');

                // Update URL without page reload
                const newUrl = '{{ route("places.index") }}' + '?' + urlParams.toString();
                window.history.pushState({}, '', newUrl);
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
                // Show error message
                toastr.error('Search failed. Please try again.');
            },
            complete: function() {
                // Re-enable input
                searchInput.prop('disabled', false);
                searchInput.css('opacity', '1');
            }
        });
    }

    function clearSearch() {
        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);

        // Remove search parameter
        urlParams.delete('search');
        urlParams.delete('page');

        // Redirect to clear search
        const newUrl = '{{ route("places.index") }}' + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.location.href = newUrl;
    }

    // Infinite Scroll Variables
    let currentPage = {{ $places->currentPage() }};
    let isLoading = false;
    let hasMorePages = {{ $places->hasMorePages() ? 'true' : 'false' }};

    // Infinite Scroll Implementation
    $(window).on('scroll', function() {
        if (isLoading || !hasMorePages) return;

        const scrollTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        const documentHeight = $(document).height();

        // Load more when user is 200px from bottom
        if (scrollTop + windowHeight >= documentHeight - 200) {
            loadMorePlaces();
        }
    });

    function loadMorePlaces() {
        if (isLoading || !hasMorePages) return;

        isLoading = true;
        currentPage++;

        // Show loading indicator
        $('#loading-indicator').show();

        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', currentPage);

        $.ajax({
            url: '{{ route("places.index") }}',
            type: 'GET',
            data: urlParams.toString(),
            success: function(response) {
                if (response.places && response.places.length > 0) {
                    // Append new places to table
                    const tbody = $('tbody');
                    response.places.forEach(function(place) {
                        const rowHtml = generatePlaceRow(place);
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

    function generatePlaceRow(place) {
        // Generate HTML for a place row (similar to Blade template)
        let row = '<tr style="height: 45px;">';

        // Name column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<div style="font-size: 0.875rem; font-weight: 600;">' + escapeHtml(place.name.substring(0, 30)) + (place.name.length > 30 ? '...' : '') + '</div>';
        row += '<small class="text-info" style="font-size: 0.75rem;">' + (place.category ? escapeHtml(place.category.substring(0, 25)) + (place.category.length > 25 ? '...' : '') : '-') + '</small>';
        row += '</td>';

        // Address column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<div style="font-size: 0.875rem;">' + (place.address ? escapeHtml(place.address.substring(0, 25)) + (place.address.length > 25 ? '...' : '') : 'N/A') + '</div>';
        row += '</td>';

        // Phone column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        if (place.phone) {
            row += '<a href="https://wa.me/' + place.phone.replace(/\D/g, '') + '" target="_blank" class="btn btn-sm btn-success" title="Chat via WhatsApp">';
            row += '<i class="fab fa-whatsapp"></i> ' + escapeHtml(place.phone.substring(0, 12)) + (place.phone.length > 12 ? '...' : '');
            row += '</a>';
        } else {
            row += '<span style="font-size: 0.875rem;">N/A</span>';
        }
        row += '</td>';

        // Website column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        if (place.website) {
            row += '<a href="' + escapeHtml(place.website) + '" target="_blank" class="btn btn-sm btn-outline-primary">';
            row += '<i class="fas fa-external-link-alt"></i>';
            row += '</a>';
        } else {
            row += '<span style="font-size: 0.875rem;">N/A</span>';
        }
        row += '</td>';

        // Rating column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        if (place.rating) {
            row += '<span class="badge badge-warning" style="font-size: 0.75rem;">';
            row += '<i class="fas fa-star"></i> ' + place.rating;
            row += '</span>';
        } else {
            row += '<span class="badge badge-secondary" style="font-size: 0.75rem;">-</span>';
        }
        row += '</td>';

        // Reviews column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        if (place.review_count) {
            row += '<span class="badge badge-info" style="font-size: 0.75rem;">';
            row += '<i class="fas fa-comments"></i> ' + place.review_count.toLocaleString();
            row += '</span>';
        } else {
            row += '<span class="badge badge-secondary" style="font-size: 0.75rem;">-</span>';
        }
        row += '</td>';

        // Scraped column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        if (place.last_scraped_at) {
            row += '<div style="font-size: 0.875rem; font-weight: 600;">' + new Date(place.last_scraped_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + '</div>';
            row += '<small class="text-muted" style="font-size: 0.75rem;">Just now</small>';
        } else {
            row += '<span class="text-muted" style="font-size: 0.875rem;">Never</span>';
        }
        row += '</td>';

        // Actions column
        row += '<td style="padding: 8px 12px; vertical-align: middle;">';
        row += '<div class="btn-group btn-group-sm">';
        row += '<a href="/places/' + place.id + '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>';
        row += '<a href="/places/' + place.id + '/edit" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>';
        row += '<form method="POST" action="/places/' + place.id + '" class="d-inline">';
        row += '<input type="hidden" name="_method" value="DELETE">';
        row += '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">';
        row += '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')"><i class="fas fa-trash"></i></button>';
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
