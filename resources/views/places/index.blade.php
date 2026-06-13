@extends('layouts.app')
@section('title', 'Data Tempat — Mafaza Fortuna')
@section('page-title', 'Data Tempat')

@push('topbar-actions')
<a href="{{ route('scraper.index') }}" class="btn btn-primary btn-sm">
    <i class="fas fa-robot"></i> Scraping
</a>
<a href="{{ route('places.create') }}" class="btn btn-secondary btn-sm">
    <i class="fas fa-plus"></i> Tambah
</a>
@endpush

@section('content')

{{-- Toolbar --}}
<div class="d-flex align-center gap-8 mb-16 flex-wrap">
    {{-- Search --}}
    <div class="input-group" style="max-width:280px;flex:1;min-width:180px;">
        <input type="text" id="searchInput" class="form-control" placeholder="Cari nama, kategori, alamat…" value="{{ request('search') }}">
        <button class="btn btn-secondary" id="clearSearch" style="display:{{ request('search') ? 'flex' : 'none' }}">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Category filter toggle --}}
    <button class="btn btn-secondary btn-sm" id="filterToggle">
        <i class="fas fa-filter"></i> Kategori
        @if(request('categories'))
            <span class="badge badge-blue" style="margin-left:4px;font-size:10px;">
                {{ count((array)request('categories')) }}
            </span>
        @endif
    </button>

    {{-- Sort --}}
    <select class="form-control" id="sortSelect" style="width:auto;flex-shrink:0;font-size:12.5px;padding:5px 28px 5px 10px;">
        <option value="created_at|desc" {{ request('sort','created_at')=='created_at'&&request('direction','desc')=='desc'?'selected':'' }}>Terbaru</option>
        <option value="busyness_score|desc" {{ request('sort')=='busyness_score'&&request('direction','desc')=='desc'?'selected':'' }}>Score ↓</option>
        <option value="rating|desc" {{ request('sort')=='rating'&&request('direction','desc')=='desc'?'selected':'' }}>Rating ↓</option>
        <option value="review_count|desc" {{ request('sort')=='review_count'&&request('direction','desc')=='desc'?'selected':'' }}>Ulasan ↓</option>
        <option value="last_scraped_at|desc" {{ request('sort')=='last_scraped_at'&&request('direction','desc')=='desc'?'selected':'' }}>Diperbarui</option>
        <option value="name|asc" {{ request('sort')=='name'&&request('direction','asc')=='asc'?'selected':'' }}>Nama A–Z</option>
    </select>

    <div class="ml-auto d-flex align-center gap-8">
        @if(request('categories') || request('search') || request('qf'))
        <a href="{{ route('places.index') }}" class="btn btn-ghost btn-sm">
            <i class="fas fa-times"></i> Reset
        </a>
        @endif

        <form method="POST" action="{{ route('places.clear-all') }}" id="clearAllForm" style="display:none">@csrf</form>
        <button class="btn btn-danger btn-sm" onclick="if(confirm('Hapus semua tempat? Tidak dapat dibatalkan.')) document.getElementById('clearAllForm').submit()">
            <i class="fas fa-trash"></i> Hapus Semua
        </button>
    </div>
</div>

{{-- Quick filter chips --}}
@php $qf = request('qf',''); @endphp
<div class="d-flex align-center gap-6 mb-12 flex-wrap" style="font-size:12.5px;">
    <span class="text-muted" style="font-size:11px;font-weight:600;letter-spacing:.4px;white-space:nowrap;">FILTER CEPAT</span>
    @foreach([
        ['' ,        'Semua',                                           'btn-secondary'],
        ['wa',       '<i class="fab fa-whatsapp"></i> Punya WA',        'btn-success'],
        ['target',   '<i class="fas fa-bullseye"></i> Target',          'btn-primary'],
        ['unsent',   '<i class="fas fa-paper-plane"></i> Belum Kirim',  'btn-info'],
        ['sent',     '<i class="fas fa-check"></i> Sudah Kirim',        'btn-warning'],
        ['replied',  '<i class="fas fa-reply"></i> Ada Respon',         'btn-orange'],
    ] as [$val, $label, $cls])
    @php $active = ($qf === $val); @endphp
    <a href="{{ route('places.index', array_merge(request()->except(['qf','page']), $val ? ['qf'=>$val] : [])) }}"
       class="btn btn-sm {{ $active ? $cls : 'btn-outline' }}">{!! $label !!}</a>
    @endforeach
</div>

{{-- Category filter panel --}}
<div class="card mb-16" id="filterPanel" style="display:none">
    <div class="card-body" style="padding:12px 16px;">
        <div style="display:flex;flex-wrap:wrap;gap:6px 12px;margin-bottom:10px;">
            @foreach($categories as $cat)
            <label style="display:flex;align-items:center;gap:5px;font-size:12.5px;cursor:pointer;">
                <input type="checkbox" class="cat-cb" value="{{ $cat['name'] }}"
                    {{ in_array($cat['name'], (array)request('categories',[])) ? 'checked' : '' }}>
                {{ $cat['name'] }}
                <span class="badge badge-gray">{{ $cat['count'] }}</span>
            </label>
            @endforeach
        </div>
        <div class="d-flex gap-6">
            <button class="btn btn-secondary btn-sm" id="selAll">Pilih semua</button>
            <button class="btn btn-ghost btn-sm" id="clrAll">Hapus pilihan</button>
            <button class="btn btn-primary btn-sm" id="applyFilter">Terapkan</button>
        </div>
    </div>
</div>

{{-- Stats row --}}
<div class="d-flex align-center gap-12 mb-12 text-sm text-muted" style="flex-wrap:wrap">
    <span><strong>{{ $places->total() }}</strong> tempat</span>
    @if(request('search'))
    <span>· hasil pencarian "<strong>{{ request('search') }}</strong>"</span>
    @endif
    @if(request('categories'))
    <span>· {{ count((array)request('categories')) }} kategori</span>
    @endif
    <span class="ml-auto text-xs">Hal {{ $places->currentPage() }} / {{ $places->lastPage() }}</span>
</div>

{{-- Bulk bar --}}
<form method="POST" action="{{ route('places.bulk-delete') }}" id="bulkForm">
@csrf
<div class="card mb-8" id="bulkBar" style="display:none">
    <div class="card-body d-flex align-center gap-8" style="padding:8px 14px;">
        <span class="text-sm fw-600" id="bulkCount">0 dipilih</span>
        <button type="submit" class="btn btn-danger btn-sm"
            onclick="return confirm('Hapus yang dipilih?')">
            <i class="fas fa-trash"></i> Hapus
        </button>
        <button type="button" class="btn btn-ghost btn-sm" id="deselAll">Batal pilih</button>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:36px;text-align:center;">
                        <input type="checkbox" id="selectAll" style="cursor:pointer">
                    </th>
                    <th>Nama / Kategori</th>
                    <th class="hide-mobile">Alamat</th>
                    <th>Telepon</th>
                    <th class="hide-mobile">Rating</th>
                    <th class="hide-mobile">Ulasan</th>
                    <th class="hide-mobile">Score</th>
                    <th class="hide-mobile">Jam Ramai</th>
                    <th class="hide-mobile">WA</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody id="placesBody">
                @forelse($places as $place)
                <tr>
                    <td style="text-align:center">
                        <input type="checkbox" class="row-cb" name="ids[]" value="{{ $place->id }}">
                    </td>
                    <td>
                        <div class="fw-600" style="font-size:13px">{{ Str::limit($place->name, 32) }}</div>
                        <div class="text-muted text-xs mt-4">{{ $place->category ? Str::limit($place->category, 26) : '—' }}</div>
                    </td>
                    <td class="hide-mobile text-muted text-sm">{{ $place->address ? Str::limit($place->address, 28) : '—' }}</td>
                    <td>
                        @if($place->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $place->phone) }}"
                           target="_blank" class="btn btn-success btn-xs" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i> {{ Str::limit($place->phone, 13) }}
                        </a>
                        @else
                        <span class="text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td class="hide-mobile">
                        @if($place->rating)
                        <span class="badge badge-yellow"><i class="fas fa-star"></i> {{ $place->rating }}</span>
                        @else
                        <span class="text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td class="hide-mobile">
                        @if($place->review_count)
                        <span class="text-sm">{{ number_format($place->review_count) }}</span>
                        @else
                        <span class="text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td class="hide-mobile">
                        @if($place->busyness_score)
                        <span class="fw-600 text-sm">{{ number_format($place->busyness_score, 0) }}</span>
                        @else
                        <span class="text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td class="hide-mobile">
                        @php
                            $pt    = $place->popular_times;
                            $days  = ['sun','mon','tue','wed','thu','fri','sat'];
                            $busy  = $place->busiestSlot();
                            $dayLbl= ['sun'=>'Min','mon'=>'Sen','tue'=>'Sel','wed'=>'Rab','thu'=>'Kam','fri'=>'Jum','sat'=>'Sab'];
                        @endphp
                        @if($pt)
                        <div style="display:flex;flex-direction:column;gap:3px">
                            {{-- 7 mini bar Sen-Min --}}
                            <div style="display:flex;gap:2px;align-items:flex-end;height:20px">
                                @foreach($days as $d)
                                @php $peak = $pt[$d] ? max($pt[$d]) : 0; @endphp
                                <div title="{{ $dayLbl[$d] }}: {{ $peak }}%"
                                     style="width:6px;border-radius:2px 2px 0 0;
                                            height:{{ max(2, round($peak * 20 / 100)) }}px;
                                            background:{{ $peak >= 70 ? 'var(--rd)' : ($peak >= 40 ? 'var(--or)' : 'var(--ac)') }};
                                            opacity:{{ $peak > 0 ? 1 : 0.2 }}"></div>
                                @endforeach
                            </div>
                            {{-- Label puncak --}}
                            @if($busy)
                            <div style="font-size:10px;color:var(--tx3);white-space:nowrap">
                                {{ $dayLbl[$busy['day']] }} {{ str_pad($busy['hour'],2,'0',STR_PAD_LEFT) }}:00
                            </div>
                            @endif
                        </div>
                        @else
                        <span class="text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td class="hide-mobile">
                        @if($place->has_whatsapp === true)
                        <span class="badge badge-green"><i class="fab fa-whatsapp"></i></span>
                        @elseif($place->has_whatsapp === false)
                        <span class="badge badge-red">Tidak</span>
                        @else
                        <span class="badge badge-gray">?</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-4 justify-content:flex-end" style="justify-content:flex-end">
                            <a href="{{ route('places.show', $place) }}" class="btn btn-ghost btn-xs" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('places.edit', $place) }}" class="btn btn-ghost btn-xs" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('places.destroy', $place) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--rd)"
                                    onclick="return confirm('Hapus tempat ini?')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:48px;color:var(--tx3)">
                        <i class="fas fa-store" style="font-size:28px;margin-bottom:8px;display:block"></i>
                        Tidak ada tempat ditemukan.<br>
                        <a href="{{ route('scraper.index') }}" class="btn btn-primary btn-sm mt-8">
                            <i class="fas fa-robot"></i> Mulai scraping
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($places->lastPage() > 1)
    <div class="card-footer d-flex align-center justify-between flex-wrap gap-8">
        <span class="text-xs text-muted">{{ $places->firstItem() }}–{{ $places->lastItem() }} dari {{ $places->total() }}</span>
        <div class="pagination">
            @if($places->onFirstPage())
                <span class="page-link disabled">‹</span>
            @else
                <a class="page-link" href="{{ $places->previousPageUrl() }}">‹</a>
            @endif

            @foreach($places->getUrlRange(max(1, $places->currentPage()-2), min($places->lastPage(), $places->currentPage()+2)) as $page => $url)
                <a class="page-link {{ $page == $places->currentPage() ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>
            @endforeach

            @if($places->hasMorePages())
                <a class="page-link" href="{{ $places->nextPageUrl() }}">›</a>
            @else
                <span class="page-link disabled">›</span>
            @endif
        </div>
    </div>
    @endif
</div>
</form>

@push('scripts')
<script>
(function(){
// Search debounce
var searchTimeout;
var searchInput = document.getElementById('searchInput');
var clearBtn = document.getElementById('clearSearch');

function buildUrl(params){
    var u = new URLSearchParams(window.location.search);
    Object.entries(params).forEach(function(kv){
        if(kv[1]===null) u.delete(kv[0]); else u.set(kv[0], kv[1]);
    });
    u.delete('page');
    return '{{ route("places.index") }}?' + u.toString();
}

searchInput.addEventListener('input', function(){
    clearTimeout(searchTimeout);
    var q = this.value.trim();
    clearBtn.style.display = q ? 'flex' : 'none';
    if(q.length >= 2){
        searchTimeout = setTimeout(function(){ window.location.href = buildUrl({search: q}); }, 400);
    } else if(q.length === 0){
        window.location.href = buildUrl({search: null});
    }
});
clearBtn.addEventListener('click', function(){
    searchInput.value=''; clearBtn.style.display='none';
    window.location.href = buildUrl({search: null});
});

// Sort
document.getElementById('sortSelect').addEventListener('change', function(){
    var parts = this.value.split('|');
    window.location.href = buildUrl({sort: parts[0], direction: parts[1]});
});

// Category filter panel
var filterPanel = document.getElementById('filterPanel');
var filterToggle = document.getElementById('filterToggle');
@if(request('categories'))
filterPanel.style.display = 'block';
@endif

filterToggle.addEventListener('click', function(){
    filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('selAll').addEventListener('click', function(){
    document.querySelectorAll('.cat-cb').forEach(function(cb){ cb.checked = true; });
});
document.getElementById('clrAll').addEventListener('click', function(){
    document.querySelectorAll('.cat-cb').forEach(function(cb){ cb.checked = false; });
});
document.getElementById('applyFilter').addEventListener('click', function(){
    var vals = Array.from(document.querySelectorAll('.cat-cb:checked')).map(function(cb){ return cb.value; });
    var u = new URLSearchParams(window.location.search);
    u.delete('categories[]'); u.delete('categories');
    vals.forEach(function(v){ u.append('categories[]', v); });
    u.delete('page');
    window.location.href = '{{ route("places.index") }}?' + u.toString();
});

// Bulk select
var selectAll = document.getElementById('selectAll');
var bulkBar = document.getElementById('bulkBar');
var bulkCount = document.getElementById('bulkCount');

function updateBulk(){
    var cbs = document.querySelectorAll('.row-cb');
    var checked = document.querySelectorAll('.row-cb:checked');
    var n = checked.length;
    bulkBar.style.display = n > 0 ? 'block' : 'none';
    bulkCount.textContent = n + ' dipilih';
    selectAll.checked = n === cbs.length && cbs.length > 0;
    selectAll.indeterminate = n > 0 && n < cbs.length;
}

selectAll.addEventListener('change', function(){
    document.querySelectorAll('.row-cb').forEach(function(cb){ cb.checked = selectAll.checked; });
    updateBulk();
});
document.getElementById('placesBody').addEventListener('change', function(e){
    if(e.target.classList.contains('row-cb')) updateBulk();
});
document.getElementById('deselAll').addEventListener('click', function(){
    document.querySelectorAll('.row-cb').forEach(function(cb){ cb.checked=false; });
    updateBulk();
});
})();
</script>
@endpush

@endsection
