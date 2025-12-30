@extends('layouts.app')

@section('page-title', 'Add Product Price')
@section('page-subtitle', 'Add new product price data for AI analysis')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Product Price
                </h3>
            </div>

            <form method="POST" action="{{ route('product-prices.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_name">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror"
                                       id="product_name" name="product_name"
                                       value="{{ old('product_name') }}" required
                                       list="productSuggestions">
                                <datalist id="productSuggestions">
                                    @foreach($productNames as $product)
                                    <option value="{{ $product }}">
                                    @endforeach
                                </datalist>
                                @error('product_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">e.g., Mangga, Beras Premium, Ayam</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_category">Product Category</label>
                                <input type="text" class="form-control @error('product_category') is-invalid @enderror"
                                       id="product_category" name="product_category"
                                       value="{{ old('product_category') }}"
                                       list="categorySuggestions">
                                <datalist id="categorySuggestions">
                                    <option value="Buah-buahan">
                                    <option value="Sayuran">
                                    <option value="Beras & Sereal">
                                    <option value="Daging & Protein">
                                    <option value="Bumbu Dapur">
                                    <option value="Minuman">
                                    <option value="Produk Susu">
                                </datalist>
                                @error('product_category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Price (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror"
                                       id="price" name="price" min="0" step="0.01"
                                       value="{{ old('price') }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="original_price">Original Price (Rp)</label>
                                <input type="number" class="form-control @error('original_price') is-invalid @enderror"
                                       id="original_price" name="original_price" min="0" step="0.01"
                                       value="{{ old('original_price') }}">
                                @error('original_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">For discounted products</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit">Unit</label>
                                <select class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit">
                                    <option value="pcs" {{ old('unit', 'pcs') === 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                    <option value="kg" {{ old('unit') === 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                    <option value="liter" {{ old('unit') === 'liter' ? 'selected' : '' }}>Liter (liter)</option>
                                    <option value="pack" {{ old('unit') === 'pack' ? 'selected' : '' }}>Pack (pack)</option>
                                    <option value="box" {{ old('unit') === 'box' ? 'selected' : '' }}>Box (box)</option>
                                    <option value="other" {{ old('unit') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="source">Source <span class="text-danger">*</span></label>
                                <select class="form-control @error('source') is-invalid @enderror" id="source" name="source" required>
                                    <option value="manual" {{ old('source', 'manual') === 'manual' ? 'selected' : '' }}>Manual Entry</option>
                                    <option value="scraped" {{ old('source') === 'scraped' ? 'selected' : '' }}>Web Scraping</option>
                                    <option value="estimated" {{ old('source') === 'estimated' ? 'selected' : '' }}>Estimated</option>
                                    <option value="api" {{ old('source') === 'api' ? 'selected' : '' }}>API Import</option>
                                </select>
                                @error('source')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="place_id">Place <span class="text-danger">*</span></label>
                        <select class="form-control @error('place_id') is-invalid @enderror"
                                id="place_id" name="place_id" required>
                            <option value="">Select Place</option>
                            @foreach($places as $place)
                            <option value="{{ $place->id }}" {{ old('place_id') == $place->id ? 'selected' : '' }}>
                                {{ $place->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('place_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Where this price was recorded</small>
                    </div>

                    <div class="form-group">
                        <label for="recorded_at">Recorded At</label>
                        <input type="datetime-local" class="form-control @error('recorded_at') is-invalid @enderror"
                               id="recorded_at" name="recorded_at"
                               value="{{ old('recorded_at', now()->format('Y-m-d\TH:i')) }}">
                        @error('recorded_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">When this price was recorded. Leave empty for current time.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confidence_score">Confidence Score</label>
                                <input type="number" class="form-control @error('confidence_score') is-invalid @enderror"
                                       id="confidence_score" name="confidence_score"
                                       min="0" max="1" step="0.01"
                                       value="{{ old('confidence_score', '1.00') }}">
                                @error('confidence_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">0.00 to 1.00 (how reliable is this data)</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="supply_index">Supply Index</label>
                                <input type="number" class="form-control @error('supply_index') is-invalid @enderror"
                                       id="supply_index" name="supply_index"
                                       min="0" max="100"
                                       value="{{ old('supply_index') }}">
                                @error('supply_index')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">0-100 scale</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="demand_index">Demand Index</label>
                                <input type="number" class="form-control @error('demand_index') is-invalid @enderror"
                                       id="demand_index" name="demand_index"
                                       min="0" max="100"
                                       value="{{ old('demand_index') }}">
                                @error('demand_index')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">0-100 scale</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="season">Season</label>
                                <select class="form-control @error('season') is-invalid @enderror" id="season" name="season">
                                    <option value="" {{ old('season') === '' ? 'selected' : '' }}>Not Specified</option>
                                    <option value="peak" {{ old('season') === 'peak' ? 'selected' : '' }}>Peak Season</option>
                                    <option value="normal" {{ old('season') === 'normal' ? 'selected' : '' }}>Normal Season</option>
                                    <option value="low" {{ old('season') === 'low' ? 'selected' : '' }}>Low Season</option>
                                </select>
                                @error('season')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_holiday_season">Holiday Season</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_holiday_season" name="is_holiday_season" value="1"
                                           {{ old('is_holiday_season') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_holiday_season">
                                        This price is from holiday season
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3"
                                  placeholder="Additional notes about this price record...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Save Product Price
                    </button>
                    <a href="{{ route('product-prices.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Information
                </h3>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-lightbulb text-warning mr-2"></i>AI Impact</h6>
                <p class="text-muted small mb-3">
                    Each price record you add improves the accuracy of our AI price prediction system. More data = better predictions!
                </p>

                <h6><i class="fas fa-chart-line text-primary mr-2"></i>Prediction Requirements</h6>
                <ul class="list-unstyled small mb-3">
                    <li><i class="fas fa-check text-success mr-1"></i> At least 2 records per product</li>
                    <li><i class="fas fa-check text-success mr-1"></i> Consistent pricing over time</li>
                    <li><i class="fas fa-check text-success mr-1"></i> Accurate place and date information</li>
                </ul>

                <h6><i class="fas fa-database text-info mr-2"></i>Field Explanations</h6>
                <dl class="small">
                    <dt>Confidence Score</dt>
                    <dd class="text-muted">How reliable this price data is (0.00-1.00)</dd>

                    <dt>Supply/Demand Index</dt>
                    <dd class="text-muted">Market conditions (0-100 scale)</dd>

                    <dt>Season</dt>
                    <dd class="text-muted">Affects seasonal price variations</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Current Statistics
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="h4 text-primary mb-0">{{ $places->count() }}</div>
                    <small class="text-muted">Available Places</small>
                </div>
                <hr>
                <div class="text-center">
                    <div class="h4 text-success mb-0">{{ $productNames->count() }}</div>
                    <small class="text-muted">Unique Products</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-fill category based on product name
    $('#product_name').on('input', function() {
        var productName = $(this).val().toLowerCase();
        var categoryField = $('#product_category');

        if (productName.includes('mangga') || productName.includes('apel') || productName.includes('jeruk') ||
            productName.includes('pisang') || productName.includes('durian') || productName.includes('rambutan')) {
            if (!categoryField.val()) categoryField.val('Buah-buahan');
        } else if (productName.includes('beras') || productName.includes('jagung') || productName.includes('kedelai')) {
            if (!categoryField.val()) categoryField.val('Beras & Sereal');
        } else if (productName.includes('ayam') || productName.includes('daging') || productName.includes('ikan')) {
            if (!categoryField.val()) categoryField.val('Daging & Protein');
        }
    });

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
