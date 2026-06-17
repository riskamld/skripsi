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

    <div class="ml-auto d-flex align-center gap-8">
        @if(request('search') || request('status'))
        <a href="{{ route('scrape-logs.index') }}" class="btn btn-ghost btn-sm">
            <i class="fas fa-times"></i> Reset
        </a>
        @endif
    </div>
</div>

{{-- Stats --}}
<div class="d-flex align-center gap-12 mb-12 text-sm" style="flex-wrap:wrap">
    <span class="badge badge-green"><i class="fas fa-check"></i> Berhasil: {{ number_format($statusCounts['success']) }}</span>
    <span class="badge badge-red"><i class="fas fa-times"></i> Gagal: {{ number_format($statusCounts['failed']) }}</span>
    <span class="badge badge-gray"><i class="fas fa-forward"></i> Dilewati: {{ number_format($statusCounts['skipped']) }}</span>
    <span class="ml-auto text-xs text-muted">Hal {{ $logs->currentPage() }} / {{ $logs->lastPage() }}</span>
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
</div>

{{-- Pagination --}}
@if($logs->hasPages())
<div class="mt-16">{{ $logs->appends(request()->query())->links() }}</div>
@endif

@push('scripts')
<script>
(function(){
    var searchInput = document.getElementById('searchInput');
    var clearBtn    = document.getElementById('clearSearch');
    var statusSel   = document.getElementById('statusFilter');
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
})();
</script>
@endpush
@endsection
