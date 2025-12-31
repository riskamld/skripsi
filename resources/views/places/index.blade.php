@extends('layouts.app')

@section('page-title', 'Tempat')
@section('page-subtitle', 'Kelola database tempat Anda')

@section('styles')
<style>
/* Category filter styling */
#categoryFilter {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#categoryFilter:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Table row hover effects */
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.75rem;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
}
</style>
@endsection

@section('content')
<!-- Action buttons -->
<div class="row mb-4">
    <div class="col-md-6">
        <a href="{{ route('places.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Tempat Baru
        </a>
        <form method="POST" action="{{ route('places.clear-all') }}" class="d-inline" style="margin-left: 10px;">
            @csrf
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Hapus Semua Tempat
            </button>
        </form>
    </div>
    <div class="col-md-6 text-right">
        <!-- Bulk delete dihapus -->
    </div>
</div>

<!-- Filters -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="form-group mb-0">
            <label for="categoryFilter" class="sr-only">Filter Kategori</label>
            <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-filter"></i></span>
                </div>
                <select class="form-control form-control-sm" id="categoryFilter" name="category">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $categoryData)
                        <option value="{{ $categoryData['name'] }}" {{ request('category') === $categoryData['name'] ? 'selected' : '' }}>
                            {{ $categoryData['name'] }} ({{ $categoryData['count'] }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-8 d-flex align-items-end justify-content-end">
        @if(request('category') || request('search'))
            <div class="text-muted small">
                @if(request('category'))
                    <span class="badge badge-primary mr-2">
                        <i class="fas fa-tag"></i> {{ request('category') }}
                        @if(isset($categories[request('category')]))
                            ({{ $categories[request('category')]['count'] }})
                        @endif
                    </span>
                @endif
                @if(request('search'))
                    <span class="badge badge-info">
                        <i class="fas fa-search"></i> "{{ request('search') }}"
                    </span>
                @endif
                <a href="{{ route('places.index') }}" class="btn btn-outline-secondary btn-sm ml-2">
                    <i class="fas fa-times"></i> Hapus Filter
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Places table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Semua Tempat</h3>

        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari tempat (min 4 karakter)" value="{{ request('search') }}">
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
                    <th style="width: 35%;">Nama</th>
                    <th style="width: 12%;">Alamat</th>
                    <th style="width: 8%;">Telepon</th>
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
                            Ulasan
                            @if(request('sort') === 'review_count')
                                <i class="fas fa-sort-{{ request('direction') === 'desc' ? 'down' : 'up' }}"></i>
                            @else
                                <i class="fas fa-sort text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width: 10%;">
                        <a href="{{ route('places.index', array_merge(request()->query(), ['sort' => 'last_scraped_at', 'direction' => (request('sort') === 'last_scraped_at' && request('direction') === 'desc') ? 'asc' : 'desc'])) }}" class="text-decoration-none">
                            Di-scrap
                            @if(request('sort') === 'last_scraped_at')
                                <i class="fas fa-sort-{{ request('direction') === 'desc' ? 'down' : 'up' }}"></i>
                            @else
                                <i class="fas fa-sort text-muted"></i>
                            @endif
                        </a>
                    </th>
                    <th style="width: 15%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($places ?? [] as $place)
                <tr style="height: 45px;" data-place-id="{{ $place->id }}">
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
                            <span class="text-muted" style="font-size: 0.875rem;"></span>
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
                                <button type="submit" class="btn btn-danger btn-sm">
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
                            <h5>Tidak ada tempat ditemukan</h5>
                            <p style="font-size: 0.875rem;">Belum ada data tempat yang sesuai dengan filter Anda.</p>
                            <a href="{{ route('places.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Tempat Baru
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
            <span class="sr-only"></span>
        </div>
        <small class="text-muted ml-2"></small>
    </div>

    <!-- End of Results Indicator -->
    <div id="end-indicator" class="text-center py-3" style="display: none;">
        <small class="text-muted"></small>
    </div>
</div>
<!-- /.card -->

<script>
$(document).ready(function() {
    let searchTimeout;
    const searchInput = $('#searchInput');
    const clearSearchBtn = $('#clearSearch');

    // Initialize clear button visibility on page load
    clearSearchBtn.toggle(searchInput.val().length > 0);

    // Real-time search with 2+ characters (reduced from 4 for better UX)
    searchInput.on('input', function() {
        const query = $(this).val().trim();

        // Show/hide clear button
        clearSearchBtn.toggle(query.length > 0);

        // Clear previous timeout
        clearTimeout(searchTimeout);

        if (query.length >= 2) {
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
        // Simple redirect approach - more reliable than AJAX
        const urlParams = new URLSearchParams(window.location.search);

        // Update search parameter
        urlParams.set('search', query);

        // Remove pagination for fresh search
        urlParams.delete('page');

        // Redirect to the search URL
        const searchUrl = '{{ route("places.index") }}' + '?' + urlParams.toString();
        window.location.href = searchUrl;
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

    // Infinite Scroll Variables - only when paginated
    let currentPage = {{ method_exists($places, 'currentPage') ? $places->currentPage() : 1 }};
    let isLoading = false;
    let hasMorePages = {{ method_exists($places, 'hasMorePages') ? ($places->hasMorePages() ? 'true' : 'false') : 'false' }};

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

    // Infinite scroll functions removed - no longer needed with filter changes

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Category filter functionality
    $('#categoryFilter').on('change', function() {
        const category = $(this).val();
        const urlParams = new URLSearchParams(window.location.search);

        if (category) {
            urlParams.set('category', category);
        } else {
            urlParams.delete('category');
        }

        // Remove pagination for fresh filter
        urlParams.delete('page');

        const filterUrl = '{{ route("places.index") }}' + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.location.href = filterUrl;
    });
});
</script>
@endsection
