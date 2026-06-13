@extends('layouts.app')
@section('title', $place->name . ' — Mafaza Fortuna')
@section('page-title', Str::limit($place->name, 40))

@push('topbar-actions')
<a href="{{ route('places.edit', $place) }}" class="btn btn-secondary btn-sm">
    <i class="fas fa-edit"></i> Edit
</a>
<a href="{{ route('places.index') }}" class="btn btn-ghost btn-sm">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endpush

@section('content')

@php
/* Format opening hours: "Sabtu08.00–17.00Minggu08.00–17.00..." → per-row */
function formatOpeningHours(?string $raw): array {
    if (!$raw) return [];
    $days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
    // remove trailing noise text (Google Maps UI strings)
    $raw = preg_replace('/Sarankan jam buka baru.*$/iu', '', $raw);
    $raw = trim($raw);
    // insert ¦ before each day name
    $pattern = '/(' . implode('|', $days) . ')/u';
    $raw = preg_replace($pattern, '¦$1', $raw);
    $parts = array_filter(explode('¦', $raw));
    $result = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') continue;
        foreach ($days as $d) {
            if (str_starts_with($part, $d)) {
                $hours = trim(substr($part, strlen($d)));
                $result[] = ['day' => $d, 'hours' => $hours ?: 'Tutup'];
                break;
            }
        }
    }
    return $result;
}
$openingHoursRows = formatOpeningHours($place->opening_hours);
$today = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'][date('N') - 1];
@endphp

<div class="grid mb-16" style="grid-template-columns:1fr 1fr;gap:16px">

    {{-- Basic Info --}}
    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-store" style="color:var(--ac);margin-right:6px"></i>Informasi Utama</span>
        </div>
        <div class="card-body p-0">
            <table style="min-width:unset">
                <tr>
                    <td style="width:100px;color:var(--tx2);font-size:12px;font-weight:500;padding:10px 16px;white-space:nowrap;vertical-align:top">Nama</td>
                    <td style="padding:10px 16px;font-size:13px;font-weight:600">{{ $place->name }}</td>
                </tr>
                <tr>
                    <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:10px 16px;white-space:nowrap;vertical-align:top">Alamat</td>
                    <td style="padding:10px 16px;font-size:13px;color:var(--tx)">{{ $place->address ?: '—' }}</td>
                </tr>
                <tr>
                    <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:10px 16px;white-space:nowrap">Telepon</td>
                    <td style="padding:10px 16px">
                        @if($place->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $place->phone) }}"
                           target="_blank" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp"></i> {{ $place->phone }}
                        </a>
                        @else
                        <span class="text-muted text-sm">—</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:10px 16px;white-space:nowrap">Website</td>
                    <td style="padding:10px 16px">
                        @if($place->website)
                        <a href="{{ $place->website }}" target="_blank" class="btn btn-secondary btn-sm">
                            <i class="fas fa-external-link-alt"></i> Buka
                        </a>
                        @else
                        <span class="text-muted text-sm">—</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:10px 16px;white-space:nowrap">Kategori</td>
                    <td style="padding:10px 16px;font-size:13px">{{ $place->category ?: '—' }}</td>
                </tr>
                @if($place->permanently_closed)
                <tr>
                    <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:10px 16px;white-space:nowrap">Status</td>
                    <td style="padding:10px 16px"><span class="badge badge-red"><i class="fas fa-ban"></i> Tutup Permanen</span></td>
                </tr>
                @endif
                @if($place->maps_url)
                <tr>
                    <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:10px 16px;white-space:nowrap">Maps</td>
                    <td style="padding:10px 16px">
                        <a href="{{ $place->maps_url }}" target="_blank" class="btn btn-ghost btn-sm">
                            <i class="fas fa-map-marker-alt"></i> Google Maps
                        </a>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Stats & Scores --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-chart-bar" style="color:var(--ac);margin-right:6px"></i>Statistik</span>
            </div>
            <div class="card-body p-0">
                <table style="min-width:unset">
                    <tr>
                        <td style="width:120px;color:var(--tx2);font-size:12px;font-weight:500;padding:9px 16px">Rating</td>
                        <td style="padding:9px 16px">
                            @if($place->rating)
                            <span class="badge badge-yellow"><i class="fas fa-star"></i> {{ $place->rating }} / 5.0</span>
                            @else
                            <span class="text-muted text-sm">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:9px 16px">Ulasan</td>
                        <td style="padding:9px 16px;font-size:13px;font-weight:600">
                            {{ $place->review_count ? number_format($place->review_count) : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:9px 16px">Busyness</td>
                        <td style="padding:9px 16px">
                            @if($place->busyness_score)
                            <span class="fw-700" style="font-size:16px;color:var(--ac)">{{ number_format($place->busyness_score, 0) }}</span>
                            <span class="text-muted text-xs">&nbsp;/ 200</span>
                            @else
                            <span class="text-muted text-sm">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:9px 16px">WhatsApp</td>
                        <td style="padding:9px 16px">
                            @if($place->has_whatsapp === true)
                            <span class="badge badge-green"><i class="fab fa-whatsapp"></i> Aktif</span>
                            @elseif($place->has_whatsapp === false)
                            <span class="badge badge-red">Tidak ada</span>
                            @else
                            <span class="badge badge-gray">Belum dicek</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:9px 16px">Target</td>
                        <td style="padding:9px 16px">
                            @if($place->is_target)
                            <span class="badge badge-orange"><i class="fas fa-crosshairs"></i> Ya</span>
                            @else
                            <span class="badge badge-gray">Bukan</span>
                            @endif
                        </td>
                    </tr>
                    @if($place->latitude && $place->longitude)
                    <tr>
                        <td style="color:var(--tx2);font-size:12px;font-weight:500;padding:9px 16px">Koordinat</td>
                        <td style="padding:9px 16px;font-size:12px;font-family:'Courier New',monospace">
                            {{ $place->latitude }}, {{ $place->longitude }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:12px 16px">
                <div class="d-flex gap-8 flex-wrap">
                    <div style="flex:1;min-width:100px">
                        <div class="text-xs text-muted mb-4">Dibuat</div>
                        <div class="text-sm fw-500">{{ $place->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div style="flex:1;min-width:100px">
                        <div class="text-xs text-muted mb-4">Diperbarui</div>
                        <div class="text-sm fw-500">{{ $place->updated_at->format('d M Y, H:i') }}</div>
                    </div>
                    @if($place->last_scraped_at)
                    <div style="flex:1;min-width:100px">
                        <div class="text-xs text-muted mb-4">Di-scrape</div>
                        <div class="text-sm fw-500">{{ $place->last_scraped_at->diffForHumans() }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Opening hours --}}
@if(count($openingHoursRows))
<div class="card mb-16">
    <div class="card-header">
        <span><i class="fas fa-clock" style="color:var(--ac);margin-right:6px"></i>Jam Operasional</span>
    </div>
    <div class="card-body p-0">
        <table style="min-width:unset">
            @foreach($openingHoursRows as $row)
            <tr style="{{ $row['day'] === $today ? 'background:var(--acl)' : '' }}">
                <td style="width:90px;padding:8px 16px;font-size:13px;font-weight:{{ $row['day'] === $today ? '700' : '500' }};color:{{ $row['day'] === $today ? 'var(--ac)' : 'var(--tx2)' }}">
                    {{ $row['day'] }}
                    @if($row['day'] === $today)
                    <span class="badge badge-blue" style="margin-left:4px;font-size:9px">hari ini</span>
                    @endif
                </td>
                <td style="padding:8px 16px;font-size:13px;font-weight:{{ $row['day'] === $today ? '600' : '400' }}">{{ $row['hours'] }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@elseif($place->opening_hours)
<div class="card mb-16">
    <div class="card-header"><span><i class="fas fa-clock" style="color:var(--ac);margin-right:6px"></i>Jam Operasional</span></div>
    <div class="card-body"><p class="text-sm">{{ $place->opening_hours }}</p></div>
</div>
@endif

{{-- Description --}}
@if($place->description)
<div class="card mb-16">
    <div class="card-header"><span><i class="fas fa-align-left" style="color:var(--ac);margin-right:6px"></i>Deskripsi</span></div>
    <div class="card-body"><p style="font-size:14px;line-height:1.6;color:var(--tx);margin:0">{{ $place->description }}</p></div>
</div>
@endif

{{-- Permanently Closed Warning --}}
@if($place->permanently_closed)
<div class="card mb-16" style="border-color:var(--rd)">
    <div class="card-body" style="background:#fef2f2;border-radius:8px">
        <p style="color:var(--rd);font-weight:600;margin:0"><i class="fas fa-ban"></i> Tempat ini sudah tutup permanen menurut Google Maps.</p>
    </div>
</div>
@endif

{{-- Images --}}
@php
$imgs = [];
foreach(['image_1','image_2','image_3','image_4'] as $f) {
    if(!empty($place->$f)) $imgs[] = $place->$f;
}
@endphp
@if(count($imgs))
<div class="card mb-16">
    <div class="card-header"><span><i class="fas fa-images" style="color:var(--ac);margin-right:6px"></i>Foto ({{ count($imgs) }})</span></div>
    <div class="card-body">
        <div class="grid grid-3" style="gap:12px">
            @foreach($imgs as $img)
            <a href="{{ $img }}" target="_blank">
                <img src="{{ $img }}" alt="Foto tempat"
                     style="width:100%;height:160px;object-fit:cover;border-radius:6px;border:1px solid var(--bdr);display:block">
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
@media(max-width:768px){
    .grid[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}
}
</style>
@endpush

@endsection
