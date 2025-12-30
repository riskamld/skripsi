@extends('layouts.app')

@section('page-title', 'Edit Product Price')
@section('page-subtitle', 'Edit product price data')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Product Price
                </h3>
                <div class="card-tools">
                    <a href="{{ route('product-prices.show', $productPrice) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye mr-1"></i>
                        View Details
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('product-prices.update', $productPrice) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_name">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror"
                                       id="product_name" name="product_name"
                                       value="{{ old('product_name', $productPrice->product_name) }}" required
                                       list="productSuggestions">
                                <datalist id="productSuggestions">
                                    @foreach($productNames as $product)
                                    <option value="{{ $product }}">
                                    @endforeach
                                </datalist>
                                @error('product_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_category">Product Category</label>
                                <input type="text" class="form-control @error('product_category') is-invalid @enderror"
                                       id="product_category" name="product_category"
                                       value="{{ old('product_category', $productPrice->product_category) }}"
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
                                       value="{{ old('price', $productPrice->price) }}" required>
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
                                       value="{{ old('original_price', $productPrice->original_price) }}">
                                @error('original_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit">Unit</label>
                                <select class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit">
                                    <option value="pcs" {{ old('unit', $productPrice->unit) === 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                    <option value="kg" {{ old('unit', $productPrice->unit) === 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                    <option value="liter" {{ old('unit', $productPrice->unit) === 'liter' ? 'selected' : '' }}>Liter (liter)</option>
                                    <option value="pack" {{ old('unit', $productPrice->unit) === 'pack' ? 'selected' : '' }}>Pack (pack)</option>
                                    <option value="box" {{ old('unit', $productPrice->unit) === 'box' ? 'selected' : '' }}>Box (box)</option>
                                    <option value="other" {{ old('unit', $productPrice->unit) === 'other' ? 'selected' : '' }}>Other</option>
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
                                    <option value="manual" {{ old('source', $productPrice->source) === 'manual' ? 'selected' : '' }}>Manual Entry</option>
                                    <option value="scraped" {{ old('source', $productPrice->source) === 'scraped' ? 'selected' : '' }}>Web Scraping</option>
                                    <option value="estimated" {{ old('source', $productPrice->source) === 'estimated' ? 'selected' : '' }}>Estimated</option>
                                    <option value="api" {{ old('source', $productPrice->source) === 'api' ? 'selected' : '' }}>API Import</option>
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
                            <option value="{{ $place->id }}" {{ old('place_id', $productPrice->place_id) == $place->id ? 'selected' : '' }}>
                                {{ $place->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('place_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="recorded_at">Recorded At</label>
                        <input type="datetime-local" class="form-control @error('recorded_at') is-invalid @enderror"
                               id="recorded_at" name="recorded_at"
                               value="{{ old('recorded_at', $productPrice->recorded_at->format('Y-m-d\TH:i')) }}">
                        @error('recorded_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confidence_score">Confidence Score</label>
                                <input type="number" class="form-control @error('confidence_score') is-invalid @enderror"
                                       id="confidence_score" name="confidence_score"
                                       min="0" max="1" step="0.01"
                                       value="{{ old('confidence_score', $productPrice->confidence_score) }}">
                                @error('confidence_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="supply_index">Supply Index</label>
                                <input type="number" class="form-control @error('supply_index') is-invalid @enderror"
                                       id="supply_index" name="supply_index"
                                       min="0" max="100"
                                       value="{{ old('supply_index', $productPrice->supply_index) }}">
                                @error('supply_index')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="demand_index">Demand Index</label>
                                <input type="number" class="form-control @error('demand_index') is-invalid @enderror"
                                       id="demand_index" name="demand_index"
                                       min="0" max="100"
                                       value="{{ old('demand_index', $productPrice->demand_index) }}">
                                @error('demand_index')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="season">Season</label>
                                <select class="form-control @error('season') is-invalid @enderror" id="season" name="season">
                                    <option value="" {{ old('season', $productPrice->season) === '' ? 'selected' : '' }}>Not Specified</option>
                                    <option value="peak" {{ old('season', $productPrice->season) === 'peak' ? 'selected' : '' }}>Peak Season</option>
                                    <option value="normal" {{ old('season', $productPrice->season) === 'normal' ? 'selected' : '' }}>Normal Season</option>
                                    <option value="low" {{ old('season', $productPrice->season) === 'low' ? 'selected' : '' }}>Low Season</option>
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
                                           {{ old('is_holiday_season', $productPrice->is_holiday_season) ? 'checked' : '' }}>
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
                                  id="notes" name="notes" rows="3">{{ old('notes', $productPrice->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Update Product Price
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
                    Current Data
                </h3>
            </div>
            <div class="card-body">
                <dl class="row small">
                    <dt class="col-sm-5">ID:</dt>
                    <dd class="col-sm-7">{{ $productPrice->id }}</dd>

                    <dt class="col-sm-5">Created:</dt>
                    <dd class="col-sm-7">{{ $productPrice->created_at->format('d/m/Y H:i') }}</dd>

                    <dt class="col-sm-5">Updated:</dt>
                    <dd class="col-sm-7">{{ $productPrice->updated_at->format('d/m/Y H:i') }}</dd>

                    <dt class="col-sm-5">Place:</dt>
                    <dd class="col-sm-7">{{ $productPrice->place->name ?? 'N/A' }}</dd>
                </dl>
            </div>
        </div>

        @if($relatedPrices->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    Recent Prices for "{{ $productPrice->product_name }}"
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($relatedPrices->take(5) as $relatedPrice)
                    <a href="{{ route('product-prices.show', $relatedPrice) }}" class="list-group-item list-group-item-action px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Rp {{ number_format($relatedPrice->price, 0, ',', '.') }}</strong>
                                <small class="text-muted d-block">{{ $relatedPrice->place->name ?? 'N/A' }}</small>
                            </div>
                            <small class="text-muted">{{ $relatedPrice->recorded_at->format('d/m H:i') }}</small>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
$(document).ready(function() {
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
