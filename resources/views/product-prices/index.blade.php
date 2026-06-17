@extends('layouts.app')
@section('title', 'Harga Produk — Mafaza Fortuna')
@section('page-title', 'Harga Produk')

@push('topbar-actions')
<a href="{{ route('product-prices.create') }}" class="btn btn-primary btn-sm">
    <i class="fas fa-plus"></i> Tambah
</a>
<form method="POST" action="{{ route('product-prices.clear-all') }}" id="clearAllForm" style="display:none">@csrf</form>
<button class="btn btn-danger btn-sm"
    onclick="if(confirm('Hapus semua data harga? Tidak dapat dibatalkan.')) document.getElementById('clearAllForm').submit()">
    <i class="fas fa-trash"></i> Hapus Semua
</button>
@endpush

@section('content')

{{-- Toolbar --}}
<form method="GET" action="{{ route('product-prices.index') }}" id="filterForm">
<div class="d-flex align-center gap-8 mb-12 flex-wrap">
    <div class="input-group" style="max-width:240px;flex:1;min-width:150px">
        <input type="text" name="search" class="form-control"
            placeholder="Cari produk, tempat…" value="{{ request('search') }}">
    </div>

    <select name="product_name" class="form-control" style="width:auto;font-size:12.5px;padding:5px 28px 5px 10px"
        onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Produk</option>
        @foreach($productNames as $product)
        <option value="{{ $product }}" {{ request('product_name') === $product ? 'selected' : '' }}>
            {{ $product }}
        </option>
        @endforeach
    </select>

    <select name="source" class="form-control" style="width:auto;font-size:12.5px;padding:5px 28px 5px 10px"
        onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Sumber</option>
        @foreach($sources as $src)
        <option value="{{ $src }}" {{ request('source') === $src ? 'selected' : '' }}>
            {{ $src === 'manual' ? 'Manual' : ($src === 'scraped' ? 'Scraping' : ucfirst($src)) }}
        </option>
        @endforeach
    </select>

    <div class="d-flex align-center gap-4">
        <input type="date" name="date_from" class="form-control" style="font-size:12.5px;padding:5px 8px"
            value="{{ request('date_from') }}" title="Dari tanggal">
        <span class="text-muted text-xs">—</span>
        <input type="date" name="date_to" class="form-control" style="font-size:12.5px;padding:5px 8px"
            value="{{ request('date_to') }}" title="Sampai tanggal">
    </div>

    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>

    @if(request('search') || request('product_name') || request('source') || request('date_from') || request('date_to'))
    <a href="{{ route('product-prices.index') }}" class="btn btn-ghost btn-sm">
        <i class="fas fa-times"></i> Reset
    </a>
    @endif
</div>
</form>

{{-- Bulk form --}}
<form id="bulkForm" method="POST" action="{{ route('product-prices.bulk-delete') }}">@csrf</form>

{{-- Stats + bulk action bar --}}
<div class="d-flex align-center gap-12 mb-12 text-sm text-muted flex-wrap">
    <span><strong>{{ $prices->total() }}</strong> data harga</span>
    <span class="ml-auto d-flex align-center gap-8" id="bulkBar" style="display:none!important">
        <span id="selectedCount" class="text-xs">0 dipilih</span>
        <button type="button" class="btn btn-danger btn-xs" id="bulkDeleteBtn"
            onclick="if(confirm('Hapus yang dipilih?')) document.getElementById('bulkForm').submit()">
            <i class="fas fa-trash"></i> Hapus Dipilih
        </button>
    </span>
</div>

{{-- Tabel --}}
<div class="card">
    <div style="overflow-x:auto">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:32px;text-align:center">
                        <input type="checkbox" id="cbAll">
                    </th>
                    <th>
                        <a href="{{ route('product-prices.index', array_merge(request()->query(), ['sort'=>'product_name','direction'=>request('sort')==='product_name'&&request('direction')==='asc'?'desc':'asc'])) }}">
                            Nama Produk @if(request('sort')==='product_name')<i class="fas fa-sort-{{ request('direction')==='asc'?'up':'down' }}"></i>@endif
                        </a>
                    </th>
                    <th class="hide-mobile">Kategori</th>
                    <th>
                        <a href="{{ route('product-prices.index', array_merge(request()->query(), ['sort'=>'price','direction'=>request('sort')==='price'&&request('direction')==='asc'?'desc':'asc'])) }}">
                            Harga @if(request('sort')==='price')<i class="fas fa-sort-{{ request('direction')==='asc'?'up':'down' }}"></i>@endif
                        </a>
                    </th>
                    <th class="hide-mobile">Satuan</th>
                    <th class="hide-mobile">Tempat</th>
                    <th class="hide-mobile">Sumber</th>
                    <th class="hide-mobile">
                        <a href="{{ route('product-prices.index', array_merge(request()->query(), ['sort'=>'recorded_at','direction'=>request('sort')==='recorded_at'&&request('direction')==='asc'?'desc':'asc'])) }}">
                            Tanggal @if(request('sort')==='recorded_at')<i class="fas fa-sort-{{ request('direction')==='asc'?'up':'down' }}"></i>@endif
                        </a>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($prices as $price)
                <tr>
                    <td style="text-align:center">
                        <input type="checkbox" class="row-cb" name="ids[]" value="{{ $price->id }}" form="bulkForm">
                    </td>
                    <td>
                        <div class="fw-600 text-sm">{{ $price->product_name }}</div>
                    </td>
                    <td class="hide-mobile">
                        <span class="badge badge-gray">{{ $price->product_category ?: '—' }}</span>
                    </td>
                    <td>
                        <span class="fw-600" style="color:var(--ac)">Rp {{ number_format($price->price, 0, ',', '.') }}</span>
                        @if($price->original_price)
                        <div class="text-xs text-muted"><s>Rp {{ number_format($price->original_price, 0, ',', '.') }}</s></div>
                        @endif
                    </td>
                    <td class="hide-mobile text-sm">{{ $price->unit }}</td>
                    <td class="hide-mobile text-sm text-muted">{{ Str::limit($price->place->name ?? '—', 25) }}</td>
                    <td class="hide-mobile">
                        @if($price->source === 'manual')
                            <span class="badge badge-blue">Manual</span>
                        @elseif($price->source === 'scraped')
                            <span class="badge badge-green">Scraping</span>
                        @else
                            <span class="badge badge-gray">{{ ucfirst($price->source) }}</span>
                        @endif
                    </td>
                    <td class="hide-mobile text-xs text-muted">{{ $price->recorded_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex gap-4 justify-content:flex-end" style="justify-content:flex-end">
                            <a href="{{ route('product-prices.show', $price) }}" class="btn btn-ghost btn-xs" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('product-prices.edit', $price) }}" class="btn btn-ghost btn-xs" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('product-prices.destroy', $price) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--rd)"
                                    onclick="return confirm('Hapus data harga ini?')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:48px;color:var(--tx3)">
                        <i class="fas fa-coins" style="font-size:28px;margin-bottom:8px;display:block"></i>
                        Belum ada data harga produk.<br>
                        <a href="{{ route('product-prices.create') }}" class="btn btn-primary btn-sm mt-8">
                            <i class="fas fa-plus"></i> Tambah Data
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($prices->hasPages())
<div class="mt-16">{{ $prices->appends(request()->query())->links() }}</div>
@endif

@push('scripts')
<script>
(function(){
    var cbAll = document.getElementById('cbAll');
    var bulkBar = document.getElementById('bulkBar');
    var selCount = document.getElementById('selectedCount');

    function updateBulk() {
        var n = document.querySelectorAll('.row-cb:checked').length;
        if (n > 0) {
            bulkBar.style.display = 'flex';
            selCount.textContent = n + ' dipilih';
        } else {
            bulkBar.style.display = 'none';
        }
    }

    cbAll.addEventListener('change', function() {
        document.querySelectorAll('.row-cb').forEach(function(cb) { cb.checked = cbAll.checked; });
        updateBulk();
    });

    document.querySelectorAll('.row-cb').forEach(function(cb) {
        cb.addEventListener('change', updateBulk);
    });
})();
</script>
@endpush
@endsection
