@extends('layouts.app')
@section('title', 'Dasbor — Mafaza Fortuna')
@section('page-title', 'Dasbor')

@push('topbar-actions')
@if($incomingToday > 0)
<span style="background:var(--rd);color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:20px">
    <i class="fas fa-envelope"></i> {{ $incomingToday }} pesan masuk
</span>
@endif
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
@if($stats['followup_due'] > 0)
<div class="alert mb-16" style="display:flex;align-items:center;gap:10px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3);color:var(--tx);border-radius:8px;padding:10px 14px">
    <i class="fas fa-bell" style="color:#ef4444"></i>
    <span><strong>{{ $stats['followup_due'] }} toko</strong> sudah 3+ hari terkirim tapi belum ada respon — saatnya follow up!</span>
    <a href="{{ route('places.index', ['qf'=>'sent']) }}" class="btn btn-sm btn-secondary" style="margin-left:auto">Lihat →</a>
</div>
@endif
@if($stats['ordered'] > 0)
<div class="alert alert-success mb-16" style="display:flex;align-items:center;gap:10px">
    <i class="fas fa-shopping-cart"></i>
    <span><strong>{{ $stats['ordered'] }} toko</strong> sudah order! 🎉</span>
    <a href="{{ route('places.index', ['qf'=>'ordered']) }}" class="btn btn-sm btn-success" style="margin-left:auto">Lihat →</a>
</div>
@elseif($stats['interested'] > 0)
<div class="alert alert-success mb-16" style="display:flex;align-items:center;gap:10px">
    <i class="fas fa-thumbs-up"></i>
    <span><strong>{{ $stats['interested'] }} toko</strong> berminat — follow up sekarang!</span>
    <a href="{{ route('places.index', ['qf'=>'interested']) }}" class="btn btn-sm btn-success" style="margin-left:auto">Follow up →</a>
</div>
@elseif($stats['replied'] > 0)
<div class="alert mb-16" style="display:flex;align-items:center;gap:10px;background:rgba(6,182,212,.08);border:1px solid rgba(6,182,212,.3);color:var(--tx);border-radius:8px;padding:10px 14px">
    <i class="fas fa-reply" style="color:#06b6d4"></i>
    <span><strong>{{ $stats['replied'] }} toko</strong> sudah membalas. Tandai status selanjutnya.</span>
    <a href="{{ route('places.index', ['qf'=>'replied']) }}" class="btn btn-sm btn-secondary" style="margin-left:auto">Lihat →</a>
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
            ['label' => 'Total Tempat Scraped',   'val' => $stats['total'],         'color' => '#6b7280', 'icon' => 'fa-store',         'href' => route('places.index')],
            ['label' => 'Kategori Relevan',        'val' => $stats['relevant'],      'color' => '#3b82f6', 'icon' => 'fa-tag',           'href' => route('places.index')],
            ['label' => 'Ramai (Score >50)',        'val' => $stats['high_score'],    'color' => '#8b5cf6', 'icon' => 'fa-fire',          'href' => route('places.index', ['sort'=>'busyness_score','direction'=>'desc'])],
            ['label' => 'Punya WA Aktif',          'val' => $stats['has_wa'],        'color' => '#16a34a', 'icon' => 'fa-whatsapp',      'href' => route('places.index', ['qf'=>'wa'])],
            ['label' => 'Sudah Dikirim Outreach',  'val' => $stats['outreach_sent'], 'color' => '#f59e0b', 'icon' => 'fa-paper-plane',   'href' => route('places.index', ['qf'=>'sent'])],
            ['label' => 'Sudah Respon',            'val' => $stats['replied'],       'color' => '#06b6d4', 'icon' => 'fa-reply',         'href' => route('places.index', ['qf'=>'replied'])],
            ['label' => 'Berminat',                'val' => $stats['interested'],    'color' => '#f97316', 'icon' => 'fa-thumbs-up',     'href' => route('places.index', ['qf'=>'interested'])],
            ['label' => 'Sudah Order',             'val' => $stats['ordered'],       'color' => '#10b981', 'icon' => 'fa-shopping-cart', 'href' => route('places.index', ['qf'=>'ordered'])],
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
        $pctWa      = $stats['total'] > 0 ? round($stats['has_wa']/$stats['total']*100, 1) : 0;
        $pctResp    = $stats['outreach_sent'] > 0 ? round($stats['replied']/$stats['outreach_sent']*100, 1) : 0;
        $pctOrder   = $stats['replied'] > 0 ? round($stats['ordered']/$stats['replied']*100, 1) : 0;
        @endphp
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--bdr);display:flex;gap:24px;flex-wrap:wrap">
            <span class="text-xs text-muted">Scrape→WA: <strong>{{ $pctWa }}%</strong></span>
            <span class="text-xs text-muted">Outreach→respon: <strong>{{ $pctResp > 0 ? $pctResp.'%' : '—' }}</strong></span>
            <span class="text-xs text-muted">Respon→order: <strong>{{ $pctOrder > 0 ? $pctOrder.'%' : '—' }}</strong></span>
            <span class="text-xs text-muted">Tidak berminat: <strong style="color:var(--rd)">{{ number_format($stats['not_interested']) }}</strong></span>
            <span class="text-xs text-muted">Belum dicek WA: <strong style="color:var(--or)">{{ number_format($stats['wa_unchecked']) }}</strong></span>
            @if(isset($stats['total_order_value']) && $stats['total_order_value'] > 0)
            <span class="text-xs text-muted">Total nilai order: <strong style="color:var(--gn)">Rp {{ number_format($stats['total_order_value'], 0, ',', '.') }}</strong></span>
            @endif
        </div>
    </div>
</div>

{{-- Status Sistem + Tren --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Grafik Tren Outreach --}}
    <div class="card">
        <div class="card-header"><i class="fas fa-chart-line" style="color:var(--ac)"></i> Tren Outreach 14 Hari</div>
        <div class="card-body" style="padding:12px 16px">
            <canvas id="trendChart" height="80"></canvas>
        </div>
    </div>

    {{-- Status Sistem + Pesan Masuk --}}
    <div style="display:flex;flex-direction:column;gap:12px">
        {{-- Status sistem --}}
        <div class="card">
            <div class="card-header"><i class="fas fa-heartbeat" style="color:var(--gn)"></i> Status Sistem</div>
            <div class="card-body" style="padding:10px 14px;display:flex;flex-direction:column;gap:8px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:12.5px"><i class="fab fa-telegram" style="color:#229ED9"></i> Notifikasi Telegram</span>
                    @if($telegramEnabled)
                        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px">Aktif</span>
                    @else
                        <a href="{{ route('telegram.index') }}" style="background:#fee2e2;color:#dc2626;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;text-decoration:none">Setup →</a>
                    @endif
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:12.5px"><i class="fab fa-whatsapp" style="color:#16a34a"></i> Webhook Pesan Masuk</span>
                    @if($webhookRegistered)
                        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px">Terdaftar</span>
                    @else
                        <a href="{{ route('whatsapp.index') }}" style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;text-decoration:none">Daftarkan →</a>
                    @endif
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:12.5px"><i class="fas fa-calendar-alt" style="color:var(--ac)"></i> Jadwal Scraping</span>
                    @if($nextSchedule)
                        <span style="font-size:11px;color:var(--tx2)">{{ $nextSchedule['name'] }} — {{ \Carbon\Carbon::parse($nextSchedule['next'])->diffForHumans() }}</span>
                    @else
                        <a href="{{ route('scraper-schedule.index') }}" style="font-size:11px;color:var(--ac);text-decoration:none">Buat jadwal →</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pesan masuk terbaru --}}
        <div class="card" style="flex:1">
            <div class="card-header">
                <span><i class="fas fa-envelope-open" style="color:var(--or)"></i> Pesan WA Masuk Terbaru</span>
                @if($incomingToday > 0)
                <span style="background:var(--rd);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px">{{ $incomingToday }} hari ini</span>
                @endif
            </div>
            @if($recentIncoming->isEmpty())
            <div style="padding:18px;text-align:center;color:var(--tx3);font-size:12.5px">
                Belum ada pesan masuk dari prospek.<br>
                <span style="font-size:11px">Pastikan webhook sudah terdaftar.</span>
            </div>
            @else
            <div style="display:flex;flex-direction:column">
                @foreach($recentIncoming as $msg)
                <div style="padding:9px 14px;border-bottom:1px solid var(--bdr);display:flex;gap:10px;align-items:flex-start">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--acl);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:11px;font-weight:700;color:var(--ac)">
                        {{ mb_strtoupper(mb_substr($msg->place?->name ?? '?', 0, 1)) }}
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:12.5px;font-weight:600">{{ Str::limit($msg->place?->name ?? $msg->from_number, 25) }}</div>
                        <div style="font-size:11.5px;color:var(--tx2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ Str::limit($msg->message, 40) }}</div>
                    </div>
                    <div style="font-size:10.5px;color:var(--tx3);white-space:nowrap;flex-shrink:0">{{ $msg->received_at->diffForHumans(null, true) }} lalu</div>
                </div>
                @endforeach
            </div>
            @endif
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
                            @if($p->outreach_status === 'ordered')
                                <span class="badge badge-green"><i class="fas fa-shopping-cart"></i> Order</span>
                            @elseif($p->outreach_status === 'interested')
                                <span class="badge badge-orange"><i class="fas fa-thumbs-up"></i> Berminat</span>
                            @elseif($p->outreach_status === 'not_interested')
                                <span class="badge badge-red">Tidak Berminat</span>
                            @elseif(in_array($p->outreach_status, ['replied','responded']))
                                <span class="badge badge-blue">Respon</span>
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
    [style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json($trendDates->values());
    const data   = @json($trendData->values());

    const shortLabels = labels.map(d => {
        const parts = d.split('-');
        return parts[2] + '/' + parts[1];
    });

    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: shortLabels,
            datasets: [{
                label: 'Pesan Terkirim',
                data: data,
                backgroundColor: 'rgba(37,99,235,.65)',
                borderColor: 'rgba(37,99,235,1)',
                borderWidth: 1,
                borderRadius: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,.05)' }
                },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });
})();
</script>
@endpush

@endsection
