@extends('layouts.app')
@section('title', 'Dasbor — Mafaza Fortuna')
@section('page-title', 'Dasbor')

@push('topbar-actions')
<a href="{{ route('scraper.index') }}" class="btn btn-primary btn-sm">
    <i class="fas fa-robot"></i> Scraping
</a>
@endpush

@section('content')

{{-- Metrics --}}
<div class="grid grid-4 mb-20">
    <div class="metric">
        <div class="metric-icon mi-blue"><i class="fas fa-store"></i></div>
        <div class="metric-label">Total Tempat</div>
        <div class="metric-value">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="metric">
        <div class="metric-icon mi-green"><i class="fab fa-whatsapp"></i></div>
        <div class="metric-label">Punya WhatsApp</div>
        <div class="metric-value">{{ number_format($stats['has_wa']) }}
            <small>/ {{ number_format($stats['has_phone']) }}</small>
        </div>
    </div>
    <div class="metric">
        <div class="metric-icon mi-orange"><i class="fas fa-crosshairs"></i></div>
        <div class="metric-label">Target Aktif</div>
        <div class="metric-value">{{ number_format($stats['is_target']) }}</div>
    </div>
    <div class="metric">
        <div class="metric-icon mi-red"><i class="fas fa-fire"></i></div>
        <div class="metric-label">Score &gt;50</div>
        <div class="metric-value">{{ number_format($stats['high_score']) }}</div>
    </div>
</div>

{{-- Alerts --}}
@if($stats['wa_unchecked'] > 0)
<div class="alert alert-warning mb-16">
    <i class="fas fa-exclamation-triangle"></i>
    <span><strong>{{ number_format($stats['wa_unchecked']) }} nomor</strong> belum dicek WA.
    Jalankan: <code>php artisan places:check-wa --limit=100</code></span>
</div>
@endif
@if($stats['today'] > 0)
<div class="alert alert-success mb-16">
    <i class="fas fa-check-circle"></i>
    <span>Hari ini ditambahkan <strong>{{ $stats['today'] }} tempat baru</strong>.</span>
    <a href="{{ route('places.index') }}" class="btn btn-sm btn-success ml-auto">Lihat →</a>
</div>
@endif

{{-- Main content --}}
<div class="grid mb-16" style="grid-template-columns:1fr 280px;gap:16px">

    {{-- Top places table --}}
    <div class="card">
        <div class="card-header">
            <span>Busyness Score Tertinggi</span>
            <a href="{{ route('places.index', ['sort'=>'busyness_score','direction'=>'desc']) }}"
               class="btn btn-secondary btn-sm">Lihat semua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama Tempat</th>
                        <th>Kategori</th>
                        <th>Score</th>
                        <th>WhatsApp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['recent_places'] as $p)
                    <tr>
                        <td>
                            <div class="fw-600" style="font-size:13px">{{ Str::limit($p->name, 32) }}</div>
                            <div class="text-muted text-xs mt-4">{{ $p->phone }}</div>
                        </td>
                        <td class="text-muted text-sm">{{ Str::limit($p->category ?? '—', 22) }}</td>
                        <td>
                            <span class="fw-600">{{ number_format($p->busyness_score, 0) }}</span>
                            <div style="height:3px;border-radius:2px;background:var(--bdr);margin-top:4px;width:80px">
                                <div style="height:3px;border-radius:2px;background:var(--ac);width:{{ min(($p->busyness_score/200)*100,100) }}%"></div>
                            </div>
                        </td>
                        <td>
                            @if($p->has_whatsapp === true)
                                <span class="badge badge-green"><i class="fab fa-whatsapp"></i> Ada</span>
                            @elseif($p->has_whatsapp === false)
                                <span class="badge badge-red">Tidak</span>
                            @else
                                <span class="badge badge-gray">?</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--tx3)">
                        Belum ada data. <a href="{{ route('scraper.index') }}">Mulai scraping →</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Category bar chart --}}
    <div class="card">
        <div class="card-header"><span>Kategori Terbanyak</span></div>
        <div class="card-body">
            @php $max = $stats['top_categories']->max('count') ?: 1; @endphp
            @forelse($stats['top_categories'] as $cat)
            <div class="mb-12">
                <div class="d-flex justify-between mb-4">
                    <span style="font-size:12px;font-weight:500">{{ Str::limit($cat->category, 22) }}</span>
                    <span class="text-muted text-xs">{{ number_format($cat->count) }}</span>
                </div>
                <div style="height:4px;border-radius:2px;background:var(--bdr)">
                    <div style="height:4px;border-radius:2px;background:var(--ac);width:{{ ($cat->count/$max)*100 }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-muted text-center text-sm">Belum ada data.</p>
            @endforelse
        </div>
    </div>

</div>

@push('styles')
<style>
@media(max-width:768px){
    .grid[style*="grid-template-columns:1fr 280px"]{grid-template-columns:1fr!important}
}
</style>
@endpush

@endsection
