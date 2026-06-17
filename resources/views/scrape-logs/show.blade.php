@extends('layouts.app')
@section('title', 'Detail Log Scraping — Mafaza Fortuna')
@section('page-title', 'Detail Log Scraping')

@section('content')

<div class="mb-16">
    <a href="{{ route('scrape-logs.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Log</a>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr" id="grid-log">

    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card">
            <div class="card-header"><i class="fas fa-file-alt" style="color:var(--ac);margin-right:6px"></i>Detail Log Scraping</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                <div class="d-flex justify-between text-sm"><span class="text-muted">ID Log</span><span class="fw-600">{{ $scrapeLog->id }}</span></div>
                <div class="d-flex justify-between text-sm align-center">
                    <span class="text-muted">Tempat</span>
                    @if($scrapeLog->place)
                    <a href="{{ route('places.show', $scrapeLog->place) }}" class="fw-600">{{ $scrapeLog->place->name }}</a>
                    @else
                    <span class="text-muted">Tempat tidak ditemukan</span>
                    @endif
                </div>
                <div class="d-flex justify-between text-sm align-center">
                    <span class="text-muted">Status</span>
                    @if($scrapeLog->status === 'success')
                    <span class="badge badge-green"><i class="fas fa-check"></i> Sukses</span>
                    @elseif($scrapeLog->status === 'error')
                    <span class="badge badge-red"><i class="fas fa-times"></i> Error</span>
                    @elseif($scrapeLog->status === 'warning')
                    <span class="badge badge-orange"><i class="fas fa-exclamation-triangle"></i> Peringatan</span>
                    @else
                    <span class="badge badge-gray"><i class="fas fa-clock"></i> {{ ucfirst($scrapeLog->status ?? 'pending') }}</span>
                    @endif
                </div>
                <div class="d-flex justify-between text-sm"><span class="text-muted">Dibuat</span><span>{{ $scrapeLog->created_at->format('d M Y \\p\\u\\k\\u\\l H:i:s') }}</span></div>
                <div class="d-flex justify-between text-sm"><span class="text-muted">Diperbarui</span><span>{{ $scrapeLog->updated_at->format('d M Y \\p\\u\\k\\u\\l H:i:s') }}</span></div>
            </div>
        </div>

        @if($scrapeLog->error_message)
        <div class="card">
            <div class="card-header"><i class="fas fa-exclamation-triangle" style="color:var(--rd);margin-right:6px"></i>Pesan Error</div>
            <div class="card-body">
                <div class="alert alert-danger" style="align-items:flex-start">
                    <pre style="margin:0;white-space:pre-wrap;font-size:12px">{{ $scrapeLog->error_message }}</pre>
                </div>
            </div>
        </div>
        @endif

        @if($scrapeLog->raw_payload)
        <div class="card">
            <div class="card-header"><i class="fas fa-code" style="color:var(--ac);margin-right:6px"></i>Raw Payload</div>
            <div class="card-body">
                <pre style="background:var(--bg);padding:12px;border-radius:6px;font-size:11.5px;overflow-x:auto"><code>{{ json_encode($scrapeLog->raw_payload, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
        @endif
    </div>

    <div style="display:flex;flex-direction:column;gap:14px">
        @if($scrapeLog->place)
        <div class="card">
            <div class="card-header"><i class="fas fa-map-marker-alt" style="color:var(--ac);margin-right:6px"></i>Informasi Tempat</div>
            <div class="card-body">
                <div class="text-center mb-8 fw-700">{{ $scrapeLog->place->name }}</div>
                <div class="text-xs text-muted text-center mb-12">{{ Str::limit($scrapeLog->place->address, 100) }}</div>

                @if($scrapeLog->place->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $scrapeLog->place->phone) }}" target="_blank" class="btn btn-success btn-sm w-100 mb-8">
                    <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                </a>
                @endif
                <a href="{{ route('places.show', $scrapeLog->place) }}" class="btn btn-primary btn-sm w-100"><i class="fas fa-eye"></i> Lihat Detail Lengkap</a>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><i class="fas fa-chart-bar" style="color:var(--ac);margin-right:6px"></i>Statistik Log</div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="metric">
                        <div class="metric-label">Sejak Dibuat</div>
                        <div class="metric-value" style="font-size:15px">{{ $scrapeLog->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Waktu Proses</div>
                        <div class="metric-value">{{ $scrapeLog->updated_at->diffInSeconds($scrapeLog->created_at) }}<small>s</small></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-cogs" style="color:var(--ac);margin-right:6px"></i>Aksi</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px">
                <button class="btn btn-warning btn-sm w-100" onclick="window.print()"><i class="fas fa-print"></i> Cetak Log</button>
                <button class="btn btn-info btn-sm w-100" onclick="copyLogDetails()"><i class="fas fa-copy"></i> Salin Detail</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@media(max-width:1024px){ #grid-log{grid-template-columns:1fr!important} }
</style>
@endpush

@push('scripts')
<script>
function copyLogDetails() {
    const text = `Log ID: {{ $scrapeLog->id }}\nStatus: {{ $scrapeLog->status }}\nTempat: {{ $scrapeLog->place ? $scrapeLog->place->name : 'N/A' }}\nDibuat: {{ $scrapeLog->created_at }}`;
    navigator.clipboard.writeText(text).then(function() {
        alert('Detail log disalin ke clipboard!');
    });
}
</script>
@endpush
