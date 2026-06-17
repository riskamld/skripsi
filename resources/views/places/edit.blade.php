@extends('layouts.app')
@section('title', 'Edit Tempat — Mafaza Fortuna')
@section('page-title', 'Edit Tempat')

@section('content')

<div class="mb-16 d-flex gap-8">
    <a href="{{ route('places.show', $place) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Lihat Tempat</a>
    <a href="{{ route('places.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<form method="POST" action="{{ route('places.update', $place) }}">
@csrf
@method('PUT')

<div class="card mb-16">
    <div class="card-header"><i class="fas fa-edit" style="color:var(--ac);margin-right:6px"></i>Edit: {{ $place->name }}</div>
    <div class="card-body">
        <div class="grid grid-2" id="grid-basic">
            <div>
                <div class="fw-700 text-sm mb-12" style="color:var(--ac)"><i class="fas fa-info-circle"></i> Informasi Dasar</div>

                <div class="form-group">
                    <label class="form-label" for="name">Nama Tempat *</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $place->name) }}" required>
                    @error('name')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="category">Kategori</label>
                    <input type="text" class="form-control" id="category" name="category" value="{{ old('category', $place->category) }}">
                    @error('category')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="address">Alamat *</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address', $place->address) }}</textarea>
                    @error('address')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $place->phone) }}">
                    @error('phone')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="website">Website</label>
                    <input type="url" class="form-control" id="website" name="website" value="{{ old('website', $place->website) }}">
                    @error('website')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
            </div>

            <div>
                <div class="fw-700 text-sm mb-12" style="color:var(--gn)"><i class="fas fa-chart-line"></i> Detail Tambahan</div>

                <div class="form-group">
                    <label class="form-label" for="rating">Rating</label>
                    <input type="number" step="0.1" min="0" max="5" class="form-control" id="rating" name="rating" value="{{ old('rating', $place->rating) }}">
                    @error('rating')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="review_count">Jumlah Ulasan</label>
                    <input type="number" class="form-control" id="review_count" name="review_count" value="{{ old('review_count', $place->review_count) }}">
                    @error('review_count')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="opening_hours">Jam Buka</label>
                    <textarea class="form-control" id="opening_hours" name="opening_hours" rows="3">{{ old('opening_hours', $place->opening_hours) }}</textarea>
                    @error('opening_hours')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="maps_url">URL Google Maps</label>
                    <input type="url" class="form-control" id="maps_url" name="maps_url" value="{{ old('maps_url', $place->maps_url) }}">
                    @error('maps_url')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="source">Sumber</label>
                    <input type="text" class="form-control" id="source" name="source" value="{{ old('source', $place->source) }}">
                    @error('source')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="grid grid-2 mt-12">
            <div class="form-group">
                <label class="form-label" for="lat">Latitude</label>
                <input type="number" step="any" class="form-control" id="lat" name="lat" value="{{ old('lat', $place->lat) }}">
                @error('lat')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="lng">Longitude</label>
                <input type="number" step="any" class="form-control" id="lng" name="lng" value="{{ old('lng', $place->lng) }}">
                @error('lng')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="fw-700 text-sm mb-12 mt-12" style="color:#0891b2"><i class="fas fa-images"></i> Gambar</div>
        <div class="grid grid-4" id="grid-images">
            @for($i = 1; $i <= 4; $i++)
            <div class="form-group">
                <label class="form-label" for="image_{{ $i }}">Gambar {{ $i }}</label>
                <input type="url" class="form-control" id="image_{{ $i }}" name="image_{{ $i }}" value="{{ old('image_' . $i, $place->{'image_' . $i}) }}">
                @error('image_' . $i)<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
            </div>
            @endfor
        </div>

        <div class="grid grid-2 mt-12">
            <div class="form-group">
                <label class="text-sm d-flex align-center gap-6" style="cursor:pointer">
                    <input type="checkbox" id="is_valid" name="is_valid" value="1" {{ old('is_valid', $place->is_valid) ? 'checked' : '' }}>
                    Valid / Relevan
                </label>
            </div>
            <div class="form-group">
                <label class="form-label" for="parser_version">Versi Parser</label>
                <input type="text" class="form-control" id="parser_version" name="parser_version" value="{{ old('parser_version', $place->parser_version) }}">
                @error('parser_version')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer d-flex gap-8">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Update Tempat</button>
        <a href="{{ route('places.show', $place) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Batal</a>
    </div>
</div>
</form>

@endsection

@push('styles')
<style>
@media(max-width:768px){ #grid-basic,#grid-images{grid-template-columns:1fr!important} }
@media(max-width:1024px){ #grid-images{grid-template-columns:repeat(2,1fr)!important} }
</style>
@endpush
