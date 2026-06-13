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

{{-- Popular Times --}}
@if($place->popular_times)
@php
    $ptDays = ['sun'=>'Minggu','mon'=>'Senin','tue'=>'Selasa','wed'=>'Rabu','thu'=>'Kamis','fri'=>'Jumat','sat'=>'Sabtu'];
    $busy   = $place->busiestSlot();
    $ptData = $place->popular_times;
    // Hari ini (default aktif)
    $todayKey = ['sun','mon','tue','wed','thu','fri','sat'][now()->dayOfWeek];
    $defaultDay = isset($ptData[$todayKey]) ? $todayKey : array_key_first($ptData);
@endphp
<div class="card mb-16">
    <div class="card-header" style="justify-content:space-between;flex-wrap:wrap;gap:8px">
        <span><i class="fas fa-chart-bar" style="color:var(--ac);margin-right:6px"></i>Jam Ramai</span>
        @if($busy)
        <span class="text-xs text-muted">
            Paling ramai: <strong>{{ $ptDays[$busy['day']] }}</strong>
            pukul <strong>{{ str_pad($busy['hour'],2,'0',STR_PAD_LEFT) }}:00</strong>
        </span>
        @endif
    </div>
    <div class="card-body" style="padding:16px">
        {{-- Tab hari --}}
        <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px" id="pt-tabs">
            @foreach($ptDays as $key => $label)
            @if(isset($ptData[$key]))
            @php $peak = max($ptData[$key]); @endphp
            <button onclick="showPtDay('{{ $key }}')" id="pt-tab-{{ $key }}"
                class="btn btn-sm {{ $key === $defaultDay ? 'btn-primary' : 'btn-secondary' }}"
                style="position:relative">
                {{ substr($label,0,3) }}
                @if($peak >= 70)
                <span style="position:absolute;top:-4px;right:-4px;width:7px;height:7px;border-radius:50%;background:var(--rd)"></span>
                @elseif($peak >= 40)
                <span style="position:absolute;top:-4px;right:-4px;width:7px;height:7px;border-radius:50%;background:var(--or)"></span>
                @endif
            </button>
            @endif
            @endforeach
        </div>

        {{-- Grafik per hari --}}
        @foreach($ptDays as $key => $label)
        @if(isset($ptData[$key]))
        <div id="pt-day-{{ $key }}" style="display:{{ $key === $defaultDay ? 'block' : 'none' }}">
            @php
                $hours = $ptData[$key];
                $peakVal = max($hours);
                $peakHr  = array_search($peakVal, $hours);
            @endphp
            <div style="display:flex;align-items:flex-end;gap:3px;height:64px;padding-bottom:0">
                @foreach($hours as $hr => $val)
                @if($hr >= 6 && $hr <= 22)
                @php
                    $h    = $val > 0 ? max(3, round($val * 64 / 100)) : 0;
                    $col  = $val >= 70 ? '#ef4444' : ($val >= 40 ? '#f97316' : '#3b82f6');
                    $isPeak = ($hr == $peakHr && $val > 0);
                @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px">
                    <div style="width:100%;height:{{ $h }}px;background:{{ $col }};border-radius:2px 2px 0 0;
                                opacity:{{ $isPeak ? 1 : 0.65 }};transition:.15s;
                                {{ $isPeak ? 'outline:2px solid '.$col.';outline-offset:1px' : '' }}"
                         title="{{ $label }} {{ str_pad($hr,2,'0',STR_PAD_LEFT) }}:00 — {{ $val }}%"></div>
                </div>
                @endif
                @endforeach
            </div>
            {{-- Label jam --}}
            <div style="display:flex;gap:3px;margin-top:4px">
                @foreach($hours as $hr => $val)
                @if($hr >= 6 && $hr <= 22)
                <div style="flex:1;text-align:center;font-size:9px;color:var(--tx3)">
                    {{ in_array($hr,[6,9,12,15,18,21]) ? $hr : '' }}
                </div>
                @endif
                @endforeach
            </div>

            {{-- Keterangan --}}
            <div style="display:flex;gap:12px;margin-top:10px;flex-wrap:wrap">
                <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--tx3)">
                    <span style="width:10px;height:10px;background:#3b82f6;border-radius:2px;display:inline-block"></span> Sepi
                </div>
                <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--tx3)">
                    <span style="width:10px;height:10px;background:#f97316;border-radius:2px;display:inline-block"></span> Cukup ramai
                </div>
                <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--tx3)">
                    <span style="width:10px;height:10px;background:#ef4444;border-radius:2px;display:inline-block"></span> Sangat ramai
                </div>
                <div style="margin-left:auto;font-size:11px;color:var(--tx2)">
                    Puncak: <strong>{{ str_pad($peakHr,2,'0',STR_PAD_LEFT) }}:00</strong> ({{ $peakVal }}%)
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif

{{-- Outreach --}}
<div class="card mb-16">
    <div class="card-header" style="justify-content:space-between">
        <span><i class="fab fa-whatsapp" style="color:var(--gn);margin-right:6px"></i>Outreach WhatsApp</span>
        @if($place->outreach_status === 'responded')
            <span class="badge badge-green"><i class="fas fa-check"></i> Sudah Respon</span>
        @elseif($place->outreach_status === 'sent')
            <span class="badge badge-blue">Terkirim {{ $place->outreach_sent_at ? $place->outreach_sent_at->diffForHumans() : '' }}</span>
        @endif
    </div>
    <div class="card-body" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
        @if($place->has_whatsapp === true)
            @if(!$place->outreach_status)
            <a href="{{ route('whatsapp.index') }}"
               class="btn btn-sm" style="background:var(--gn);color:#fff;border-color:var(--gn)">
                <i class="fab fa-whatsapp"></i> Kirim Outreach
            </a>
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $place->phone) }}" target="_blank"
               class="btn btn-sm btn-secondary">
                <i class="fas fa-external-link-alt"></i> Chat Manual
            </a>
            @elseif($place->outreach_status === 'sent')
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $place->phone) }}" target="_blank"
               class="btn btn-sm" style="background:var(--gn);color:#fff;border-color:var(--gn)">
                <i class="fab fa-whatsapp"></i> Buka Chat
            </a>
            <form method="POST" action="{{ route('whatsapp.mark-status', $place->id) }}" style="display:inline">
                @csrf
                <input type="hidden" name="status" value="responded">
                <button type="submit" class="btn btn-sm btn-secondary">✓ Tandai Respon</button>
            </form>
            @elseif($place->outreach_status === 'responded')
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $place->phone) }}" target="_blank"
               class="btn btn-sm btn-secondary">
                <i class="fab fa-whatsapp"></i> Buka Chat
            </a>
            @endif
        @elseif($place->has_whatsapp === false)
            <span class="text-sm text-muted"><i class="fas fa-times-circle" style="color:var(--rd)"></i> Nomor ini tidak terdaftar di WhatsApp</span>
        @else
            <span class="text-sm text-muted"><i class="fas fa-question-circle"></i> Belum dicek —</span>
            <a href="{{ route('whatsapp.index') }}" class="btn btn-sm btn-secondary">Cek WA sekarang</a>
        @endif
        @if($place->phone)
        <span class="text-xs text-muted" style="margin-left:auto">{{ $place->phone }}</span>
        @endif
    </div>
</div>

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

@push('scripts')
<script>
function showPtDay(key) {
    document.querySelectorAll('[id^="pt-day-"]').forEach(el => el.style.display = 'none');
    document.querySelectorAll('[id^="pt-tab-"]').forEach(el => {
        el.classList.remove('btn-primary');
        el.classList.add('btn-secondary');
    });
    const day = document.getElementById('pt-day-' + key);
    const tab = document.getElementById('pt-tab-' + key);
    if (day) day.style.display = 'block';
    if (tab) { tab.classList.remove('btn-secondary'); tab.classList.add('btn-primary'); }
}
</script>
@endpush

@endsection
