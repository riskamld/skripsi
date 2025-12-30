@extends('layouts.app')

@section('page-title', 'Product Prices')
@section('page-subtitle', 'Manage product price data for AI predictions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-coins mr-2"></i>
                    Product Prices Management
                </h3>
                <div class="card-tools">
                    <a href="{{ route('product-prices.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i>
                        Add Price
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#clearAllModal">
                        <i class="fas fa-trash-alt mr-1"></i>
                        Clear All
                    </button>
                </div>
            </div>

            <!-- Search and Filter Form -->
            <div class="card-header border-0">
                <form method="GET" action="{{ route('product-prices.index') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products, places..." value="{{ request('search') }}">
                    </div>

                    <div class="form-group mr-3">
                        <select name="product_name" class="form-control form-control-sm">
                            <option value="">All Products</option>
                            @foreach($productNames as $product)
                            <option value="{{ $product }}" {{ request('product_name') === $product ? 'selected' : '' }}>
                                {{ $product }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mr-3">
                        <select name="place_id" class="form-control form-control-sm">
                            <option value="">All Places</option>
                            @foreach($places as $place)
                            <option value="{{ $place->id }}" {{ request('place_id') == $place->id ? 'selected' : '' }}>
                                {{ $place->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mr-3">
                        <select name="source" class="form-control form-control-sm">
                            <option value="">All Sources</option>
                            @foreach($sources as $sourceOption)
                            <option value="{{ $sourceOption }}" {{ request('source') === $sourceOption ? 'selected' : '' }}>
                                {{ ucfirst($sourceOption) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mr-3">
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="From Date">
                    </div>

                    <div class="form-group mr-3">
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="To Date">
                    </div>

                    <button type="submit" class="btn btn-outline-primary btn-sm mr-2">
                        <i class="fas fa-search"></i> Filter
                    </button>

                    <a href="{{ route('product-prices.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="card-header border-0">
                <form id="bulkActionForm" method="POST" action="{{ route('product-prices.bulk-delete') }}">
                    @csrf
                    <div class="form-inline">
                        <div class="form-check mr-3">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                            <label class="form-check-label" for="selectAll">Select All</label>
                        </div>

                        <button type="submit" class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn" disabled>
                            <i class="fas fa-trash mr-1"></i>
                            Delete Selected (0)
                        </button>
                    </div>
                </form>
            </div>

            <div class="card-body table-responsive p-0">
                @if($prices->count() > 0)
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAllTop">
                            </th>
                            <th>
                                <a href="{{ route('product-prices.index', array_merge(request()->query(), ['sort' => 'product_name', 'direction' => request('sort') === 'product_name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                    Product Name
                                    @if(request('sort') === 'product_name')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Category</th>
                            <th>
                                <a href="{{ route('product-prices.index', array_merge(request()->query(), ['sort' => 'price', 'direction' => request('sort') === 'price' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                    Price
                                    @if(request('sort') === 'price')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Unit</th>
                            <th>Place</th>
                            <th>
                                <a href="{{ route('product-prices.index', array_merge(request()->query(), ['sort' => 'source', 'direction' => request('sort') === 'source' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                    Source
                                    @if(request('sort') === 'source')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('product-prices.index', array_merge(request()->query(), ['sort' => 'recorded_at', 'direction' => request('sort') === 'recorded_at' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                    Recorded At
                                    @if(request('sort') === 'recorded_at')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prices as $price)
                        <tr>
                            <td>
                                <input type="checkbox" class="row-checkbox" name="ids[]" value="{{ $price->id }}" form="bulkActionForm">
                            </td>
                            <td>
                                <strong>{{ $price->product_name }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $price->product_category ?: 'N/A' }}</span>
                            </td>
                            <td>
                                <strong class="text-primary">Rp {{ number_format($price->price, 0, ',', '.') }}</strong>
                                @if($price->original_price)
                                    <br><small class="text-muted"><s>Rp {{ number_format($price->original_price, 0, ',', '.') }}</s></small>
                                @endif
                            </td>
                            <td>{{ $price->unit }}</td>
                            <td>
                                <small>{{ $price->place->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-{{ $price->source === 'manual' ? 'primary' : ($price->source === 'scraped' ? 'success' : 'info') }}">
                                    {{ ucfirst($price->source) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $price->recorded_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product-prices.show', $price) }}" class="btn btn-outline-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('product-prices.edit', $price) }}" class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('product-prices.destroy', $price) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this price record?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-coins fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No Product Prices Found</h4>
                    <p class="text-muted mb-4">
                        Start by adding product price data to enable AI forecasting and analysis.
                    </p>
                    <a href="{{ route('product-prices.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Add First Price
                    </a>
                </div>
                @endif
            </div>

            @if($prices->hasPages())
            <div class="card-footer">
                {{ $prices->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Clear All Modal -->
<div class="modal fade" id="clearAllModal" tabindex="-1" role="dialog" aria-labelledby="clearAllModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearAllModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                    Clear All Product Prices
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete all product price records? This action cannot be undone.</p>
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This will permanently delete {{ $prices->total() }} product price records and affect AI predictions.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('product-prices.clear-all') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Yes, Clear All
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Select All functionality
    $('#selectAll, #selectAllTop').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkDeleteButton();
    });

    $('.row-checkbox').on('change', function() {
        var allChecked = $('.row-checkbox:checked').length === $('.row-checkbox').length;
        $('#selectAll, #selectAllTop').prop('checked', allChecked);
        updateBulkDeleteButton();
    });

    function updateBulkDeleteButton() {
        var selectedCount = $('.row-checkbox:checked').length;
        var button = $('#bulkDeleteBtn');
        button.prop('disabled', selectedCount === 0);
        button.html('<i class="fas fa-trash mr-1"></i> Delete Selected (' + selectedCount + ')');
    }

    // Show success/error messages
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error('{{ $error }}');
        @endforeach
    @endif
});
</script>
@endsection
