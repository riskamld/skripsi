@extends('layouts.app')
@section('title', 'Analisis K-Means — Mafaza Fortuna')
@section('page-title', 'Analisis K-Means')

@push('topbar-actions')
<form method="POST" action="{{ route('kmeans.run') }}">
    @csrf
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="fas fa-rotate"></i> Jalankan Analisis
    </button>
</form>
@endpush

@section('content')

@if (session('success'))
<div class="card" style="border-color:var(--gn);margin-bottom:14px">
    <div class="card-body" style="color:var(--gn)">{{ session('success') }}</div>
</div>
@endif

@if (session('error'))
<div class="card" style="border-color:var(--rd);margin-bottom:14px">
    <div class="card-body" style="color:var(--rd)">{{ session('error') }}</div>
</div>
@endif

<div class="card" style="margin-bottom:14px">
    <div class="card-body" style="font-size:12.5px;color:var(--tx2)">
        Mengelompokkan tempat (agen/toko buah) ke dalam <strong>3 cluster</strong> potensi kemitraan
        berdasarkan rating (X1), jumlah review (X2), latitude (X3), dan longitude (X4) — menggunakan
        Min-Max Normalization dan Euclidean Distance (K-Means, Lloyd's algorithm).
        @if($lastComputedAt)
            Terakhir dihitung: <strong>{{ \Carbon\Carbon::parse($lastComputedAt)->format('d M Y H:i') }}</strong>.
        @endif
        Data memenuhi syarat (rating, review, koordinat lengkap): <strong>{{ $eligibleCount }}</strong> tempat.
    </div>
</div>

<div class="metrics-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px">
    @forelse($summary as $label => $data)
        @php
            $color = match($label) { 'Tinggi' => 'green', 'Sedang' => 'orange', 'Rendah' => 'red', default => 'gray' };
        @endphp
        <div class="metric">
            <div class="metric-icon mi-{{ $color === 'gray' ? 'blue' : $color }}">
                <i class="fas fa-circle-nodes"></i>
            </div>
            <div class="metric-label">Potensi {{ $label }}</div>
            <div class="metric-value">{{ $data['count'] }} <small>tempat</small></div>
            <div style="font-size:11.5px;color:var(--tx3);margin-top:6px">
                Rating rata-rata {{ $data['avg_rating'] }} &middot; Review rata-rata {{ $data['avg_review_count'] }}
            </div>
        </div>
    @empty
        <div class="card"><div class="card-body" style="color:var(--tx2)">
            Belum ada hasil analisis. Klik "Jalankan Analisis" untuk memulai clustering.
        </div></div>
    @endforelse
</div>

@if($evaluation)
<div class="card" style="margin-bottom:14px">
    <div class="card-header">
        <span>Evaluasi Model — Elbow Method &amp; Silhouette Score</span>
        <span style="font-size:12px;color:var(--tx2)">Dihitung: {{ \Carbon\Carbon::parse($evaluation['computed_at'])->format('d M Y H:i') }}</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start">
            <div>
                <div style="font-size:12.5px;color:var(--tx2);margin-bottom:8px">
                    WCSS (Within-Cluster Sum of Squares) per nilai k — titik siku grafik menunjukkan k optimal.
                </div>
                <table>
                    <thead><tr><th>k</th>@foreach($evaluation['elbow'] as $k => $w)<th>{{ $k }}</th>@endforeach</tr></thead>
                    <tbody><tr><td>WCSS</td>@foreach($evaluation['elbow'] as $k => $w)<td>{{ number_format($w, 4) }}</td>@endforeach</tr></tbody>
                </table>
            </div>
            <div>
                <div style="font-size:12.5px;color:var(--tx2);margin-bottom:8px">
                    Silhouette Score rata-rata untuk k = {{ $evaluation['k'] }} (rentang -1 s.d. 1, mendekati 1 = kualitas cluster baik).
                </div>
                <div class="metric-value" style="font-size:28px">{{ $evaluation['silhouette'] }}</div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <span>Hasil Clustering</span>
        <span style="font-size:12px;color:var(--tx2)">{{ $places->count() }} tempat</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Potensi</th>
                    <th>Rating</th>
                    <th>Jumlah Review</th>
                    <th class="hide-mobile">Area</th>
                    <th class="hide-mobile">Koordinat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($places as $place)
                @php
                    $badgeColor = match($place->cluster_label) { 'Tinggi' => 'green', 'Sedang' => 'orange', 'Rendah' => 'red', default => 'gray' };
                @endphp
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px">{{ Str::limit($place->name, 40) }}</div>
                    </td>
                    <td><span class="badge badge-{{ $badgeColor }}">{{ $place->cluster_label }}</span></td>
                    <td>{{ $place->rating }}</td>
                    <td>{{ number_format($place->review_count) }}</td>
                    <td class="hide-mobile">{{ Str::limit($place->address, 35) }}</td>
                    <td class="hide-mobile" style="font-size:11.5px;color:var(--tx3)">{{ $place->lat }}, {{ $place->lng }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--tx2);padding:24px">
                    Belum ada hasil. Jalankan analisis terlebih dahulu.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
