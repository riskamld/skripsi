@extends('layouts.app')
@section('title', 'Buat Token API — Mafaza Fortuna')
@section('page-title', 'Buat Token API')

@section('content')

<div class="mb-16">
    <a href="{{ route('api-tokens.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Token API</a>
</div>

<form method="POST" action="{{ route('api-tokens.store') }}" style="max-width:560px">
@csrf
<div class="card">
    <div class="card-header"><i class="fas fa-plus" style="color:var(--ac);margin-right:6px"></i>Buat Token API Baru</div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label" for="name">Nama Token *</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="cth: Mobile App, Web Scraper, dll" required>
            @error('name')<div class="text-xs" style="color:var(--rd);margin-top:3px">{{ $message }}</div>@enderror
            <div class="text-xs text-muted mt-4">Beri nama deskriptif untuk mengidentifikasi tujuan token ini</div>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <span><strong>Penting:</strong> Setelah token dibuat, pastikan untuk menyalin dan menyimpan nilainya. Token hanya ditampilkan sekali demi keamanan.</span>
        </div>
    </div>
    <div class="card-footer d-flex gap-8">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Buat Token</button>
        <a href="{{ route('api-tokens.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Batal</a>
    </div>
</div>
</form>

@endsection
