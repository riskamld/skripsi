@extends('layouts.app')
@section('title', 'Dasbor — Mafaza Fortuna')
@section('page-title', 'Dasbor')

@push('topbar-actions')
<a href="{{ route('scraper.index') }}" class="btn btn-primary btn-sm">
    <i class="fas fa-robot"></i> Scraping
</a>
@endpush

@section('content')

{{-- Alert bar --}}
@if($stats['wa_unchecked'] > 0)
<div class="alert alert-warning mb-16" style="display:flex;align-items:center;gap:10px">
    <i class="fas fa-exclamation-triangle"></i>
    <span><strong>{{ number_format($stats['wa_unchecked']) }} nomor</strong> belum dicek WA.
    <a href="{{ route('whatsapp.index') }}" style="color:inherit;font-weight:600;text-decoration:underline">Cek sekarang →</a></span>
</div>
@endif
@if($stats['responded'] > 0)
<div class="alert alert-success mb-16" style="display:flex;align-items:center;gap:10px">
    <i class="fab fa-whatsapp"></i>
    <span><strong>{{ $stats['responded'] }} toko</strong> sudah membalas pesan outreach.</span>
    <a href="{{ route('whatsapp.index') }}" class="btn btn-sm btn-success" style="margin-left:auto">Lihat →</a>
</div>
@endif
@if($stats['today'] > 0)
<div class="alert mb-16" style="display:flex;align-items:center;gap:10px;background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.25);color:var(--tx);border-radius:8px;padding:10px 14px">
    <i class="fas fa-plus-circle" style="color:#3b82f6"></i>
    <span>Hari ini ditambahkan <strong>{{ $stats['today'] }} tempat baru</strong>.</span>
    <a href="{{ route('places.index') }}" class="btn btn-sm btn-secondary" style="margin-left:auto">Lihat →</a>
</div>
@endif

{{-- Funnel Prospecting --}}
<div class="card mb-20">
    <div class="card-header"><span><i class="fas fa-filter" style="color:var(--ac);margin-right:6px"></i>Funnel Prospecting</span></div>
    <div class="card-body" style="padding:20px 24px">
        @php
        $funnel = [
            ['label' => 'Total Tempat Scraped',   'val' => $stats['total'],         'color' => '#6b7280', 'icon' => 'fa-store',       'href' => route('places.index')],
            ['label' => 'Kategori Relevan',        'val' => $stats['relevant'],      'color' => '#3b82f6', 'icon' => 'fa-tag',         'href' => route('places.index')],
            ['label' => 'Ramai (Score >50)',        'val' => $stats['high_score'],    'color' => '#8b5cf6', 'icon' => 'fa-fire',        'href' => route('places.index', ['sort'=>'busyness_score','direction'=>'desc'])],
            ['label' => 'Punya WA Aktif',          'val' => $stats['has_wa'],        'color' => '#16a34a', 'icon' => 'fa-whatsapp',    'href' => route('whatsapp.index')],
            ['label' => 'Sudah Dikirim Outreach',  'val' => $stats['outreach_sent'], 'color' => '#f59e0b', 'icon' => 'fa-paper-plane', 'href' => route('whatsapp.index')],
            ['label' => 'Respon / Tertarik',       'val' => $stats['responded'],     'color' => '#10b981', 'icon' => 'fa-handshake',   'href' => route('whatsapp.index')],
        ];
        $max = max(array_column($funnel, 'val')) ?: 1;
        @endphp
        <div style="display:flex;flex-direction:column;gap:10px">
            @foreach($funnel as $f)
            <a href="{{ $f['href'] }}" style="text-decoration:none;color:inherit">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:22px;text-align:center;flex-shrink:0">
                    <i class="fas {{ $f['icon'] }}" style="color:{{ $f['color'] }};font-size:12px"></i>
                </div>
                <div style="width:190px;font-size:12px;font-weight:500;color:var(--tx2);flex-shrink:0">{{ $f['label'] }}</div>
                <div style="flex:1;height:18px;background:var(--bdr);border-radius:4px;overflow:hidden">
                    <div style="height:100%;width:{{ $max > 0 ? round(($f['val']/$max)*100) : 0 }}%;background:{{ $f['color'] }};border-radius:4px;opacity:.75"></div>
                </div>
                <div style="width:50px;text-align:right;font-size:14px;font-weight:700;color:{{ $f['color'] }};flex-shrink:0">{{ number_format($f['val']) }}</div>
            </div>
            </a>
            @endforeach
        </div>
        @php
        $pctWa   = $stats['total'] > 0 ? round($stats['has_wa']/$stats['total']*100, 1) : 0;
        $pctResp = $stats['outreach_sent'] > 0 ? round($stats['responded']/$stats['outreach_sent']*100, 1) : 0;
        @endphp
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--bdr);display:flex;gap:24px;flex-wrap:wrap">
            <span class="text-xs text-muted">Konversi scrape→WA: <strong>{{ $pctWa }}%</strong></span>
            <span class="text-xs text-muted">Konversi outreach→respon: <strong>{{ $pctResp > 0 ? $pctResp.'%' : '—' }}</strong></span>
            <span class="text-xs text-muted">Belum dicek WA: <strong style="color:var(--or)">{{ number_format($stats['wa_unchecked']) }}</strong></span>
        </div>
    </div>
</div>

{{-- Main content --}}
<div class="grid mb-16" style="grid-template-columns:1fr 260px;gap:16px">

    {{-- Top places by score --}}
    <div class="card">
        <div class="card-header">
            <span>Prospek Terbaik (Busyness Score)</span>
            <a href="{{ route('places.index', ['sort'=>'busyness_score','direction'=>'desc']) }}"
               class="btn btn-secondary btn-sm">Semua →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama & Kategori</th>
                        <th>Score</th>
                        <th>WA</th>
                        <th>Outreach</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['recent_places'] as $p)
                    <tr onclick="location.href='{{ route('places.show', $p->id) }}'" style="cursor:pointer">
                        <td>
                            <div class="fw-600" style="font-size:13px">{{ Str::limit($p->name, 30) }}</div>
                            <div class="text-muted text-xs mt-2">{{ Str::limit($p->category ?? '—', 24) }}</div>
                        </td>
                        <td>
                            <span class="fw-600">{{ number_format($p->busyness_score, 0) }}</span>
                            <div style="height:3px;border-radius:2px;background:var(--bdr);margin-top:3px;width:60px">
                                <div style="height:3px;border-radius:2px;background:var(--ac);width:{{ min(($p->busyness_score/200)*100,100) }}%"></div>
                            </div>
                        </td>
                        <td>
                            @if($p->has_whatsapp === true)
                                <span class="badge badge-green"><i class="fab fa-whatsapp"></i></span>
                            @elseif($p->has_whatsapp === false)
                                <span class="badge badge-red">✗</span>
                            @else
                                <span class="badge badge-gray">?</span>
                            @endif
                        </td>
                        <td>
                            @if($p->outreach_status === 'responded')
                                <span class="badge badge-green">Respon</span>
                            @elseif($p->outreach_status === 'sent')
                                <span class="badge badge-blue">Terkirim</span>
                            @else
                                <span class="text-muted text-xs">—</span>
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

    {{-- Category breakdown --}}
    <div class="card">
        <div class="card-header"><span>Kategori Terbanyak</span></div>
        <div class="card-body">
            @php $max = $stats['top_categories']->max('count') ?: 1; @endphp
            @forelse($stats['top_categories'] as $cat)
            <div class="mb-10">
                <div class="d-flex justify-between mb-3">
                    <span style="font-size:12px;font-weight:500">{{ Str::limit($cat->category, 20) }}</span>
                    <span class="text-muted text-xs fw-600">{{ number_format($cat->count) }}</span>
                </div>
                <div style="height:4px;border-radius:2px;background:var(--bdr)">
                    <div style="height:4px;border-radius:2px;background:var(--ac);width:{{ ($cat->count/$max)*100 }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-muted text-center text-sm">Belum ada data.</p>
            @endforelse
            <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--bdr)">
                <div class="text-xs text-muted">Total kategori unik</div>
                <div style="font-size:18px;font-weight:700;color:var(--tx)">
                    {{ \App\Models\Place::whereNotNull('category')->distinct('category')->count('category') }}
                </div>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
@media(max-width:768px){
    .grid[style*="1fr 260px"]{grid-template-columns:1fr!important}
    [style*="width:190px"]{width:110px!important}
}
</style>
@endpush

@endsection
