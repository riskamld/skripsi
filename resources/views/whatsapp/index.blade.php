@extends('layouts.app')
@section('title', 'WhatsApp — Mafaza Fortuna')
@section('page-title', 'WhatsApp Outreach')

@push('styles')
<style>
/* ── Stats strip ────────────────────────────────────────────────── */
.wa-stats-strip {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 6px;
    margin-bottom: 8px;
}
.wa-stat {
    background: var(--sur);
    border: 1px solid var(--bdr);
    border-radius: 8px;
    padding: 9px 10px;
    text-align: center;
    border-top: 3px solid transparent;
}
.wa-stat .lbl {
    font-size: 9.5px;
    color: var(--tx3);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 2px;
    white-space: nowrap;
}
.wa-stat .val {
    font-size: 20px;
    font-weight: 800;
    line-height: 1.1;
}
/* ── Funnel strip ───────────────────────────────────────────────── */
.funnel-strip {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 8px 14px;
    background: linear-gradient(135deg,#f0f9ff,#eff6ff);
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    margin-bottom: 8px;
    overflow-x: auto;
}
.funnel-step { text-align: center; padding: 0 12px; flex-shrink: 0; }
.funnel-val  { font-size: 17px; font-weight: 800; line-height: 1.1; }
.funnel-lbl  { font-size: 9px; text-transform: uppercase; letter-spacing: .06em; color: var(--tx3); }
.funnel-pct  { font-size: 10px; font-weight: 700; margin-top: 1px; }
.funnel-arrow { color: #93c5fd; padding: 0 2px; font-size: 14px; flex-shrink: 0; }
/* ── Device strip ───────────────────────────────────────────────── */
.dev-strip {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
    padding: 8px 12px;
    background: var(--sur);
    border: 1px solid var(--bdr);
    border-radius: 8px;
    margin-bottom: 10px;
}
.dev-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 11px;
    border-radius: 20px;
    border: 1.5px solid var(--bdr);
    cursor: pointer;
    transition: .15s;
    font-size: 12px;
    font-weight: 500;
    color: var(--tx);
    background: var(--bg);
}
.dev-pill:hover        { border-color: var(--ac); color: var(--ac); }
.dev-pill.dev-selected { border-color: var(--ac); background: var(--acl); color: var(--ac); }
.dev-dot  { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.dot-ready { background: #16a34a; box-shadow: 0 0 0 2px #dcfce7; }
.dot-off   { background: #9ca3af; }
.dev-badge { font-size: 10px; padding: 1px 6px; border-radius: 10px; font-weight: 600; }
.badge-ready { background: #dcfce7; color: #16a34a; }
.badge-off   { background: #f3f4f6; color: #6b7280; }
/* ── Tabs ───────────────────────────────────────────────────────── */
.wa-tabs-nav { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 12px; }
.tab-btn {
    padding: 6px 16px;
    border: 1.5px solid var(--bdr);
    background: var(--sur);
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    color: var(--tx2);
    border-radius: 20px;
    transition: .15s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}
.tab-btn:hover  { border-color: var(--ac); color: var(--ac); }
.tab-btn.active { background: var(--ac); border-color: var(--ac); color: #fff; }
.tab-badge { background: #ef4444; color: #fff; border-radius: 10px; font-size: 9.5px; padding: 1px 6px; }
.tab-btn.active .tab-badge { background: rgba(255,255,255,.3); }
/* ── Misc ───────────────────────────────────────────────────────── */
.log-box {
    background: #0d1117;
    border-radius: 6px;
    padding: 9px 11px;
    font-family: monospace;
    font-size: 11.5px;
    color: #c9d1d9;
    min-height: 56px;
    max-height: 180px;
    overflow-y: auto;
    white-space: pre-wrap;
}
.progress-bar-wrap { background: #e5e7eb; border-radius: 4px; height: 5px; overflow: hidden; margin: 5px 0; }
.progress-bar-fill { background: var(--gn); height: 100%; transition: width .4s; border-radius: 4px; }
.template-card {
    border: 1.5px solid var(--bdr);
    border-radius: 8px;
    padding: 9px 11px;
    cursor: pointer;
    transition: .15s;
}
.template-card:hover,.template-card.active { border-color: var(--ac); background: #eff6ff; }
.template-card.active .template-name { color: var(--ac); }
.template-body { font-size: 11.5px; color: var(--tx2); white-space: pre-wrap; margin-top: 4px; line-height: 1.5; }
/* ── Card header gradients ──────────────────────────────────────── */
.ch-green  { background: linear-gradient(135deg,#f0fdf4,#dcfce7); }
.ch-blue   { background: linear-gradient(135deg,#eff6ff,#dbeafe); }
.ch-amber  { background: linear-gradient(135deg,#fffbeb,#fef3c7); }
.ch-orange { background: linear-gradient(135deg,#fff7ed,#ffedd5); }
.ch-slate  { background: linear-gradient(135deg,#f8fafc,#f1f5f9); }
/* ── Responsive ─────────────────────────────────────────────────── */
@media(max-width:900px) { .wa-stats-strip { grid-template-columns:repeat(4,1fr); } }
@media(max-width:600px) { .wa-stats-strip { grid-template-columns:repeat(2,1fr); } }
@media(max-width:768px) { .two-col { grid-template-columns:1fr!important; } }
</style>
@endpush

@section('content')

{{-- ── 1. Stats strip ──────────────────────────────────────────────────── --}}
<div class="wa-stats-strip">
    <div class="wa-stat" style="border-top-color:#22c55e">
        <div class="lbl"><i class="fab fa-whatsapp" style="color:#22c55e"></i> Ada WA</div>
        <div class="val" style="color:#16a34a" id="stat-has-wa">{{ $stats['has_wa'] }}</div>
    </div>
    <div class="wa-stat" style="border-top-color:#f59e0b">
        <div class="lbl"><i class="fas fa-question" style="color:#f59e0b"></i> Belum Cek</div>
        <div class="val" style="color:#d97706" id="stat-unchecked">{{ $stats['unchecked'] }}</div>
    </div>
    <div class="wa-stat" style="border-top-color:#94a3b8">
        <div class="lbl">Tidak WA</div>
        <div class="val" style="color:#94a3b8" id="stat-no-wa">{{ $stats['no_wa'] }}</div>
    </div>
    <div class="wa-stat" style="border-top-color:#3b82f6">
        <div class="lbl"><i class="fas fa-paper-plane" style="color:#3b82f6"></i> Terkirim</div>
        <div class="val" style="color:#3b82f6" id="stat-sent">{{ $stats['outreach_sent'] }}</div>
    </div>
    <div class="wa-stat" style="border-top-color:#06b6d4">
        <div class="lbl"><i class="fas fa-reply" style="color:#06b6d4"></i> Respon</div>
        <div class="val" style="color:#06b6d4" id="stat-replied">{{ $stats['replied'] }}</div>
    </div>
    <div class="wa-stat" style="border-top-color:#f97316">
        <div class="lbl"><i class="fas fa-thumbs-up" style="color:#f97316"></i> Berminat</div>
        <div class="val" style="color:#f97316" id="stat-interested">{{ $stats['interested'] }}</div>
    </div>
    <div class="wa-stat" style="border-top-color:#9ca3af">
        <div class="lbl"><i class="fas fa-thumbs-down" style="color:#9ca3af"></i> Tidak Minat</div>
        <div class="val" style="color:#9ca3af" id="stat-not-interested">{{ $stats['not_interested'] }}</div>
    </div>
    <div class="wa-stat" style="border-top-color:#10b981">
        <div class="lbl"><i class="fas fa-shopping-cart" style="color:#10b981"></i> Order</div>
        <div class="val" style="color:#10b981" id="stat-ordered">{{ $stats['ordered'] }}</div>
    </div>
</div>

{{-- ── 2. Funnel + Daily quota ─────────────────────────────────────────── --}}
@php
$_hw  = max(1, $stats['has_wa']);
$_s   = $stats['outreach_sent'];
$_r   = $stats['replied'];
$_i   = $stats['interested'];
$_o   = $stats['ordered'];
@endphp
<div class="funnel-strip">
    <div class="funnel-step">
        <div class="funnel-val" style="color:#22c55e">{{ $stats['has_wa'] }}</div>
        <div class="funnel-lbl">Ada WA</div>
    </div>
    <span class="funnel-arrow">→</span>
    <div class="funnel-step">
        <div class="funnel-val" style="color:#3b82f6">{{ $_s }}</div>
        <div class="funnel-lbl">Terkirim</div>
        <div class="funnel-pct" style="color:#3b82f6">{{ round($_s/$_hw*100) }}%</div>
    </div>
    <span class="funnel-arrow">→</span>
    <div class="funnel-step">
        <div class="funnel-val" style="color:#06b6d4">{{ $_r }}</div>
        <div class="funnel-lbl">Respon</div>
        <div class="funnel-pct" style="color:#06b6d4">{{ $_s>0 ? round($_r/$_s*100) : 0 }}%</div>
    </div>
    <span class="funnel-arrow">→</span>
    <div class="funnel-step">
        <div class="funnel-val" style="color:#f97316">{{ $_i }}</div>
        <div class="funnel-lbl">Berminat</div>
        <div class="funnel-pct" style="color:#f97316">{{ $_r>0 ? round($_i/$_r*100) : 0 }}%</div>
    </div>
    <span class="funnel-arrow">→</span>
    <div class="funnel-step">
        <div class="funnel-val" style="color:#10b981">{{ $_o }}</div>
        <div class="funnel-lbl">Order</div>
        <div class="funnel-pct" style="color:#10b981">{{ $_i>0 ? round($_o/$_i*100) : 0 }}%</div>
    </div>

    {{-- Daily quota --}}
    <div style="margin-left:auto;padding-left:16px;border-left:1px solid #bfdbfe;display:flex;align-items:center;gap:8px;flex-shrink:0">
        <span style="font-size:10px;font-weight:700;color:var(--tx2);text-transform:uppercase;letter-spacing:.05em">Kuota Hari Ini</span>
        <div style="width:90px;height:7px;background:#dbeafe;border-radius:4px;overflow:hidden">
            <div id="daily-bar" style="height:100%;border-radius:4px;background:var(--ac);transition:width .3s;width:{{ min(100, round($stats['sent_today']/$stats['daily_limit']*100)) }}%"></div>
        </div>
        <span style="font-size:12px;font-weight:700;color:var(--tx)"><span id="sent-today">{{ $stats['sent_today'] }}</span><span style="color:var(--tx3)"> / </span><span id="daily-limit">{{ $stats['daily_limit'] }}</span></span>
        <span id="daily-warn" style="font-size:11px;color:var(--rd);display:none" title="Limit hampir habis!"><i class="fas fa-exclamation-triangle"></i></span>
    </div>
</div>

{{-- ── 3. Device selector strip ────────────────────────────────────────── --}}
<div class="dev-strip">
    <span style="font-size:10px;font-weight:700;color:var(--tx3);text-transform:uppercase;letter-spacing:.07em;flex-shrink:0;margin-right:2px">
        <i class="fas fa-mobile-alt" style="color:var(--ac)"></i> Device
    </span>
    <div id="device-list" style="display:contents">
        @forelse($devices as $d)
        <div class="dev-pill {{ ($d['status'] ?? '') !== 'ready' ? '' : '' }}"
             onclick="selectDevice('{{ $d['id'] }}','{{ $d['name'] }}')"
             id="dev-{{ $d['id'] }}"
             style="{{ ($d['status'] ?? '') !== 'ready' ? 'opacity:.5' : '' }}">
            <span class="dev-dot {{ ($d['status'] ?? '') === 'ready' ? 'dot-ready' : 'dot-off' }}"></span>
            <span>{{ $d['name'] }}</span>
            @if(!empty($d['number']))<span style="font-size:10px;color:var(--tx3)">{{ $d['number'] }}</span>@endif
            <span class="dev-badge {{ ($d['status'] ?? '') === 'ready' ? 'badge-ready' : 'badge-off' }}">{{ ($d['status'] ?? '') === 'ready' ? 'Online' : 'Off' }}</span>
        </div>
        @empty
        <span style="font-size:12px;color:var(--tx3)">Tidak ada device. Pastikan wa-api berjalan.</span>
        @endforelse
    </div>
    <div style="margin-left:auto;display:flex;align-items:center;gap:6px;flex-shrink:0">
        <span style="font-size:11px;color:var(--tx2)">Aktif:</span>
        <span id="selected-device-name" style="font-size:12px;font-weight:700;color:var(--ac)">— pilih —</span>
        <input type="hidden" id="selected-device-id" value="">
        <button class="btn btn-xs btn-secondary" onclick="refreshDevices()" title="Refresh devices"><i class="fas fa-sync-alt"></i></button>
    </div>
</div>

{{-- ── 4. Tabs nav ─────────────────────────────────────────────────────── --}}
<div class="wa-tabs-nav">
    <button class="tab-btn active" onclick="switchTab('cek-wa')">
        <i class="fas fa-search"></i> Cek WA
    </button>
    <button class="tab-btn" onclick="switchTab('outreach')">
        <i class="fas fa-paper-plane"></i> Kirim Pesan
    </button>
    <button class="tab-btn" onclick="switchTab('list')">
        <i class="fas fa-list"></i> Daftar Target
    </button>
    <button class="tab-btn" onclick="switchTab('followup')">
        <i class="fas fa-bell"></i> Follow Up
        @if($stats['replied'] + $stats['interested'] > 0)
        <span class="tab-badge">{{ $stats['replied'] + $stats['interested'] }}</span>
        @endif
    </button>
</div>

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- Tab: Cek WA                                                             --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
<div id="tab-cek-wa" class="tab-panel">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start" class="two-col">

        {{-- Cek baru --}}
        <div class="card">
            <div class="card-header ch-green">
                <span><i class="fas fa-search" style="color:#16a34a;margin-right:6px"></i>Cek Nomor WhatsApp</span>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                <div style="display:flex;align-items:center;gap:8px;font-size:12px">
                    <span style="color:var(--tx2)">Belum dicek:</span>
                    <strong style="font-size:16px;color:var(--or)" id="unchecked-count">{{ $stats['unchecked'] }}</strong>
                    <span style="color:var(--tx3)">nomor</span>
                </div>
                <div class="d-flex align-center gap-8 flex-wrap">
                    <label class="text-xs text-muted">Per batch:</label>
                    <select id="check-limit" class="form-control" style="width:70px;font-size:12px">
                        <option value="10">10</option>
                        <option value="30" selected>30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <button id="btn-check-wa" class="btn btn-sm" style="background:#16a34a;color:#fff;border-color:#16a34a" onclick="runCheckWA()">
                        <i class="fas fa-play"></i> Mulai Cek
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="runCheckWAAll()">
                        <i class="fas fa-forward"></i> Cek Semua
                    </button>
                </div>
                <div class="progress-bar-wrap" id="check-progress-wrap" style="display:none">
                    <div class="progress-bar-fill" id="check-progress-bar" style="width:0%"></div>
                </div>
                <div class="log-box" id="check-log" style="display:none"></div>
            </div>
        </div>

        {{-- Re-cek nomor lama --}}
        <div class="card">
            <div class="card-header ch-amber">
                <span><i class="fas fa-redo" style="color:#d97706;margin-right:6px"></i>Re-Cek Nomor Lama</span>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                <div style="font-size:12px;color:var(--tx2)">
                    Nomor yang sebelumnya dicek "tidak punya WA" mungkin sudah daftar. Sisa: <strong id="recheck-count" style="color:var(--or)">...</strong> nomor.
                </div>
                <button class="btn btn-sm btn-secondary" onclick="runReCheckWA()">
                    <i class="fas fa-redo"></i> Re-Cek 30 Nomor
                </button>
                <div class="log-box" id="recheck-log" style="display:none"></div>
            </div>
        </div>

    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;align-items:start" class="two-col">

        {{-- Webhook --}}
        <div class="card">
            <div class="card-header ch-blue">
                <span><i class="fas fa-plug" style="color:#3b82f6;margin-right:6px"></i>Webhook Pesan Masuk</span>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                <p class="text-xs text-muted">Saat aktif, setiap pesan WA yang masuk dari prospek akan otomatis mengupdate status dan mengirim notifikasi Telegram.</p>
                <div id="webhook-status-wrap" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span id="webhook-badge" style="font-size:12px;padding:3px 10px;border-radius:10px;font-weight:600">Mengecek...</span>
                    <button id="btn-webhook-reg" onclick="registerWebhook()" class="btn btn-sm" style="background:#16a34a;color:#fff;border-color:#16a34a;display:none">
                        <i class="fas fa-link"></i> Daftarkan
                    </button>
                    <button id="btn-webhook-unreg" onclick="unregisterWebhook()" class="btn btn-sm btn-danger" style="display:none">
                        <i class="fas fa-unlink"></i> Cabut
                    </button>
                </div>
                <code id="webhook-url" style="font-size:10.5px;color:var(--tx3);background:var(--bg);padding:3px 8px;border-radius:4px;word-break:break-all;display:block"></code>
            </div>
        </div>

        {{-- Pesan masuk --}}
        <div class="card">
            <div class="card-header ch-orange" style="justify-content:space-between">
                <span><i class="fas fa-envelope-open" style="color:#f97316;margin-right:6px"></i>Pesan Masuk Prospek</span>
                <button class="btn btn-sm btn-secondary" onclick="loadIncoming()"><i class="fas fa-sync"></i></button>
            </div>
            <div id="incoming-list" style="max-height:260px;overflow-y:auto">
                <div style="padding:24px;text-align:center;color:var(--tx3);font-size:13px">Klik Refresh untuk memuat.</div>
            </div>
        </div>

    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- Tab: Outreach                                                            --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
<div id="tab-outreach" class="tab-panel" style="display:none">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start" class="two-col">

        {{-- Templates --}}
        <div class="card">
            <div class="card-header ch-blue" style="justify-content:space-between">
                <span><i class="fas fa-comment-alt" style="color:var(--ac);margin-right:6px"></i>Template Pesan</span>
                <button class="btn btn-primary btn-xs" onclick="openAddTemplate()"><i class="fas fa-plus"></i> Tambah</button>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:7px" id="template-list">
                <div class="template-card active" onclick="selectTemplate(0)" id="tpl-0" style="border-color:var(--ac);background:#eff6ff">
                    <div style="display:flex;align-items:center;gap:6px">
                        <i class="fas fa-random" style="color:var(--ac)"></i>
                        <span class="template-name" style="font-weight:700;font-size:12px">Acak (bergantian)</span>
                    </div>
                    <div class="template-body" style="color:var(--tx3)">Tiap penerima mendapat template berbeda secara acak — lebih aman dari deteksi spam.</div>
                </div>
                @foreach($templates as $tpl)
                <div class="template-card" onclick="selectTemplate({{ $tpl->id }})" id="tpl-{{ $tpl->id }}"
                     style="{{ !$tpl->is_active ? 'opacity:.45;pointer-events:none' : '' }}">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                        <div class="template-name" style="font-weight:600;font-size:12px">{{ $tpl->name }}</div>
                        <div style="display:flex;gap:3px;flex-shrink:0" onclick="event.stopPropagation()">
                            <button class="btn btn-xs btn-secondary" title="{{ $tpl->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                onclick="toggleTemplate({{ $tpl->id }}, this)">
                                <i class="fas fa-{{ $tpl->is_active ? 'eye' : 'eye-slash' }}"></i>
                            </button>
                            <button class="btn btn-xs btn-secondary" title="Edit"
                                onclick="editTemplate({{ $tpl->id }}, {{ json_encode($tpl->name) }}, {{ json_encode($tpl->body) }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-xs btn-ghost" style="color:var(--rd)" title="Hapus"
                                onclick="deleteTemplate({{ $tpl->id }}, {{ json_encode($tpl->name) }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="template-body">{{ $tpl->body }}</div>
                </div>
                @endforeach
                <input type="hidden" id="selected-template" value="0">
            </div>
            {{-- Template stats --}}
            <div id="template-stats-wrap" style="margin:0 12px 12px;display:none">
                <div style="font-size:10px;font-weight:700;color:var(--tx2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.06em">
                    <i class="fas fa-chart-bar" style="color:var(--ac)"></i> Statistik Template
                </div>
                <div style="overflow-x:auto">
                    <table style="width:100%;border-collapse:collapse;font-size:11px">
                        <thead><tr style="background:var(--bg2);border-bottom:1px solid var(--bdr)">
                            <th style="padding:4px 8px;text-align:left;font-weight:600;color:var(--tx2)">Template</th>
                            <th style="padding:4px 8px;text-align:center;color:#3b82f6">Kirim</th>
                            <th style="padding:4px 8px;text-align:center;color:#06b6d4">Respon</th>
                            <th style="padding:4px 8px;text-align:center;color:#f97316">Minat</th>
                            <th style="padding:4px 8px;text-align:center;color:#10b981">Order</th>
                            <th style="padding:4px 8px;text-align:center;color:var(--tx2)">%</th>
                        </tr></thead>
                        <tbody id="template-stats-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kirim panel --}}
        <div style="display:flex;flex-direction:column;gap:12px">
            <div class="card">
                <div class="card-header ch-green">
                    <span><i class="fas fa-paper-plane" style="color:#16a34a;margin-right:6px"></i>Kirim Outreach</span>
                </div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                    {{-- Sisa target mini cards --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                        <div style="background:#eff6ff;border-radius:7px;padding:8px 10px;text-align:center">
                            <div style="font-size:9.5px;color:#3b82f6;font-weight:700;text-transform:uppercase;letter-spacing:.04em">Relevan Belum Kirim</div>
                            <div style="font-size:22px;font-weight:800;color:#3b82f6;line-height:1.1" id="remaining-relevant">{{ $stats['remaining_relevant'] }}</div>
                        </div>
                        <div style="background:#f0fdf4;border-radius:7px;padding:8px 10px;text-align:center">
                            <div style="font-size:9.5px;color:#16a34a;font-weight:700;text-transform:uppercase;letter-spacing:.04em">Semua Belum Kirim</div>
                            <div style="font-size:22px;font-weight:800;color:#16a34a;line-height:1.1" id="remaining-count">{{ $stats['remaining'] }}</div>
                        </div>
                    </div>

                    {{-- Filter kategori --}}
                    <div>
                        <label style="font-size:10px;font-weight:700;color:var(--tx2);text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:5px">Target Kategori:</label>
                        <div style="display:flex;gap:5px;flex-wrap:wrap" id="cat-filter-btns">
                            <button class="btn btn-sm btn-primary cat-filter-btn" data-cat="relevant" onclick="setCatFilter(this)">
                                <i class="fas fa-star"></i> Relevan
                            </button>
                            <button class="btn btn-sm btn-ghost cat-filter-btn" data-cat="" onclick="setCatFilter(this)">
                                Semua
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="category-filter" value="relevant">

                    {{-- Kirim button row --}}
                    <div class="d-flex align-center gap-8 flex-wrap">
                        <label class="text-xs text-muted">Kirim:</label>
                        <select id="send-limit" class="form-control" style="width:70px;font-size:12px">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <label class="text-xs text-muted">pesan</label>
                        <button id="btn-send" class="btn btn-sm" style="background:#16a34a;color:#fff;border-color:#16a34a" onclick="openSendPreview()">
                            <i class="fas fa-eye"></i> Preview & Kirim
                        </button>
                        <span id="daily-warn-2" style="font-size:11px;color:var(--rd);display:none">
                            <i class="fas fa-exclamation-triangle"></i> Limit hampir!
                        </span>
                    </div>
                    <div class="progress-bar-wrap" id="send-progress-wrap" style="display:none">
                        <div class="progress-bar-fill" id="send-progress-bar" style="width:0%"></div>
                    </div>
                    <div class="log-box" id="send-log" style="display:none"></div>
                    <div style="font-size:10.5px;color:var(--tx3);display:flex;align-items:flex-start;gap:5px">
                        <i class="fas fa-shield-alt" style="color:var(--gn);margin-top:2px"></i>
                        <span>Delay acak 3–8 dtk · re-verifikasi nomor basi &gt;7 hari · skip duplikat · hanya filter relevan</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- Tab: Daftar Target                                                       --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
<div id="tab-list" class="tab-panel" style="display:none">
    <div class="card">
        <div class="card-header ch-slate" style="justify-content:space-between;flex-wrap:wrap;gap:8px">
            <span><i class="fas fa-list" style="color:var(--ac);margin-right:6px"></i>Target Outreach</span>
            <div class="d-flex gap-6 flex-wrap align-center">
                <button class="btn btn-sm btn-secondary" onclick="checkDuplicates()">
                    <i class="fas fa-copy"></i> Duplikat
                </button>
                <select id="list-category" class="form-control" style="width:auto;font-size:12px" onchange="loadTargetList()">
                    <option value="relevant">Kategori Relevan</option>
                    <option value="">Semua Kategori</option>
                </select>
                <select id="list-filter" class="form-control" style="width:auto;font-size:12px" onchange="loadTargetList()">
                    <option value="pending">Belum dikirim</option>
                    <option value="sent">Sudah dikirim</option>
                    <option value="responded">Sudah respon</option>
                    <option value="interested">Berminat</option>
                    <option value="ordered">Sudah order</option>
                </select>
            </div>
        </div>
        <div id="bulk-action-bar" style="display:none;padding:7px 12px;background:var(--acl);border-bottom:1px solid var(--bdr);align-items:center;gap:8px;flex-wrap:wrap">
            <span class="text-xs fw-600" id="bulk-count-label">0 dipilih</span>
            <select id="bulk-status-select" class="form-control" style="width:auto;font-size:12px">
                <option value="">— pilih status —</option>
                <option value="none">Belum</option>
                <option value="sent">Terkirim</option>
                <option value="replied">Sudah Respon</option>
                <option value="interested">Berminat</option>
                <option value="not_interested">Tidak Berminat</option>
                <option value="ordered">Sudah Order</option>
            </select>
            <button class="btn btn-sm btn-primary" onclick="applyBulkStatus()">Terapkan</button>
            <button class="btn btn-sm btn-ghost" onclick="clearBulkSelection()">Batalkan</button>
        </div>
        <div class="card-body p-0">
            <div id="target-list-wrap">
                <p class="text-sm text-muted" style="padding:16px"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- Tab: Follow Up                                                           --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
<div id="tab-followup" class="tab-panel" style="display:none">
    <div id="followup-wrap">
        <p class="text-sm text-muted" style="padding:16px"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>
    </div>
</div>

{{-- ── Globals ─────────────────────────────────────────────────────────── --}}
<div id="ph-popup" style="display:none;position:fixed;z-index:9999;pointer-events:auto;background:#fff;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,.25);overflow:hidden;width:280px"></div>

{{-- Modal: Duplikat --}}
<div id="dup-modal" style="display:none;position:fixed;inset:0;z-index:400;background:rgba(0,0,0,.5);align-items:flex-start;justify-content:center;padding:20px 12px;overflow-y:auto">
    <div class="card" style="width:min(640px,98vw);margin:auto">
        <div class="card-header" style="justify-content:space-between">
            <span><i class="fas fa-copy" style="color:var(--ac);margin-right:6px"></i>Nomor Telepon Duplikat</span>
            <button class="btn btn-ghost btn-xs" onclick="document.getElementById('dup-modal').style.display='none'"><i class="fas fa-times"></i></button>
        </div>
        <div class="card-body p-0">
            <div id="dup-modal-content" style="padding:16px"></div>
        </div>
    </div>
</div>

{{-- Modal: Preview Kirim --}}
<div id="preview-modal" style="display:none;position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.5);align-items:flex-start;justify-content:center;padding:20px 12px;overflow-y:auto">
    <div class="card" style="width:min(620px,98vw);margin:auto">
        <div class="card-header" style="justify-content:space-between">
            <span><i class="fas fa-eye" style="color:var(--ac);margin-right:6px"></i>Preview Target Outreach</span>
            <button class="btn btn-ghost btn-xs" onclick="closePreviewModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
            <div id="preview-time-warn" style="display:none;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;padding:8px 12px;font-size:12px;color:#92400e">
                <i class="fas fa-clock"></i> <strong>Perhatian:</strong> Sekarang di luar jam kerja (07:00–21:00). Pesan mungkin tidak langsung dibaca.
            </div>
            <div>
                <div style="font-size:11px;color:var(--tx2);font-weight:600;margin-bottom:4px">Preview pesan (contoh target pertama):</div>
                <div id="preview-message-box" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;font-size:12px;white-space:pre-wrap;line-height:1.6;max-height:130px;overflow-y:auto;color:#064e3b"></div>
            </div>
            <div id="area-chips" style="display:flex;flex-wrap:wrap;gap:5px;min-height:16px"></div>
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                    <div style="font-size:11px;color:var(--tx2);font-weight:600">
                        <span id="preview-count">0</span> target <span style="color:var(--tx3);font-weight:400">— dikelompokkan per area</span>
                    </div>
                    <div style="display:flex;gap:4px">
                        <button type="button" class="btn btn-xs btn-secondary" onclick="checkAll(true)">Pilih Semua</button>
                        <button type="button" class="btn btn-xs btn-ghost" onclick="checkAll(false)">Batalkan</button>
                    </div>
                </div>
                <div id="preview-list" style="border:1px solid var(--bdr);border-radius:6px;overflow:hidden;max-height:300px;overflow-y:auto"></div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px">
                <button class="btn btn-secondary btn-sm" onclick="closePreviewModal()">Batal</button>
                <button id="btn-confirm-send" class="btn btn-sm" style="background:#16a34a;color:#fff;border-color:#16a34a" onclick="confirmSend()">
                    <i class="fas fa-paper-plane"></i> Konfirmasi Kirim (<span id="preview-selected-count">0</span>)
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Template --}}
<div id="tpl-modal" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.45);align-items:center;justify-content:center">
    <div class="card" style="width:min(520px,95vw);max-height:90vh;overflow-y:auto">
        <div class="card-header" style="justify-content:space-between">
            <span id="tpl-modal-title">Tambah Template</span>
            <button class="btn btn-ghost btn-xs" onclick="closeTplModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
            <input type="hidden" id="tpl-edit-id" value="">
            <div>
                <label class="form-label">Nama Template</label>
                <input type="text" id="tpl-name-input" class="form-control" placeholder="contoh: Perkenalan Singkat">
            </div>
            <div>
                <label class="form-label">Isi Pesan</label>
                <div style="font-size:11px;color:var(--tx3);margin-bottom:4px">
                    Variabel: <code>{nama}</code> = nama tempat &nbsp; <code>{kategori}</code> = kategori &nbsp; <code>{alamat}</code> = alamat
                </div>
                <textarea id="tpl-body-input" class="form-control" rows="8"
                    style="font-family:monospace;font-size:12px;resize:vertical"
                    placeholder="Halo {nama} 👋..."></textarea>
                <div style="font-size:11px;color:var(--tx3);margin-top:4px;text-align:right">
                    <span id="tpl-char-count">0</span> karakter
                </div>
            </div>
            <div class="d-flex gap-8 justify-end">
                <button class="btn btn-secondary btn-sm" onclick="closeTplModal()">Batal</button>
                <button class="btn btn-primary btn-sm" onclick="saveTplModal()">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
var selectedDeviceId   = '';
var selectedTemplateId = 0;
var checkAllRunning    = false;

// ── device ────────────────────────────────────────────────────────────────────
function selectDevice(id, name) {
    selectedDeviceId = id;
    document.getElementById('selected-device-id').value = id;
    document.getElementById('selected-device-name').textContent = name;
    document.querySelectorAll('.dev-pill').forEach(el => el.classList.remove('dev-selected'));
    const el = document.getElementById('dev-' + id);
    if (el) el.classList.add('dev-selected');
}

function refreshDevices() {
    fetch('{{ route("whatsapp.devices") }}')
        .then(r => r.json())
        .then(d => {
            const list = document.getElementById('device-list');
            list.innerHTML = d.devices.map(dev => `
                <div class="dev-pill${dev.status === 'ready' ? '' : ''}"
                     onclick="selectDevice('${dev.id}','${dev.name}')"
                     id="dev-${dev.id}"
                     style="${dev.status !== 'ready' ? 'opacity:.5' : ''}">
                    <span class="dev-dot ${dev.status === 'ready' ? 'dot-ready' : 'dot-off'}"></span>
                    <span>${dev.name}</span>
                    ${dev.number ? `<span style="font-size:10px;color:var(--tx3)">${dev.number}</span>` : ''}
                    <span class="dev-badge ${dev.status === 'ready' ? 'badge-ready' : 'badge-off'}">${dev.status === 'ready' ? 'Online' : 'Off'}</span>
                </div>
            `).join('');
            if (selectedDeviceId) {
                const el = document.getElementById('dev-' + selectedDeviceId);
                if (el) el.classList.add('dev-selected');
            }
        });
}

// ── tabs ──────────────────────────────────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.tab-panel').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + name).style.display = 'block';
    event.target.closest('.tab-btn').classList.add('active');
    if (name === 'list')     loadTargetList();
    if (name === 'followup') loadFollowup();
    if (name === 'outreach') loadTemplateStats();
}

// ── templates ─────────────────────────────────────────────────────────────────
function selectTemplate(id) {
    selectedTemplateId = id;
    document.getElementById('selected-template').value = id;
    document.querySelectorAll('.template-card').forEach(el => el.classList.remove('active'));
    const card = document.getElementById('tpl-' + id);
    if (card) card.classList.add('active');
}

function openAddTemplate() {
    document.getElementById('tpl-edit-id').value = '';
    document.getElementById('tpl-name-input').value = '';
    document.getElementById('tpl-body-input').value = '';
    document.getElementById('tpl-char-count').textContent = '0';
    document.getElementById('tpl-modal-title').textContent = 'Tambah Template';
    document.getElementById('tpl-modal').style.display = 'flex';
}

function editTemplate(id, name, body) {
    document.getElementById('tpl-edit-id').value = id;
    document.getElementById('tpl-name-input').value = name;
    document.getElementById('tpl-body-input').value = body;
    document.getElementById('tpl-char-count').textContent = body.length;
    document.getElementById('tpl-modal-title').textContent = 'Edit Template';
    document.getElementById('tpl-modal').style.display = 'flex';
}

function closeTplModal() { document.getElementById('tpl-modal').style.display = 'none'; }

document.getElementById('tpl-body-input')?.addEventListener('input', function() {
    document.getElementById('tpl-char-count').textContent = this.value.length;
});

async function saveTplModal() {
    const id   = document.getElementById('tpl-edit-id').value;
    const name = document.getElementById('tpl-name-input').value.trim();
    const body = document.getElementById('tpl-body-input').value.trim();
    if (!name || !body) { alert('Nama dan isi pesan wajib diisi'); return; }
    const url    = id ? `/mafaza/public/whatsapp/templates/${id}` : '/mafaza/public/whatsapp/templates';
    const method = id ? 'PUT' : 'POST';
    const resp   = await fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ name, body }),
    });
    const d = await resp.json();
    if (d.status === 'ok') { closeTplModal(); location.reload(); }
    else alert('Gagal menyimpan template');
}

async function deleteTemplate(id, name) {
    if (!confirm(`Hapus template "${name}"?`)) return;
    const resp = await fetch(`/mafaza/public/whatsapp/templates/${id}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    });
    const d = await resp.json();
    if (d.status === 'ok') location.reload();
}

async function toggleTemplate(id, btn) {
    const resp = await fetch(`/mafaza/public/whatsapp/templates/${id}/toggle`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    });
    const d = await resp.json();
    if (d.status === 'ok') location.reload();
}

// ── cek WA ────────────────────────────────────────────────────────────────────
function logCheck(msg) {
    const box = document.getElementById('check-log');
    box.style.display = 'block';
    box.textContent += msg + '\n';
    box.scrollTop = box.scrollHeight;
}

function updateCheckProgress(checked, total) {
    const pct = total > 0 ? Math.min(100, Math.round(checked / total * 100)) : 0;
    document.getElementById('check-progress-wrap').style.display = 'block';
    document.getElementById('check-progress-bar').style.width = pct + '%';
}

async function runCheckWA(loopAll = false) {
    if (!selectedDeviceId) { alert('Pilih device terlebih dahulu'); return; }
    const btn   = document.getElementById('btn-check-wa');
    const limit = parseInt(document.getElementById('check-limit').value);
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengecek...';
    document.getElementById('check-log').textContent = '';

    const uncheckedTotal = parseInt(document.getElementById('unchecked-count').textContent) || 0;
    let totalChecked = 0;

    const doOneBatch = async () => {
        const resp = await fetch('{{ route("whatsapp.check-wa") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ device_id: selectedDeviceId, limit })
        });
        const d = await resp.json();
        if (d.status === 'ok') {
            totalChecked += d.results.checked;
            logCheck(`✓ Batch: ${d.results.has_wa} punya WA, ${d.results.no_wa} tidak, ${d.results.error} error`);
            logCheck(`  Sisa: ${d.remaining}`);
            document.getElementById('unchecked-count').textContent = d.remaining;
            document.getElementById('stat-unchecked').textContent = d.remaining;
            updateCheckProgress(uncheckedTotal - d.remaining, uncheckedTotal);
            refreshStats();
            return d.remaining;
        }
        throw new Error('Gagal');
    };

    try {
        if (loopAll) {
            checkAllRunning = true;
            let remaining = 1;
            while (remaining > 0 && checkAllRunning) {
                remaining = await doOneBatch();
                if (remaining > 0) await new Promise(r => setTimeout(r, 2000));
            }
            logCheck(checkAllRunning ? '✅ Semua nomor selesai dicek!' : '⏹ Dihentikan.');
            checkAllRunning = false;
        } else {
            await doOneBatch();
        }
    } catch(e) {
        logCheck('✗ Error: ' + e.message);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-play"></i> Mulai Cek';
}

function runCheckWAAll() {
    if (!selectedDeviceId) { alert('Pilih device terlebih dahulu'); return; }
    if (!confirm('Cek semua nomor yang belum dicek? Ini bisa memakan waktu.')) return;
    document.getElementById('check-limit').value = 50;
    runCheckWA(true);
}

// ── send outreach ─────────────────────────────────────────────────────────────
function logSend(msg) {
    const box = document.getElementById('send-log');
    box.style.display = 'block';
    box.textContent += msg + '\n';
    box.scrollTop = box.scrollHeight;
}

function setCatFilter(btn) {
    document.querySelectorAll('.cat-filter-btn').forEach(b => {
        b.classList.remove('btn-primary'); b.classList.add('btn-ghost');
    });
    btn.classList.add('btn-primary'); btn.classList.remove('btn-ghost');
    document.getElementById('category-filter').value = btn.dataset.cat;
}

function updateDailyBar(sentToday, dailyLimit) {
    const pct = Math.min(100, Math.round(sentToday / dailyLimit * 100));
    const bar = document.getElementById('daily-bar');
    bar.style.width    = pct + '%';
    bar.style.background = pct >= 90 ? '#ef4444' : pct >= 70 ? '#f97316' : 'var(--ac)';
    document.getElementById('sent-today').textContent = sentToday;
    const warn = document.getElementById('daily-warn');
    if (warn) warn.style.display = pct >= 80 ? 'inline' : 'none';
}

// ── Preview Modal ─────────────────────────────────────────────────────────────
let previewData = [];

async function openSendPreview() {
    if (!selectedDeviceId) { alert('Pilih device terlebih dahulu'); return; }
    const sentToday  = parseInt(document.getElementById('sent-today').textContent) || 0;
    const dailyLimit = parseInt(document.getElementById('daily-limit').textContent) || 50;
    if (sentToday >= dailyLimit) { alert(`Limit harian ${dailyLimit} pesan sudah tercapai. Coba lagi besok.`); return; }

    const catFilter = document.getElementById('category-filter').value;
    const remaining = catFilter === 'relevant'
        ? parseInt(document.getElementById('remaining-relevant').textContent) || 0
        : parseInt(document.getElementById('remaining-count').textContent) || 0;
    if (remaining === 0) { alert('Tidak ada target tersisa untuk filter ini.'); return; }

    const btn = document.getElementById('btn-send');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';

    const h = new Date().getHours();
    document.getElementById('preview-time-warn').style.display = (h < 7 || h >= 21) ? 'block' : 'none';

    try {
        const limit = parseInt(document.getElementById('send-limit').value);
        const resp  = await fetch('{{ route("whatsapp.preview-targets") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ limit, category_filter: catFilter, template_id: selectedTemplateId })
        });
        const d = await resp.json();
        if (!d.data || d.data.length === 0) {
            alert('Tidak ada target valid. Semua sudah dikirim, atau masuk daftar chain besar yang dilewati.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-eye"></i> Preview & Kirim';
            return;
        }
        previewData = d.data;
        document.getElementById('preview-count').textContent = d.count;
        document.getElementById('preview-message-box').textContent = d.sample_message || '(tidak ada template aktif)';
        renderPreviewList();
        updateSelectedCount();
        document.getElementById('preview-modal').style.display = 'flex';
    } catch(e) {
        alert('Gagal memuat preview: ' + e.message);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-eye"></i> Preview & Kirim';
}

function closePreviewModal() { document.getElementById('preview-modal').style.display = 'none'; }

function checkAll(checked) {
    previewData.forEach(p => {
        const el = document.getElementById('pchk-' + p.id);
        if (el) el.checked = checked;
    });
    updateSelectedCount();
}

var areaColors  = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ec4899','#8b5cf6','#ef4444','#14b8a6'];
var areaColorMap = {};
var areaColorIdx  = 0;
function areaColor(area) {
    if (!areaColorMap[area]) {
        areaColorMap[area] = areaColors[areaColorIdx % areaColors.length];
        areaColorIdx++;
    }
    return areaColorMap[area];
}

function renderPreviewList() {
    areaColorMap = {}; areaColorIdx = 0;
    var groups = {};
    previewData.forEach(function(p) {
        var a = p.area || 'Area tidak diketahui';
        if (!groups[a]) groups[a] = [];
        groups[a].push(p);
    });
    var areaNames = Object.keys(groups).sort();

    var chipsEl = document.getElementById('area-chips');
    chipsEl.innerHTML = areaNames.map(function(a) {
        var c = areaColor(a);
        return `<button type="button" onclick="checkArea('${a.replace(/'/g,"\\'")}', true)"
            style="font-size:10px;padding:3px 9px;border-radius:20px;border:1.5px solid ${c};background:${c}18;color:${c};cursor:pointer;font-weight:600;white-space:nowrap"
            title="Pilih semua di area ini">
            <i class="fas fa-map-marker-alt" style="font-size:9px"></i>
            ${escHtml(a)} <span style="opacity:.7">(${groups[a].length})</span>
        </button>`;
    }).join('');

    var listEl = document.getElementById('preview-list');
    var html = '';
    areaNames.forEach(function(area) {
        var c = areaColor(area);
        var items = groups[area];
        html += `<div style="background:${c}12;border-bottom:2px solid ${c}40;padding:5px 10px;display:flex;align-items:center;justify-content:space-between;gap:8px;position:sticky;top:0;z-index:2">
            <div style="display:flex;align-items:center;gap:6px">
                <span style="width:8px;height:8px;border-radius:50%;background:${c};flex-shrink:0;display:inline-block"></span>
                <span style="font-size:11px;font-weight:700;color:${c}">${escHtml(area)}</span>
                <span style="font-size:10px;color:var(--tx3)">${items.length} tempat</span>
            </div>
            <div style="display:flex;gap:4px">
                <button type="button" onclick="checkArea('${area.replace(/'/g,"\\'")}', true)"
                    style="font-size:10px;padding:2px 7px;border-radius:4px;border:1px solid ${c};background:${c}20;color:${c};cursor:pointer">Pilih</button>
                <button type="button" onclick="checkArea('${area.replace(/'/g,"\\'")}', false)"
                    style="font-size:10px;padding:2px 7px;border-radius:4px;border:1px solid var(--bdr);background:transparent;color:var(--tx3);cursor:pointer">Hapus</button>
            </div>
        </div>`;

        items.forEach(function(p, i) {
            var border = (i < items.length - 1) ? 'border-bottom:1px solid var(--bdr)' : '';
            html += `<div id="prow-${p.id}" style="display:flex;align-items:center;gap:10px;padding:6px 10px;${border};transition:.1s"
                onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background=''">
                <input type="checkbox" id="pchk-${p.id}" data-area="${escHtml(area)}" checked onchange="updateSelectedCount()" style="width:14px;height:14px;flex-shrink:0;cursor:pointer">
                ${p.thumb
                    ? `<div class="ph-wrap" data-imgs="${escHtml((p.images||[]).join('|'))}" style="width:32px;height:32px;border-radius:5px;flex-shrink:0;cursor:zoom-in">
                           <img src="${escHtml(p.thumb)}" loading="lazy" style="width:32px;height:32px;border-radius:5px;object-fit:cover;border:1px solid var(--bdr);display:block"
                               onerror="this.closest('.ph-wrap').outerHTML='<div style=\\'width:32px;height:32px;border-radius:5px;background:var(--bg2);flex-shrink:0;display:flex;align-items:center;justify-content:center;color:var(--tx3);font-size:12px\\'><i class=\\'fas fa-store\\'></i></div>'">
                       </div>`
                    : `<div style="width:32px;height:32px;border-radius:5px;background:var(--bg2);flex-shrink:0;display:flex;align-items:center;justify-content:center;color:var(--tx3);font-size:12px"><i class="fas fa-store"></i></div>`
                }
                <div style="flex:1;min-width:0;cursor:pointer" onclick="document.getElementById('pchk-${p.id}').click()">
                    <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(p.name)}</div>
                    <div style="font-size:10px;color:var(--tx3)">${escHtml(p.category || '—')} · ${escHtml(p.phone)}</div>
                </div>
                <div style="font-size:10px;color:var(--tx3);flex-shrink:0;white-space:nowrap;text-align:right;margin-right:4px">
                    ${p.rating ? '★' + p.rating : ''}${p.review_count ? '<br><span style="font-size:9px">' + p.review_count + ' ulasan</span>' : ''}
                </div>
                <button type="button" title="Tandai tidak relevan"
                    style="flex-shrink:0;border:none;background:transparent;cursor:pointer;color:var(--tx3);padding:2px 4px;font-size:11px;line-height:1"
                    onmouseover="this.style.color='var(--rd)'" onmouseout="this.style.color='var(--tx3)'"
                    onclick="markIrrelevantFromPreview(${p.id}, this)">
                    <i class="fas fa-ban"></i>
                </button>
            </div>`;
        });
    });

    listEl.innerHTML = html;
    updateSelectedCount();
}

function checkArea(area, checked) {
    document.querySelectorAll('#preview-list input[type=checkbox][data-area]').forEach(function(cb) {
        if (cb.dataset.area === area) cb.checked = checked;
    });
    updateSelectedCount();
}

async function markIrrelevantFromPreview(placeId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    try {
        const resp = await fetch(`/places/${placeId}/toggle-relevance`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
        });
        const data = await resp.json();
        if (resp.ok && data.is_valid === false) {
            const row = document.getElementById('prow-' + placeId);
            row.style.transition = 'opacity .25s';
            row.style.opacity = '0';
            setTimeout(() => {
                previewData = previewData.filter(p => p.id !== placeId);
                renderPreviewList();
                document.getElementById('preview-count').textContent = previewData.length;
            }, 260);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-ban"></i>';
            alert('Gagal menandai tidak relevan.');
        }
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-ban"></i>';
        alert('Error: ' + e.message);
    }
}

function updateSelectedCount() {
    const n = previewData.filter(p => document.getElementById('pchk-' + p.id)?.checked).length;
    document.getElementById('preview-selected-count').textContent = n;
    document.getElementById('btn-confirm-send').disabled = n === 0;
}

async function confirmSend() {
    const placeIds = previewData.filter(p => document.getElementById('pchk-' + p.id)?.checked).map(p => p.id);
    if (!placeIds.length) { alert('Tidak ada target yang dipilih.'); return; }
    closePreviewModal();
    await executeSend(placeIds);
}

async function executeSend(placeIds) {
    const catFilter = document.getElementById('category-filter').value;
    const btn = document.getElementById('btn-send');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    document.getElementById('send-log').textContent = '';
    document.getElementById('send-progress-wrap').style.display = 'block';
    document.getElementById('send-progress-bar').style.width = '10%';

    try {
        logSend(`Mengirim ke ${placeIds.length} target... (delay 3–8 detik per pesan)`);
        const resp = await fetch('{{ route("whatsapp.send-outreach") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                device_id:       selectedDeviceId,
                template_id:     selectedTemplateId,
                place_ids:       placeIds,
                category_filter: catFilter,
            })
        });
        const d = await resp.json();
        if (d.status === 'ok') {
            document.getElementById('send-progress-bar').style.width = '100%';
            const skipNote = d.results.skipped_stale > 0 ? ` | Dilewati (basi): ${d.results.skipped_stale}` : '';
            logSend(`✓ Terkirim: ${d.results.sent} | Gagal: ${d.results.failed}${skipNote}`);
            logSend(`Sisa target: ${d.remaining}`);
            document.getElementById('remaining-count').textContent = d.remaining;
            updateDailyBar(d.sent_today, d.daily_limit);
            refreshStats();
        } else {
            logSend('✗ ' + (d.error || 'Gagal'));
        }
    } catch(e) {
        logSend('✗ Error: ' + e.message);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-eye"></i> Preview & Kirim';
}

// ── target list ───────────────────────────────────────────────────────────────
let selectedIds = new Set();

function updateBulkBar() {
    const bar = document.getElementById('bulk-action-bar');
    const lbl = document.getElementById('bulk-count-label');
    if (selectedIds.size > 0) {
        bar.style.display = 'flex';
        lbl.textContent = selectedIds.size + ' dipilih';
    } else {
        bar.style.display = 'none';
    }
}

function clearBulkSelection() {
    selectedIds.clear();
    document.querySelectorAll('.bulk-chk').forEach(c => c.checked = false);
    const allChk = document.getElementById('select-all-chk');
    if (allChk) allChk.checked = false;
    updateBulkBar();
}

async function applyBulkStatus() {
    const status = document.getElementById('bulk-status-select').value;
    if (!status) { alert('Pilih status terlebih dahulu'); return; }
    if (!selectedIds.size) { alert('Tidak ada yang dipilih'); return; }
    if (!confirm(`Terapkan status "${status}" ke ${selectedIds.size} tempat?`)) return;
    const resp = await fetch('{{ route("whatsapp.bulk-status") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ ids: [...selectedIds], status })
    });
    const d = await resp.json();
    if (d.status === 'ok') {
        alert(`Berhasil update ${d.updated} tempat.`);
        selectedIds.clear();
        loadTargetList();
        refreshStats();
    } else {
        alert('Gagal: ' + JSON.stringify(d));
    }
}

function loadTargetList() {
    const filter   = document.getElementById('list-filter').value;
    const category = document.getElementById('list-category').value;
    const wrap     = document.getElementById('target-list-wrap');
    wrap.innerHTML = '<p class="text-sm text-muted" style="padding:16px"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>';
    selectedIds.clear();
    updateBulkBar();

    fetch(`{{ route('whatsapp.target-list') }}?filter=${filter}&category=${encodeURIComponent(category)}`)
        .then(r => r.json())
        .then(d => {
            if (!d.data || d.data.length === 0) {
                wrap.innerHTML = '<p class="text-sm text-muted" style="padding:16px">Tidak ada data untuk filter ini.</p>';
                return;
            }
            const statusBadge = {
                sent:           '<span style="background:#dbeafe;color:#1d4ed8;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px">Terkirim</span>',
                replied:        '<span style="background:#cffafe;color:#0e7490;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px">Respon</span>',
                responded:      '<span style="background:#cffafe;color:#0e7490;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px">Respon</span>',
                interested:     '<span style="background:#ffedd5;color:#c2410c;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px">Berminat</span>',
                not_interested: '<span style="background:#f3f4f6;color:#6b7280;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px">Tidak Minat</span>',
                ordered:        '<span style="background:#dcfce7;color:#15803d;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px">Order ✓</span>',
            };
            const scoreColor = s => s >= 50 ? '#10b981' : s >= 30 ? '#f97316' : '#9ca3af';
            const rows = d.data.map(p => {
                const score = p.priority_score || 0;
                const waLink = p.phone ? `<a href="https://wa.me/${p.phone.replace(/\D/g,'')}" target="_blank" class="btn btn-xs" style="background:#22c55e;color:#fff;border-color:#22c55e" title="Buka WA"><i class="fab fa-whatsapp"></i></a>` : '';
                const detailLink = `<a href="/mafaza/public/places/${p.id}" target="_blank" class="btn btn-xs btn-ghost" title="Detail"><i class="fas fa-eye"></i></a>`;
                const actionBtn = p.outreach_status === 'sent'
                    ? `<button class="btn btn-xs btn-secondary" onclick="markStatus(${p.id},'replied',this)">↩ Respon</button>`
                    : (p.outreach_status === 'replied' || p.outreach_status === 'responded')
                    ? `<button class="btn btn-xs" style="background:#f97316;color:#fff;border-color:#f97316" onclick="markStatus(${p.id},'interested',this)">👍</button>`
                    : '';
                const areaHtml = p.area
                    ? `<div style="font-size:9px;color:#6366f1;font-weight:600;margin-top:1px"><i class="fas fa-map-marker-alt" style="font-size:8px"></i> ${escHtml(p.area)}</div>`
                    : '';
                return `<tr style="border-bottom:1px solid var(--bdr)">
                    <td style="padding:6px 8px;text-align:center">
                        <input type="checkbox" class="bulk-chk" data-id="${p.id}" onchange="onBulkChkChange(this)" style="width:13px;height:13px;cursor:pointer">
                    </td>
                    <td style="padding:6px 10px">
                        <div style="font-size:12px;font-weight:600">${escHtml(p.name)}</div>
                        <div style="font-size:10px;color:var(--tx3)">${escHtml(p.category || '—')}</div>
                        ${areaHtml}
                    </td>
                    <td style="padding:6px 10px;font-size:11px;color:var(--tx2)">${escHtml(p.phone || '—')}</td>
                    <td style="padding:6px 10px;text-align:center">
                        <span style="font-size:12px;font-weight:700;color:${scoreColor(score)}">${score}</span>
                    </td>
                    <td style="padding:6px 10px">${statusBadge[p.outreach_status] || '<span style="color:var(--tx3);font-size:10px">Belum</span>'}</td>
                    <td style="padding:6px 10px;font-size:10px;color:var(--tx3)">${p.outreach_sent_at ? p.outreach_sent_at.replace('T',' ').slice(0,10) : '—'}</td>
                    <td style="padding:6px 10px"><div style="display:flex;gap:3px">${waLink}${detailLink}${actionBtn}</div></td>
                </tr>`;
            }).join('');
            wrap.innerHTML = `
                <div style="font-size:11px;color:var(--tx2);padding:5px 12px;border-bottom:1px solid var(--bdr);background:var(--bg2)">${d.count} tempat</div>
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead><tr style="border-bottom:2px solid var(--bdr);background:var(--bg2)">
                        <th style="padding:6px 8px;text-align:center"><input type="checkbox" id="select-all-chk" onchange="selectAllBulk(this)" style="width:13px;height:13px;cursor:pointer"></th>
                        <th style="padding:6px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--tx2)">Nama / Kategori / Area</th>
                        <th style="padding:6px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--tx2)">Telepon</th>
                        <th style="padding:6px 10px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--tx2)">Score</th>
                        <th style="padding:6px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--tx2)">Status</th>
                        <th style="padding:6px 10px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--tx2)">Kirim</th>
                        <th style="padding:6px 10px"></th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>
                </div>`;
        })
        .catch(() => { wrap.innerHTML = '<p style="padding:16px;color:var(--rd)">Gagal memuat.</p>'; });
}

function onBulkChkChange(chk) {
    const id = parseInt(chk.dataset.id);
    if (chk.checked) selectedIds.add(id);
    else selectedIds.delete(id);
    updateBulkBar();
}

function selectAllBulk(chk) {
    document.querySelectorAll('.bulk-chk').forEach(c => {
        c.checked = chk.checked;
        const id = parseInt(c.dataset.id);
        if (chk.checked) selectedIds.add(id);
        else selectedIds.delete(id);
    });
    updateBulkBar();
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function markStatus(id, status, btn) {
    openGRespModal(id, status, null, function() { loadTargetList(); });
}

// ── stats refresh ─────────────────────────────────────────────────────────────
function refreshStats() {
    fetch('{{ route("whatsapp.stats") }}')
        .then(r => r.json())
        .then(d => {
            document.getElementById('stat-has-wa').textContent        = d.has_wa;
            document.getElementById('stat-no-wa').textContent         = d.no_wa;
            document.getElementById('stat-unchecked').textContent     = d.unchecked;
            document.getElementById('stat-sent').textContent          = d.outreach_sent;
            document.getElementById('stat-replied').textContent       = d.replied;
            document.getElementById('stat-interested').textContent    = d.interested;
            document.getElementById('stat-not-interested').textContent= d.not_interested ?? 0;
            document.getElementById('stat-ordered').textContent       = d.ordered ?? 0;
            document.getElementById('unchecked-count').textContent    = d.unchecked;
            document.getElementById('remaining-count').textContent    = d.remaining;
            if (d.remaining_relevant !== undefined)
                document.getElementById('remaining-relevant').textContent = d.remaining_relevant;
        });
}
setInterval(refreshStats, 30000);

// ── follow up ─────────────────────────────────────────────────────────────────
function loadFollowup() {
    const wrap = document.getElementById('followup-wrap');
    wrap.innerHTML = '<p class="text-sm text-muted" style="padding:16px"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>';

    fetch('{{ route("whatsapp.followup-list") }}')
        .then(r => r.json())
        .then(d => {
            const daysBadge = days => {
                if (!days && days !== 0) return '<span style="font-size:10px;color:var(--tx3)">—</span>';
                const col = days > 7 ? '#ef4444' : days > 3 ? '#f97316' : '#94a3b8';
                return `<span style="background:${col};color:#fff;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700">${days} hari lalu</span>`;
            };
            const buildTable = (items, emptyMsg) => {
                if (!items || items.length === 0) return `<p class="text-sm text-muted" style="padding:12px 14px">${emptyMsg}</p>`;
                const rows = items.map(p => {
                    const waLink = p.phone ? `<a href="https://wa.me/${p.phone.replace(/\D/g,'')}" target="_blank" class="btn btn-xs" style="background:#22c55e;color:#fff;border-color:#22c55e"><i class="fab fa-whatsapp"></i></a>` : '';
                    return `<tr style="border-bottom:1px solid var(--bdr)">
                        <td style="padding:7px 12px">
                            <div style="font-size:12px;font-weight:600">${escHtml(p.name)}</div>
                            <div style="font-size:10px;color:var(--tx3)">${escHtml(p.category || '—')}</div>
                        </td>
                        <td style="padding:7px 12px">${daysBadge(p.days_since)}</td>
                        <td style="padding:7px 12px">
                            <div style="display:flex;gap:4px;flex-wrap:wrap">
                                ${waLink}
                                <button class="btn btn-xs btn-secondary" onclick="markStatusInline(${p.id},'replied',this)">Respon</button>
                                <button class="btn btn-xs" style="background:#f97316;color:#fff;border-color:#f97316" onclick="markStatusInline(${p.id},'interested',this)">Berminat</button>
                                <button class="btn btn-xs" style="background:#9ca3af;color:#fff;border-color:#9ca3af" onclick="markStatusInline(${p.id},'not_interested',this)">Tidak</button>
                                <button class="btn btn-xs" style="background:#10b981;color:#fff;border-color:#10b981" onclick="markStatusInline(${p.id},'ordered',this)">Order</button>
                            </div>
                        </td>
                    </tr>`;
                }).join('');
                return `<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse">
                    <thead><tr style="background:var(--bg2);border-bottom:2px solid var(--bdr)">
                        <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tx2)">Nama / Kategori</th>
                        <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tx2)">Dikirim</th>
                        <th style="padding:6px 12px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;color:var(--tx2)">Aksi</th>
                    </tr></thead><tbody>${rows}</tbody></table></div>`;
            };

            wrap.innerHTML = `
                <div class="card mb-12">
                    <div class="card-header" style="background:linear-gradient(135deg,#fee2e2,#fff1f2)">
                        <span><i class="fas fa-exclamation-circle" style="color:#ef4444;margin-right:6px"></i>
                        Perlu Di-Follow Up</span>
                        <span style="background:#ef4444;color:#fff;border-radius:10px;padding:1px 8px;font-size:11px;margin-left:6px">${d.followup.length}</span>
                        <span class="text-xs text-muted" style="margin-left:auto">Dikirim &gt;3 hari lalu, belum respon</span>
                    </div>
                    <div class="card-body p-0">${buildTable(d.followup, 'Tidak ada yang perlu di-follow up.')}</div>
                </div>
                <div class="card mb-12">
                    <div class="card-header" style="background:linear-gradient(135deg,#fff7ed,#fff)">
                        <span><i class="fas fa-thumbs-up" style="color:#f97316;margin-right:6px"></i>
                        Berminat – Belum Order</span>
                        <span style="background:#f97316;color:#fff;border-radius:10px;padding:1px 8px;font-size:11px;margin-left:6px">${d.interested.length}</span>
                    </div>
                    <div class="card-body p-0">${buildTable(d.interested, 'Tidak ada yang berminat saat ini.')}</div>
                </div>
                <div class="card">
                    <div class="card-header" style="background:linear-gradient(135deg,#eff6ff,#fff)">
                        <span><i class="fas fa-reply" style="color:var(--ac);margin-right:6px"></i>
                        Sudah Respon – Belum Berminat</span>
                        <span style="background:var(--ac);color:#fff;border-radius:10px;padding:1px 8px;font-size:11px;margin-left:6px">${d.replied.length}</span>
                    </div>
                    <div class="card-body p-0">${buildTable(d.replied, 'Tidak ada yang sudah respon saat ini.')}</div>
                </div>`;
        })
        .catch(() => { wrap.innerHTML = '<p style="padding:16px;color:var(--rd)">Gagal memuat data follow up.</p>'; });
}

function markStatusInline(id, status, btn) {
    btn.disabled = true;
    fetch(`{{ url('/whatsapp/mark-status') }}/${id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status })
    }).then(r => r.json()).then(d => {
        if (d.status === 'ok') { loadFollowup(); refreshStats(); }
    }).catch(() => { btn.disabled = false; });
}

// ── template stats ────────────────────────────────────────────────────────────
function loadTemplateStats() {
    fetch('{{ route("whatsapp.template-stats") }}')
        .then(r => r.json())
        .then(d => {
            if (!d.data || d.data.length === 0) return;
            document.getElementById('template-stats-wrap').style.display = 'block';
            const rows = d.data.map(t => {
                const conv = t.sent > 0 ? (t.ordered / t.sent * 100).toFixed(1) : '0';
                return `<tr style="border-bottom:1px solid var(--bdr)">
                    <td style="padding:5px 8px;font-size:11.5px;font-weight:500">${escHtml(t.template_name)}</td>
                    <td style="padding:5px 8px;text-align:center;font-size:11.5px">${t.sent}</td>
                    <td style="padding:5px 8px;text-align:center;font-size:11.5px;color:#06b6d4">${t.replied}</td>
                    <td style="padding:5px 8px;text-align:center;font-size:11.5px;color:#f97316">${t.interested}</td>
                    <td style="padding:5px 8px;text-align:center;font-size:11.5px;color:#10b981">${t.ordered}</td>
                    <td style="padding:5px 8px;text-align:center;font-size:11.5px;font-weight:700;color:${parseFloat(conv)>=5?'#10b981':'#6b7280'}">${conv}%</td>
                </tr>`;
            }).join('');
            document.getElementById('template-stats-body').innerHTML = rows;
        });
}

// ── re-check WA ───────────────────────────────────────────────────────────────
function loadRecheckCount() {
    fetch('{{ route("whatsapp.recheck-count") }}')
        .then(r => r.json())
        .then(d => { document.getElementById('recheck-count').textContent = d.count; });
}

async function runReCheckWA() {
    if (!selectedDeviceId) { alert('Pilih device terlebih dahulu'); return; }
    const box = document.getElementById('recheck-log');
    box.style.display = 'block';
    box.textContent = 'Memulai re-cek...\n';
    try {
        const resp = await fetch('{{ route("whatsapp.recheck-wa") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ device_id: selectedDeviceId, limit: 30 })
        });
        const d = await resp.json();
        if (d.status === 'ok') {
            box.textContent += `✓ Selesai: ${d.results.has_wa} punya WA, ${d.results.no_wa} tidak, ${d.results.error} error\n`;
            box.textContent += `Sisa: ${d.remaining}\n`;
            document.getElementById('recheck-count').textContent = d.remaining;
            refreshStats();
        } else { box.textContent += '✗ Error\n'; }
    } catch(e) { box.textContent += '✗ Error: ' + e.message + '\n'; }
}

// ── duplicates ────────────────────────────────────────────────────────────────
function checkDuplicates() {
    document.getElementById('dup-modal').style.display = 'flex';
    document.getElementById('dup-modal-content').innerHTML = '<p class="text-sm text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>';
    fetch('{{ route("whatsapp.duplicates") }}')
        .then(r => r.json())
        .then(d => {
            if (d.count === 0) {
                document.getElementById('dup-modal-content').innerHTML = '<p class="text-sm text-muted" style="padding:8px 0">Tidak ditemukan nomor duplikat.</p>';
                return;
            }
            const rows = d.data.map(dup => {
                const entries = dup.entries.map((e, i) => {
                    const delBtn = i > 0
                        ? `<button class="btn btn-xs" style="color:var(--rd);border-color:var(--rd)" onclick="deletePlace(${e.id}, this)"><i class="fas fa-trash"></i></button>`
                        : '<span class="text-xs text-muted">pertama</span>';
                    const detailLink = `<a href="/mafaza/public/places/${e.id}" target="_blank" class="btn btn-xs btn-ghost"><i class="fas fa-eye"></i></a>`;
                    return `<div style="display:flex;align-items:center;gap:8px;padding:4px 0;${i>0?'border-top:1px solid var(--bdr)':''}">
                        <div style="flex:1;font-size:12px">${escHtml(e.name)} <span class="text-xs text-muted">#${e.id}</span></div>
                        <div style="display:flex;gap:4px">${detailLink}${delBtn}</div>
                    </div>`;
                }).join('');
                return `<div style="border:1px solid var(--bdr);border-radius:6px;padding:10px 12px;margin-bottom:8px">
                    <div style="font-size:11px;font-weight:600;color:var(--tx2);margin-bottom:6px">
                        <i class="fas fa-phone" style="color:var(--ac)"></i> ${escHtml(dup.phone)} — ${dup.count} entri
                    </div>
                    ${entries}
                </div>`;
            }).join('');
            document.getElementById('dup-modal-content').innerHTML = `
                <div style="font-size:12px;color:var(--tx2);margin-bottom:12px">
                    <strong>${d.count}</strong> nomor duplikat. Hapus entri ganda (pertahankan yang pertama).
                </div>
                <div style="max-height:60vh;overflow-y:auto">${rows}</div>`;
        })
        .catch(() => {
            document.getElementById('dup-modal-content').innerHTML = '<p style="color:var(--rd)">Gagal memuat data duplikat.</p>';
        });
}

async function deletePlace(id, btn) {
    if (!confirm('Hapus tempat ini dari database?')) return;
    btn.disabled = true;
    const resp = await fetch(`/mafaza/public/places/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });
    if (resp.ok || resp.status === 302 || resp.redirected) {
        checkDuplicates();
    } else {
        btn.disabled = false;
        alert('Gagal menghapus.');
    }
}

// ── webhook ───────────────────────────────────────────────────────────────────
async function checkWebhookStatus() {
    try {
        const d = await fetch('{{ route("whatsapp.webhook-status") }}').then(r => r.json());
        const badge    = document.getElementById('webhook-badge');
        const btnReg   = document.getElementById('btn-webhook-reg');
        const btnUnreg = document.getElementById('btn-webhook-unreg');
        const urlEl    = document.getElementById('webhook-url');
        if (urlEl) urlEl.textContent = d.webhook_url;
        if (d.registered) {
            badge.textContent = '✅ Webhook Terdaftar';
            badge.style.background = '#dcfce7'; badge.style.color = '#16a34a';
            if (btnReg)   btnReg.style.display   = 'none';
            if (btnUnreg) btnUnreg.style.display = 'inline-flex';
        } else {
            badge.textContent = '⚠️ Belum Terdaftar';
            badge.style.background = '#fef3c7'; badge.style.color = '#92400e';
            if (btnReg)   btnReg.style.display   = 'inline-flex';
            if (btnUnreg) btnUnreg.style.display = 'none';
        }
    } catch(e) {
        const badge = document.getElementById('webhook-badge');
        if (badge) { badge.textContent = 'WA API tidak terhubung'; badge.style.background='#fee2e2'; badge.style.color='#dc2626'; }
    }
}

async function registerWebhook() {
    const r = await fetch('{{ route("whatsapp.register-webhook") }}', {
        method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
    }).then(r => r.json());
    if (r.status === 'ok') { alert('Webhook berhasil didaftarkan!'); checkWebhookStatus(); }
    else alert('Gagal mendaftarkan webhook.');
}

async function unregisterWebhook() {
    if (!confirm('Cabut webhook? Pesan masuk tidak akan diproses otomatis.')) return;
    const r = await fetch('{{ route("whatsapp.unregister-webhook") }}', {
        method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
    }).then(r => r.json());
    if (r.status === 'ok') { alert('Webhook dicabut.'); checkWebhookStatus(); }
    else alert('Gagal.');
}

async function loadIncoming() {
    const el = document.getElementById('incoming-list');
    if (!el) return;
    el.innerHTML = '<div style="padding:20px;text-align:center;color:var(--tx3)">Memuat...</div>';
    const d = await fetch('{{ route("whatsapp.incoming-messages") }}').then(r => r.json()).catch(() => ({data:[]}));
    if (!d.data.length) {
        el.innerHTML = '<div style="padding:24px;text-align:center;color:var(--tx3);font-size:13px">Belum ada pesan masuk dari prospek.</div>';
        return;
    }
    const statusColor = {sent:'var(--or)',replied:'#06b6d4',interested:'#16a34a',ordered:'#7c3aed',not_interested:'var(--rd)'};
    const statusLabel = {sent:'Terkirim',replied:'Respon',interested:'Berminat',ordered:'Order',not_interested:'Tdk Minat'};
    el.innerHTML = d.data.map(m => `
        <div style="padding:9px 12px;border-bottom:1px solid var(--bdr);display:flex;gap:9px;align-items:flex-start">
            <div style="width:32px;height:32px;border-radius:50%;background:var(--acl);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;font-weight:700;color:var(--ac)">
                ${(m.place?.name || m.from_number).charAt(0).toUpperCase()}
            </div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;flex-wrap:wrap">
                    <span style="font-size:12px;font-weight:600">${m.place?.name || m.from_number}</span>
                    ${m.place?.outreach_status ? `<span style="font-size:10px;font-weight:700;color:${statusColor[m.place.outreach_status]||'var(--tx3)'};">${statusLabel[m.place.outreach_status]||m.place.outreach_status}</span>` : ''}
                    ${m.action_taken==='status_updated'?'<span style="font-size:9px;background:#dcfce7;color:#16a34a;padding:1px 5px;border-radius:4px">auto-updated</span>':''}
                </div>
                <div style="font-size:11.5px;color:var(--tx2)">${m.message||'(pesan media)'}</div>
            </div>
            <div style="font-size:10px;color:var(--tx3);white-space:nowrap;flex-shrink:0">${new Date(m.received_at).toLocaleString('id-ID',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'})}</div>
        </div>
    `).join('');
}

// ── foto hover popup ──────────────────────────────────────────────────────────
(function(){
    var popup = document.getElementById('ph-popup');
    var phImgs = [], phIdx = 0, phTimer, phOver = false;

    function phRender() {
        if (!phImgs.length) return;
        var nav = phImgs.length > 1
            ? '<div style="display:flex;align-items:center;justify-content:center;gap:8px;padding:4px 0">'
              + '<span id="ph-prev" style="cursor:pointer;font-size:16px;color:var(--tx2);padding:0 6px">‹</span>'
              + '<span style="font-size:10px;color:var(--tx3)">'+(phIdx+1)+' / '+phImgs.length+'</span>'
              + '<span id="ph-next" style="cursor:pointer;font-size:16px;color:var(--tx2);padding:0 6px">›</span>'
              + '</div>' : '';
        var dots = phImgs.length > 1
            ? '<div style="display:flex;gap:4px;justify-content:center;padding:4px 0">'
              + phImgs.map(function(_,i){ return '<span style="width:6px;height:6px;border-radius:50%;background:'+(i===phIdx?'var(--ac)':'#cbd5e1')+'"></span>'; }).join('')
              + '</div>' : '';
        popup.innerHTML = '<img src="'+phImgs[phIdx]+'" style="width:280px;height:200px;object-fit:cover;display:block">' + nav + dots;
        var prev = document.getElementById('ph-prev');
        var next = document.getElementById('ph-next');
        if (prev) prev.addEventListener('click', function(e){ e.stopPropagation(); phStep(-1); });
        if (next) next.addEventListener('click', function(e){ e.stopPropagation(); phStep(1); });
    }

    function phStep(d) { phIdx = (phIdx + d + phImgs.length) % phImgs.length; phRender(); }

    function phPos(e) {
        var pad = 14, w = 290, h = 240;
        var x = e.clientX + pad, y = e.clientY + pad;
        if (x + w > window.innerWidth)  x = e.clientX - w - pad;
        if (y + h > window.innerHeight) y = e.clientY - h - pad;
        popup.style.left = x + 'px';
        popup.style.top  = y + 'px';
    }

    function phScheduleHide() {
        phTimer = setTimeout(function(){ if (!phOver) popup.style.display = 'none'; }, 200);
    }

    document.addEventListener('mouseover', function(e) {
        var el = e.target.closest('.ph-wrap');
        if (!el) return;
        var imgs = (el.dataset.imgs || '').split('|').filter(Boolean);
        if (!imgs.length) return;
        phOver = true; clearTimeout(phTimer);
        phImgs = imgs; phIdx = 0;
        phRender(); phPos(e);
        popup.style.display = 'block';
    });
    document.addEventListener('mousemove', function(e) {
        if (popup.style.display === 'none') return;
        if (e.target.closest('.ph-wrap')) phPos(e);
    });
    document.addEventListener('mouseout', function(e) {
        if (!e.target.closest('.ph-wrap')) return;
        phOver = false; phScheduleHide();
    });
    popup.addEventListener('mouseenter', function(){ phOver = true; clearTimeout(phTimer); });
    popup.addEventListener('mouseleave', function(){ phOver = false; phScheduleHide(); });
    document.addEventListener('wheel', function(e) {
        if (popup.style.display === 'none' || phImgs.length < 2) return;
        if (!e.target.closest('.ph-wrap') && e.target !== popup && !popup.contains(e.target)) return;
        e.preventDefault(); phStep(e.deltaY > 0 ? 1 : -1);
    }, {passive: false});
})();

// ── init ──────────────────────────────────────────────────────────────────────
loadRecheckCount();
checkWebhookStatus();
</script>
@endpush
