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
        <option value="pt_peak|desc" {{ request('sort')=='pt_peak'?'selected':'' }}>Ada Jam Ramai</option>
        <option value="priority|desc" {{ request('sort')=='priority'?'selected':'' }}>Prioritas Tertinggi</option>
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
        ['' ,          'Semua',                                              'btn-secondary'],
        ['unchecked',  '<i class="fas fa-question-circle"></i> Belum Cek WA','btn-secondary'],
        ['wa',         '<i class="fab fa-whatsapp"></i> Punya WA',           'btn-success'],
        ['no_wa',      '<i class="fas fa-times-circle"></i> Tidak Ada WA',   'btn-danger'],
        ['has_pt',     '<i class="fas fa-chart-bar"></i> Ada Jam Ramai',      'btn-primary'],
        ['target',     '<i class="fas fa-bullseye"></i> Target',             'btn-primary'],
        ['unsent',          '<i class="fas fa-paper-plane"></i> Belum Kirim',     'btn-info'],
        ['sent',            '<i class="fas fa-check"></i> Sudah Kirim',           'btn-warning'],
        ['replied',         '<i class="fas fa-reply"></i> Ada Respon',            'btn-orange'],
        ['interested',      '<i class="fas fa-thumbs-up"></i> Berminat',          'btn-success'],
        ['not_interested',  '<i class="fas fa-thumbs-down"></i> Tidak Berminat',  'btn-danger'],
        ['ordered',         '<i class="fas fa-shopping-cart"></i> Sudah Order',   'btn-primary'],
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
                        <div style="display:flex;align-items:center;gap:7px">
                            @if($place->image_1)
                            @php
                                $imgThumb = preg_replace('/=w\d+-h\d+[^"]*$/', '=w48-h48-k-no', $place->image_1);
                                $imgBig   = preg_replace('/=w\d+-h\d+[^"]*$/', '=w400-h300-k-no', $place->image_1);
                                // dedup by hash (sebelum =w) agar badge tidak hitung foto duplikat
                                $imgCount = collect([$place->image_1,$place->image_2,$place->image_3,$place->image_4])
                                    ->filter()->unique(fn($u) => explode('=', $u)[0])->count();
                            @endphp
                            <div class="img-thumb-wrap" data-img="{{ $imgBig }}"
                                 data-imgs="{{ implode('|', array_filter([$place->image_1,$place->image_2,$place->image_3,$place->image_4])) }}"
                                 style="position:relative;flex-shrink:0;cursor:zoom-in">
                                <img src="{{ $imgThumb }}" loading="lazy"
                                     style="width:36px;height:36px;border-radius:5px;object-fit:cover;border:1px solid var(--bdr);display:block">
                                @if($imgCount > 1)
                                <span style="position:absolute;bottom:-3px;right:-3px;background:#334155;color:#fff;
                                      font-size:9px;font-weight:700;border-radius:3px;padding:1px 3px;line-height:1">
                                    {{ $imgCount }}
                                </span>
                                @endif
                            </div>
                            @endif
                            <div>
                                <div class="fw-600" style="font-size:13px">{{ Str::limit($place->name, 30) }}</div>
                                <div class="text-muted text-xs mt-4">{{ $place->category ? Str::limit($place->category, 24) : '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="hide-mobile text-muted text-sm" title="{{ $place->address }}">{{ $place->address ? Str::limit($place->address, 45) : '—' }}</td>
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
                    <td class="hide-mobile pt-cell"
                        @if($place->popular_times)
                        data-pt="{{ json_encode($place->popular_times) }}"
                        data-name="{{ addslashes($place->name) }}"
                        @endif>
                        @php
                            $pt    = $place->popular_times;
                            $days  = ['sun','mon','tue','wed','thu','fri','sat'];
                            $busy  = $place->busiestSlot();
                            $dayLbl= ['sun'=>'Min','mon'=>'Sen','tue'=>'Sel','wed'=>'Rab','thu'=>'Kam','fri'=>'Jum','sat'=>'Sab'];
                        @endphp
                        @if($pt && count($pt) > 0)
                        <div style="display:flex;flex-direction:column;gap:3px;cursor:default">
                            <div style="display:flex;gap:2px;align-items:flex-end;height:20px">
                                @foreach($days as $d)
                                @php $peak = isset($pt[$d]) ? max($pt[$d]) : 0; @endphp
                                <div style="width:6px;border-radius:2px 2px 0 0;
                                            height:{{ max(2, round($peak * 20 / 100)) }}px;
                                            background:{{ $peak >= 70 ? '#ef4444' : ($peak >= 40 ? '#f97316' : '#3b82f6') }};
                                            opacity:{{ $peak > 0 ? 1 : 0.15 }}"></div>
                                @endforeach
                            </div>
                            @if($busy)
                            <div style="font-size:10px;color:var(--tx3);white-space:nowrap">
                                {{ $dayLbl[$busy['day']] }}
                                {{ str_pad($busy['hour'],2,'0',STR_PAD_LEFT) }}:00
                            </div>
                            @endif
                        </div>
                        @elseif(is_array($pt))
                        <span style="font-size:10px;color:#6b7280;background:#f3f4f6;border:1px solid #d1d5db;border-radius:4px;padding:1px 5px;white-space:nowrap">Tidak Ada</span>
                        @else
                        <span style="font-size:10px;color:#92400e;background:#fef3c7;border:1px solid #fcd34d;border-radius:4px;padding:1px 5px;white-space:nowrap">Belum</span>
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
                        <div class="d-flex gap-4 justify-content:flex-end" style="justify-content:flex-end;flex-wrap:wrap">
                            @if($place->has_whatsapp === true && $place->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $place->phone) }}"
                               target="_blank" class="btn btn-xs" title="Buka Chat WA"
                               style="background:#22c55e;color:#fff;border-color:#22c55e">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            @endif
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

// ── Image hover preview ──────────────────────────────────────────────────────
(function(){
  const preview = document.createElement('div');
  preview.style.cssText = 'display:none;position:fixed;z-index:9999;pointer-events:auto;'
    + 'background:#fff;border:1px solid var(--bdr);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.18);'
    + 'padding:6px;max-width:300px;user-select:none';
  document.body.appendChild(preview);

  let currentImgs = [], currentIdx = 0, hideTimer, isOver = false;

  function showPreview(el, e) {
    const seen = new Set();
    const imgs = (el.dataset.imgs || '').split('|').filter(Boolean).reduce(function(acc, u){
      const hash = u.split('=')[0];
      if (!seen.has(hash)) { seen.add(hash); acc.push(u.replace(/=w\d+-h\d+[^"]*$/, '=w400-h300-k-no')); }
      return acc;
    }, []);
    if (!imgs.length) return;
    currentImgs = imgs; currentIdx = 0;
    renderPreview();
    positionPreview(e);
    preview.style.display = 'block';
  }

  function renderPreview() {
    const nav = currentImgs.length > 1
      ? '<div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:5px">'
        + '<span style="cursor:pointer;font-size:14px;color:var(--tx2);padding:0 4px" id="pp-prev">‹</span>'
        + '<span style="font-size:10px;color:var(--tx3)">'+(currentIdx+1)+' / '+currentImgs.length+'</span>'
        + '<span style="cursor:pointer;font-size:14px;color:var(--tx2);padding:0 4px" id="pp-next">›</span>'
        + '</div>' : '';
    const dots = currentImgs.length > 1
      ? '<div style="display:flex;gap:4px;justify-content:center;margin-top:4px">'
        + currentImgs.map(function(_, i){
            return '<span style="width:6px;height:6px;border-radius:50%;background:'+(i===currentIdx?'var(--ac)':'#cbd5e1')+'"></span>';
          }).join('') + '</div>'
      : '';
    preview.innerHTML = '<img src="' + currentImgs[currentIdx] + '" style="width:280px;height:200px;object-fit:cover;border-radius:5px;display:block">'
      + nav + dots;
    var prev = document.getElementById('pp-prev');
    var next = document.getElementById('pp-next');
    if (prev) prev.addEventListener('click', function(){ step(-1); });
    if (next) next.addEventListener('click', function(){ step(1); });
  }

  function step(dir) {
    currentIdx = (currentIdx + dir + currentImgs.length) % currentImgs.length;
    renderPreview();
  }

  function positionPreview(e) {
    const pad = 14;
    let x = e.clientX + pad, y = e.clientY + pad;
    if (x + 310 > window.innerWidth)  x = e.clientX - 310 - pad;
    if (y + 240 > window.innerHeight) y = e.clientY - 240 - pad;
    preview.style.left = x + 'px';
    preview.style.top  = y + 'px';
  }

  function scheduleHide() {
    hideTimer = setTimeout(function(){ if (!isOver) preview.style.display='none'; }, 200);
  }

  // Thumbnail events
  document.addEventListener('mouseover', function(e){
    const el = e.target.closest('.img-thumb-wrap');
    if (!el) return;
    isOver = true;
    clearTimeout(hideTimer);
    showPreview(el, e);
  });
  document.addEventListener('mousemove', function(e){
    if (preview.style.display === 'none') return;
    if (e.target.closest('.img-thumb-wrap')) positionPreview(e);
  });
  document.addEventListener('mouseout', function(e){
    if (!e.target.closest('.img-thumb-wrap')) return;
    isOver = false;
    scheduleHide();
  });

  // Popup events — mouse masuk popup, tahan tampil
  preview.addEventListener('mouseenter', function(){ isOver = true; clearTimeout(hideTimer); });
  preview.addEventListener('mouseleave', function(){ isOver = false; scheduleHide(); });

  // Scroll di thumbnail atau popup untuk ganti foto
  function handleWheel(e) {
    if (preview.style.display === 'none' || currentImgs.length < 2) return;
    e.preventDefault();
    step(e.deltaY > 0 ? 1 : -1);
  }
  document.addEventListener('wheel', function(e){
    if (!e.target.closest('.img-thumb-wrap') && e.target !== preview && !preview.contains(e.target)) return;
    handleWheel(e);
  }, {passive: false});
})();

// ── Popular Times hover heatmap ───────────────────────────────────────────────
(function(){
const DAY_KEYS  = ['mon','tue','wed','thu','fri','sat','sun'];
const DAY_NAMES = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
const HOURS     = [6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22];

function ptColor(v) {
    if (!v || v <= 0)  return '#f1f5f9';
    if (v < 25)  return '#dbeafe';
    if (v < 45)  return '#bfdbfe';
    if (v < 60)  return '#fed7aa';
    if (v < 75)  return '#fb923c';
    if (v < 88)  return '#ef4444';
    return '#b91c1c';
}

function buildHeatmap(pt, name) {
    // Cari slot paling ramai
    let peakDay = '', peakHr = 0, peakVal = 0;
    DAY_KEYS.forEach((dk, di) => {
        if (!pt[dk]) return;
        HOURS.forEach(h => {
            if (pt[dk][h] > peakVal) {
                peakVal = pt[dk][h]; peakHr = h; peakDay = DAY_NAMES[di];
            }
        });
    });

    // Header jam
    let html = `<div style="font-size:12px;font-weight:700;color:var(--tx);margin-bottom:10px;padding-right:4px">
        ${name}</div>`;
    html += `<div style="display:grid;grid-template-columns:28px repeat(${HOURS.length},1fr);gap:2px;align-items:center">`;

    // Baris header jam
    html += `<div></div>`;
    HOURS.forEach(h => {
        html += `<div style="text-align:center;font-size:9px;color:var(--tx3);font-weight:500">
            ${h % 3 === 0 ? h : ''}</div>`;
    });

    // Baris per hari
    DAY_KEYS.forEach((dk, di) => {
        const row = pt[dk] || [];
        html += `<div style="font-size:10px;font-weight:600;color:var(--tx2);text-align:right;padding-right:5px">${DAY_NAMES[di]}</div>`;
        HOURS.forEach(h => {
            const v = row[h] || 0;
            const isPeak = (v === peakVal && peakVal > 0);
            html += `<div title="${DAY_NAMES[di]} ${String(h).padStart(2,'0')}:00 — ${v}%"
                style="height:14px;border-radius:2px;background:${ptColor(v)};
                       ${isPeak ? 'outline:2px solid #1d4ed8;outline-offset:1px;' : ''}
                       cursor:default"></div>`;
        });
    });
    html += `</div>`;

    // Legenda + puncak
    html += `<div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;flex-wrap:wrap;gap:6px">`;
    html += `<div style="display:flex;align-items:center;gap:6px">`;
    [['#dbeafe','Sepi'],['#fb923c','Cukup'],['#b91c1c','Sangat ramai']].forEach(([c,l]) => {
        html += `<span style="display:flex;align-items:center;gap:3px;font-size:10px;color:var(--tx3)">
            <span style="width:10px;height:10px;border-radius:2px;background:${c};display:inline-block"></span>${l}</span>`;
    });
    html += `</div>`;
    if (peakVal > 0) {
        html += `<span style="font-size:10px;color:var(--tx2)">
            Puncak: <strong>${peakDay} ${String(peakHr).padStart(2,'0')}:00</strong> (${peakVal}%)</span>`;
    }
    html += `</div>`;
    return html;
}

// Buat popup elemen
const popup = document.createElement('div');
popup.id = 'pt-popup';
popup.style.cssText = `
    position:fixed;z-index:999;background:var(--sur);border:1px solid var(--bdr);
    border-radius:10px;padding:14px 16px;box-shadow:0 8px 32px rgba(0,0,0,.18);
    pointer-events:none;display:none;min-width:320px;max-width:420px;
    transition:opacity .12s;
`;
document.body.appendChild(popup);

let hideTimer;

document.querySelectorAll('.pt-cell[data-pt]').forEach(cell => {
    cell.addEventListener('mouseenter', function(e) {
        clearTimeout(hideTimer);
        const pt   = JSON.parse(this.dataset.pt || 'null');
        const name = this.dataset.name || '';
        if (!pt) return;

        popup.innerHTML = buildHeatmap(pt, name);

        // Posisi: preferred di atas kursor, cek batas layar
        popup.style.display = 'block';
        popup.style.opacity = '0';
        const rect = this.getBoundingClientRect();
        const pw   = popup.offsetWidth;
        const ph   = popup.offsetHeight;

        let left = rect.left + (rect.width / 2) - (pw / 2);
        let top  = rect.top - ph - 10;

        if (left < 8) left = 8;
        if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
        if (top < 8) top = rect.bottom + 10;

        popup.style.left = left + 'px';
        popup.style.top  = top  + 'px';
        popup.style.opacity = '1';
    });

    cell.addEventListener('mouseleave', function() {
        hideTimer = setTimeout(() => { popup.style.display = 'none'; }, 80);
    });
});
})();
</script>
@endpush

@endsection
