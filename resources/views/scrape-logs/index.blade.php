@extends('layouts.app')
@section('title', 'Log Scraping — Mafaza Fortuna')
@section('page-title', 'Log Scraping')

@push('topbar-actions')
<form method="POST" action="{{ route('scrape-logs.clear-all') }}" id="clearLogsForm" style="display:none">@csrf</form>
<button class="btn btn-danger btn-sm"
    onclick="if(confirm('Hapus semua log? Tidak dapat dibatalkan.')) document.getElementById('clearLogsForm').submit()">
    <i class="fas fa-trash"></i> Hapus Semua
</button>
@endpush

@section('content')

{{-- Stats strip --}}
<div class="grid grid-4 mb-16">
    <div class="metric">
        <div class="metric-icon mi-blue"><i class="fas fa-history"></i></div>
        <div class="metric-label">Total Log</div>
        <div class="metric-value">{{ number_format($statusCounts['total']) }}</div>
    </div>
    <div class="metric">
        <div class="metric-icon mi-green"><i class="fas fa-check"></i></div>
        <div class="metric-label">Berhasil</div>
        <div class="metric-value" style="color:#16a34a">{{ number_format($statusCounts['success']) }}</div>
    </div>
    <div class="metric">
        <div class="metric-icon mi-red"><i class="fas fa-times"></i></div>
        <div class="metric-label">Gagal</div>
        <div class="metric-value" style="color:#dc2626">{{ number_format($statusCounts['failed']) }}</div>
    </div>
    <div class="metric">
        <div class="metric-icon" style="background:#f3f4f6;color:#6b7280"><i class="fas fa-forward"></i></div>
        <div class="metric-label">Dilewati</div>
        <div class="metric-value" style="color:#6b7280">{{ number_format($statusCounts['skipped']) }}</div>
    </div>
</div>

{{-- Success rate + last activity --}}
<div class="card mb-16">
    <div class="card-body" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;padding:14px 18px">
        <div style="flex-shrink:0">
            <div class="text-xs text-muted fw-600" style="text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px">Tingkat Keberhasilan</div>
            <div class="d-flex align-center gap-8">
                <div style="width:120px;height:8px;background:var(--bdr);border-radius:4px;overflow:hidden">
                    <div style="height:100%;border-radius:4px;background:{{ $statusCounts['success_rate'] >= 80 ? '#16a34a' : ($statusCounts['success_rate'] >= 50 ? '#d97706' : '#dc2626') }};width:{{ $statusCounts['success_rate'] }}%"></div>
                </div>
                <span class="fw-700" style="font-size:15px">{{ $statusCounts['success_rate'] }}%</span>
            </div>
        </div>
        <div style="border-left:1px solid var(--bdr);padding-left:20px;flex-shrink:0">
            <div class="text-xs text-muted fw-600" style="text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px">Aktivitas Terakhir</div>
            @if($lastLog)
            <div class="text-sm fw-600" title="{{ $lastLog->created_at->format('d/m/Y H:i:s') }}">{{ $lastLog->created_at->diffForHumans() }}</div>
            @else
            <div class="text-sm text-muted">Belum ada aktivitas</div>
            @endif
        </div>
    </div>
</div>

{{-- Toolbar --}}
<div class="d-flex align-center gap-8 mb-12 flex-wrap">
    <div class="input-group" style="max-width:280px;flex:1;min-width:180px;">
        <input type="text" id="searchInput" class="form-control" placeholder="Cari nama tempat atau pesan error…"
            value="{{ request('search') }}">
        <button class="btn btn-secondary" id="clearSearch"
            style="display:{{ request('search') ? 'flex' : 'none' }}">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <select class="form-control" id="statusFilter" style="width:auto;font-size:12.5px;padding:5px 28px 5px 10px">
        <option value="" {{ !request('status') ? 'selected' : '' }}>Semua Status</option>
        <option value="success" {{ request('status')==='success' ? 'selected' : '' }}>Berhasil</option>
        <option value="failed"  {{ request('status')==='failed'  ? 'selected' : '' }}>Gagal</option>
        <option value="skipped" {{ request('status')==='skipped' ? 'selected' : '' }}>Dilewati</option>
    </select>

    <div class="d-flex align-center gap-4" id="dateRangeWrap"
        style="{{ request('date_from') || request('date_to') ? '' : 'display:none!important' }}">
        <input type="date" id="dateFrom" class="form-control" style="font-size:12px;padding:4px 7px;width:130px" value="{{ request('date_from') }}" title="Dari tanggal">
        <span class="text-xs text-muted">—</span>
        <input type="date" id="dateTo" class="form-control" style="font-size:12px;padding:4px 7px;width:130px" value="{{ request('date_to') }}" title="Sampai tanggal">
    </div>
    <button type="button" class="btn btn-ghost btn-sm" id="dateRangeToggle" title="Filter tanggal"
        style="{{ request('date_from') || request('date_to') ? 'color:var(--ac)' : 'color:var(--tx3)' }}">
        <i class="fas fa-calendar-alt"></i>
    </button>

    <div class="ml-auto d-flex align-center gap-8">
        @if(request('search') || request('status') || request('date_from') || request('date_to'))
        <a href="{{ route('scrape-logs.index') }}" class="btn btn-ghost btn-sm">
            <i class="fas fa-times"></i> Reset
        </a>
        @endif
    </div>
</div>

{{-- Ringkasan hasil --}}
<div class="text-xs text-muted mb-8">
    @if($logs->total() > 0)
    Menampilkan <strong>{{ $logs->firstItem() }}–{{ $logs->lastItem() }}</strong> dari <strong>{{ number_format($logs->total()) }}</strong> log
    @else
    Tidak ada log yang cocok dengan filter ini
    @endif
</div>

{{-- Tabel --}}
<div class="card">
    <div style="overflow-x:auto">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:28%">Nama Tempat</th>
                    <th style="width:12%">Status</th>
                    <th>Pesan</th>
                    <th style="width:14%" class="hide-mobile">Waktu</th>
                    <th style="width:60px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <div class="fw-600 text-sm">{{ Str::limit($log->place->name ?? '(tempat dihapus)', 35) }}</div>
                        @if($log->place)
                        <div class="text-xs text-muted">{{ Str::limit($log->place->category ?? '', 28) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($log->status === 'success')
                            <span class="badge badge-green"><i class="fas fa-check"></i> Berhasil</span>
                        @elseif($log->status === 'failed' || $log->status === 'error')
                            <span class="badge badge-red"><i class="fas fa-times"></i> Gagal</span>
                        @elseif($log->status === 'skipped')
                            <span class="badge badge-gray"><i class="fas fa-forward"></i> Dilewati</span>
                        @else
                            <span class="badge badge-gray">{{ ucfirst($log->status ?? '-') }}</span>
                        @endif
                    </td>
                    <td class="text-sm text-muted" style="max-width:280px">
                        <span title="{{ $log->error_message ?? '' }}">
                            {{ Str::limit($log->error_message ?? '—', 80) }}
                        </span>
                    </td>
                    <td class="hide-mobile text-xs text-muted">
                        <span title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('scrape-logs.show', $log) }}" class="btn btn-ghost btn-xs" title="Lihat detail">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:48px;color:var(--tx3)">
                        <i class="fas fa-history" style="font-size:28px;margin-bottom:8px;display:block"></i>
                        Belum ada log scraping.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->lastPage() > 1)
    <div class="card-footer d-flex align-center justify-between flex-wrap gap-8">
        <span class="text-xs text-muted">{{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ number_format($logs->total()) }}</span>
        <div class="pagination">
            @if($logs->onFirstPage())
                <span class="page-link disabled">‹</span>
            @else
                <a class="page-link" href="{{ $logs->previousPageUrl() }}">‹</a>
            @endif

            @foreach($logs->getUrlRange(max(1, $logs->currentPage()-2), min($logs->lastPage(), $logs->currentPage()+2)) as $page => $url)
                <a class="page-link {{ $page == $logs->currentPage() ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>
            @endforeach

            @if($logs->hasMorePages())
                <a class="page-link" href="{{ $logs->nextPageUrl() }}">›</a>
            @else
                <span class="page-link disabled">›</span>
            @endif
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
(function(){
    var searchInput   = document.getElementById('searchInput');
    var clearBtn      = document.getElementById('clearSearch');
    var statusSel     = document.getElementById('statusFilter');
    var dateWrap      = document.getElementById('dateRangeWrap');
    var dateToggle     = document.getElementById('dateRangeToggle');
    var dateFromEl    = document.getElementById('dateFrom');
    var dateToEl      = document.getElementById('dateTo');
    var searchTimer;

    function buildUrl(params) {
        var u = new URLSearchParams(window.location.search);
        Object.entries(params).forEach(function(kv) {
            if (kv[1] === null) u.delete(kv[0]); else u.set(kv[0], kv[1]);
        });
        u.delete('page');
        return '?' + u.toString();
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        clearBtn.style.display = this.value ? 'flex' : 'none';
        var q = this.value.trim();
        searchTimer = setTimeout(function() {
            window.location.href = buildUrl({ search: q || null });
        }, 400);
    });

    clearBtn.addEventListener('click', function() {
        window.location.href = buildUrl({ search: null });
    });

    statusSel.addEventListener('change', function() {
        window.location.href = buildUrl({ status: this.value || null });
    });

    dateToggle.addEventListener('click', function() {
        var visible = dateWrap.style.display !== 'none';
        dateWrap.style.display = visible ? 'none' : 'flex';
        if (!visible) setTimeout(function(){ dateFromEl.focus(); }, 60);
    });

    function applyDateRange() {
        window.location.href = buildUrl({ date_from: dateFromEl.value || null, date_to: dateToEl.value || null });
    }
    dateFromEl.addEventListener('change', applyDateRange);
    dateToEl.addEventListener('change', applyDateRange);
})();
</script>
@endpush
@endsection
