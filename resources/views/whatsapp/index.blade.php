@extends('layouts.app')
@section('title', 'WhatsApp — Mafaza Fortuna')
@section('page-title', 'WhatsApp Outreach')

@push('styles')
<style>
.wa-stat { background:var(--sur); border:1px solid var(--bdr); border-radius:8px; padding:14px 18px; }
.wa-stat .lbl { font-size:11px; color:var(--tx2); font-weight:600; text-transform:uppercase; letter-spacing:.05em; margin-bottom:2px; }
.wa-stat .val { font-size:22px; font-weight:700; color:var(--tx); line-height:1; }
.device-card { background:var(--sur); border:1px solid var(--bdr); border-radius:8px; padding:14px 16px; display:flex; align-items:center; gap:12px; }
.device-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.dot-ready { background:#16a34a; box-shadow:0 0 0 3px #dcfce7; }
.dot-off   { background:#9ca3af; }
.device-info { flex:1; min-width:0; }
.device-name { font-weight:600; font-size:14px; color:var(--tx); }
.device-num  { font-size:12px; color:var(--tx2); }
.device-badge { font-size:11px; padding:2px 8px; border-radius:12px; font-weight:600; }
.badge-ready { background:#dcfce7; color:#16a34a; }
.badge-off   { background:#f3f4f6; color:#6b7280; }
.template-card { border:2px solid var(--bdr); border-radius:8px; padding:12px 14px; cursor:pointer; transition:.15s; }
.template-card:hover,.template-card.active { border-color:var(--ac); background:#eff6ff; }
.template-card.active .template-name { color:var(--ac); }
.template-body { font-size:12px; color:var(--tx2); white-space:pre-wrap; margin-top:6px; line-height:1.5; }
.log-box { background:#0d1117; border-radius:6px; padding:12px 14px; font-family:monospace; font-size:12px; color:#c9d1d9; min-height:80px; max-height:220px; overflow-y:auto; white-space:pre-wrap; }
.progress-bar-wrap { background:#e5e7eb; border-radius:4px; height:6px; overflow:hidden; margin:8px 0; }
.progress-bar-fill { background:var(--gn); height:100%; transition:width .4s; border-radius:4px; }
</style>
@endpush

@section('content')

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:10px">
    <div class="wa-stat">
        <div class="lbl">Ada WA</div>
        <div class="val" style="color:var(--gn)" id="stat-has-wa">{{ $stats['has_wa'] }}</div>
    </div>
    <div class="wa-stat">
        <div class="lbl">Tidak Ada WA</div>
        <div class="val" style="color:var(--rd)" id="stat-no-wa">{{ $stats['no_wa'] }}</div>
    </div>
    <div class="wa-stat">
        <div class="lbl">Belum Dicek</div>
        <div class="val" style="color:var(--or)" id="stat-unchecked">{{ $stats['unchecked'] }}</div>
    </div>
    <div class="wa-stat">
        <div class="lbl">Terkirim</div>
        <div class="val" style="color:var(--ac)" id="stat-sent">{{ $stats['outreach_sent'] }}</div>
    </div>
</div>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px">
    <div class="wa-stat">
        <div class="lbl"><i class="fas fa-reply" style="color:var(--ac)"></i> Ada Respon</div>
        <div class="val" style="color:var(--ac)" id="stat-replied">{{ $stats['replied'] }}</div>
    </div>
    <div class="wa-stat">
        <div class="lbl"><i class="fas fa-thumbs-up" style="color:var(--or)"></i> Berminat</div>
        <div class="val" style="color:var(--or)" id="stat-interested">{{ $stats['interested'] }}</div>
    </div>
    <div class="wa-stat">
        <div class="lbl"><i class="fas fa-thumbs-down" style="color:var(--rd)"></i> Tidak Berminat</div>
        <div class="val" style="color:var(--rd)" id="stat-not-interested">{{ $stats['not_interested'] }}</div>
    </div>
    <div class="wa-stat">
        <div class="lbl"><i class="fas fa-shopping-cart" style="color:var(--gn)"></i> Sudah Order</div>
        <div class="val" style="color:var(--gn)" id="stat-ordered">{{ $stats['ordered'] }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:16px;align-items:start">

{{-- Kolom kiri: Devices --}}
<div style="display:flex;flex-direction:column;gap:12px">
    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <span><i class="fas fa-mobile-alt" style="color:var(--ac);margin-right:6px"></i>Device WA</span>
            <button class="btn btn-xs btn-secondary" onclick="refreshDevices()"><i class="fas fa-sync-alt"></i></button>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:8px" id="device-list">
            @forelse($devices as $d)
            <div class="device-card {{ request('device') === $d['id'] ? 'active' : '' }}"
                 onclick="selectDevice('{{ $d['id'] }}','{{ $d['name'] }}')"
                 id="dev-{{ $d['id'] }}"
                 style="cursor:pointer;{{ ($d['status'] ?? '') !== 'ready' ? 'opacity:.5' : '' }}">
                <div class="device-dot {{ ($d['status'] ?? '') === 'ready' ? 'dot-ready' : 'dot-off' }}"></div>
                <div class="device-info">
                    <div class="device-name">{{ $d['name'] }}</div>
                    <div class="device-num">{{ $d['number'] ?? '—' }}</div>
                </div>
                <span class="device-badge {{ ($d['status'] ?? '') === 'ready' ? 'badge-ready' : 'badge-off' }}">
                    {{ ($d['status'] ?? '') === 'ready' ? 'Online' : 'Offline' }}
                </span>
            </div>
            @empty
            <p class="text-muted text-sm">Tidak ada device. Pastikan wa-api berjalan.</p>
            @endforelse
        </div>
    </div>

    {{-- Device terpilih --}}
    <div class="card">
        <div class="card-body">
            <div class="text-xs text-muted mb-4">Device aktif untuk outreach</div>
            <div id="selected-device-name" style="font-weight:600;font-size:14px;color:var(--ac)">— pilih device di atas —</div>
            <input type="hidden" id="selected-device-id" value="">
        </div>
    </div>
</div>

{{-- Kolom kanan: Tabs --}}
<div>
    {{-- Tab nav --}}
    <div style="display:flex;gap:0;border-bottom:2px solid var(--bdr);margin-bottom:16px;flex-wrap:wrap">
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
            <span style="background:var(--rd);color:#fff;border-radius:10px;font-size:10px;padding:1px 5px;margin-left:3px">{{ $stats['replied'] + $stats['interested'] }}</span>
            @endif
        </button>
    </div>

    {{-- Tab: Cek WA --}}
    <div id="tab-cek-wa" class="tab-panel">
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-search" style="color:var(--ac);margin-right:6px"></i>Cek Nomor WhatsApp</span>
            </div>
            <div class="card-body">
                <p class="text-sm text-muted mb-12">
                    Cek mana nomor di database yang terdaftar di WhatsApp.
                    Sisa belum dicek: <strong id="unchecked-count">{{ $stats['unchecked'] }}</strong> nomor.
                </p>
                <div class="d-flex align-center gap-8 mb-12">
                    <label class="text-xs text-muted">Per batch:</label>
                    <select id="check-limit" class="form-control" style="width:80px;font-size:13px">
                        <option value="10">10</option>
                        <option value="30" selected>30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <button id="btn-check-wa" class="btn btn-sm" style="background:var(--gn);color:#fff;border-color:var(--gn)"
                            onclick="runCheckWA()">
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

                <div style="border-top:1px solid var(--bdr);margin-top:16px;padding-top:16px">
                    <div class="text-sm fw-600 mb-8">Re-Cek Nomor Lama</div>
                    <p class="text-xs text-muted mb-8">
                        Nomor yang sebelumnya dicek "tidak punya WA" mungkin sudah daftar.
                        Sisa untuk re-cek: <strong id="recheck-count">...</strong> nomor.
                    </p>
                    <button class="btn btn-sm btn-secondary" onclick="runReCheckWA()">
                        <i class="fas fa-redo"></i> Re-Cek Sekarang
                    </button>
                    <div class="log-box" id="recheck-log" style="display:none;margin-top:8px"></div>
                </div>

                <div style="border-top:1px solid var(--bdr);margin-top:16px;padding-top:16px">
                    <div class="text-sm fw-600 mb-6"><i class="fas fa-plug" style="color:var(--ac)"></i> Webhook Pesan Masuk</div>
                    <p class="text-xs text-muted mb-10">
                        Saat aktif, setiap pesan WA yang masuk dari prospek akan otomatis mengupdate status dan mengirim notifikasi Telegram.
                    </p>
                    <div id="webhook-status-wrap" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                        <span id="webhook-badge" style="font-size:12px;padding:3px 10px;border-radius:10px;font-weight:600">Mengecek...</span>
                        <button id="btn-webhook-reg" onclick="registerWebhook()" class="btn btn-sm" style="background:var(--gn);color:#fff;border-color:var(--gn);display:none">
                            <i class="fas fa-link"></i> Daftarkan Webhook
                        </button>
                        <button id="btn-webhook-unreg" onclick="unregisterWebhook()" class="btn btn-sm btn-danger" style="display:none">
                            <i class="fas fa-unlink"></i> Cabut
                        </button>
                        <code id="webhook-url" style="font-size:10.5px;color:var(--tx3);background:var(--bg);padding:2px 6px;border-radius:4px"></code>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pesan Masuk dari Prospek --}}
        <div class="card" style="margin-top:14px">
            <div class="card-header">
                <span><i class="fas fa-envelope-open" style="color:var(--or)"></i> Pesan Masuk dari Prospek</span>
                <button class="btn btn-sm btn-secondary" onclick="loadIncoming()"><i class="fas fa-sync"></i> Refresh</button>
            </div>
            <div id="incoming-list">
                <div style="padding:28px;text-align:center;color:var(--tx3);font-size:13px">Klik Refresh untuk memuat pesan masuk.</div>
            </div>
        </div>
    </div>

    {{-- Tab: Outreach --}}
    <div id="tab-outreach" class="tab-panel" style="display:none">
        <div class="card mb-16">
            <div class="card-header" style="justify-content:space-between">
                <span><i class="fas fa-comment-alt" style="color:var(--ac);margin-right:6px"></i>Template Pesan</span>
                <button class="btn btn-primary btn-xs" onclick="openAddTemplate()">
                    <i class="fas fa-plus"></i> Tambah
                </button>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px" id="template-list">
                {{-- Opsi Acak --}}
                <div class="template-card active" onclick="selectTemplate(0)" id="tpl-0" style="border-color:var(--ac);background:#eff6ff">
                    <div style="display:flex;align-items:center;gap:6px">
                        <i class="fas fa-random" style="color:var(--ac)"></i>
                        <span style="font-weight:700;font-size:13px;color:var(--ac)">Acak (bergantian)</span>
                    </div>
                    <div class="template-body" style="color:var(--tx3)">Tiap penerima mendapat template berbeda secara acak — lebih aman dari deteksi spam.</div>
                </div>

                @foreach($templates as $tpl)
                <div class="template-card {{ !$tpl->is_active ? 'opacity-50' : '' }}"
                     onclick="selectTemplate({{ $tpl->id }})"
                     id="tpl-{{ $tpl->id }}"
                     style="{{ !$tpl->is_active ? 'opacity:.45;pointer-events:none' : '' }}">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                        <div class="template-name" style="font-weight:600;font-size:13px">{{ $tpl->name }}</div>
                        <div style="display:flex;gap:4px;flex-shrink:0" onclick="event.stopPropagation()">
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
            <div id="template-stats-wrap" style="margin-top:12px;display:none">
                <div style="font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:6px">
                    <i class="fas fa-chart-bar" style="color:var(--ac)"></i> Statistik Template
                </div>
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead><tr style="background:var(--bg2);border-bottom:1px solid var(--bdr)">
                        <th style="padding:6px 10px;text-align:left;font-weight:600;color:var(--tx2)">Template</th>
                        <th style="padding:6px 10px;text-align:center;font-weight:600;color:var(--tx2)">Terkirim</th>
                        <th style="padding:6px 10px;text-align:center;font-weight:600;color:var(--tx2)">Respon</th>
                        <th style="padding:6px 10px;text-align:center;font-weight:600;color:var(--tx2)">Berminat</th>
                        <th style="padding:6px 10px;text-align:center;font-weight:600;color:var(--tx2)">Order</th>
                        <th style="padding:6px 10px;text-align:center;font-weight:600;color:var(--tx2)">Konversi%</th>
                    </tr></thead>
                    <tbody id="template-stats-body"></tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- Modal tambah/edit template --}}
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

        <div class="card">
            <div class="card-header" style="justify-content:space-between;flex-wrap:wrap;gap:8px">
                <span><i class="fas fa-paper-plane" style="color:var(--ac);margin-right:6px"></i>Kirim Outreach</span>
                {{-- Limit harian --}}
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="text-xs text-muted">Limit hari ini:</span>
                    <div style="display:flex;align-items:center;gap:6px">
                        <div style="width:100px;height:6px;background:var(--bdr);border-radius:3px;overflow:hidden">
                            <div id="daily-bar" style="height:100%;border-radius:3px;background:var(--ac);transition:width .3s;width:{{ min(100, round($stats['sent_today']/$stats['daily_limit']*100)) }}%"></div>
                        </div>
                        <span class="text-xs"><span id="sent-today">{{ $stats['sent_today'] }}</span>/<span id="daily-limit">{{ $stats['daily_limit'] }}</span></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {{-- Sisa target --}}
                <div style="display:flex;gap:16px;margin-bottom:12px;flex-wrap:wrap">
                    <div class="text-sm text-muted">
                        Kategori relevan belum kirim: <strong id="remaining-relevant">{{ $stats['remaining_relevant'] }}</strong>
                    </div>
                    <div class="text-sm text-muted">
                        Semua punya WA belum kirim: <strong id="remaining-count">{{ $stats['remaining'] }}</strong>
                    </div>
                </div>

                {{-- Filter kategori --}}
                <div style="margin-bottom:12px">
                    <label class="text-xs text-muted fw-600" style="display:block;margin-bottom:4px">Target Kategori:</label>
                    <div style="display:flex;gap:6px;flex-wrap:wrap" id="cat-filter-btns">
                        <button class="btn btn-sm btn-primary cat-filter-btn" data-cat="relevant" onclick="setCatFilter(this)">
                            <i class="fas fa-star"></i> Kategori Relevan ({{ $stats['remaining_relevant'] }})
                        </button>
                        <button class="btn btn-sm btn-ghost cat-filter-btn" data-cat="" onclick="setCatFilter(this)">
                            Semua ({{ $stats['remaining'] }})
                        </button>
                    </div>
                </div>
                <input type="hidden" id="category-filter" value="relevant">

                {{-- Kirim --}}
                <div class="d-flex align-center gap-8 mb-12 flex-wrap">
                    <label class="text-xs text-muted">Kirim:</label>
                    <select id="send-limit" class="form-control" style="width:80px;font-size:13px">
                        <option value="3">3</option>
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <label class="text-xs text-muted">pesan</label>
                    <button id="btn-send" class="btn btn-sm" style="background:var(--ac);color:#fff;border-color:var(--ac)"
                            onclick="openSendPreview()">
                        <i class="fas fa-eye"></i> Preview & Kirim
                    </button>
                    <span id="daily-warn" style="font-size:11px;color:var(--rd);display:none">
                        <i class="fas fa-exclamation-triangle"></i> Limit harian hampir tercapai!
                    </span>
                </div>
                <div class="progress-bar-wrap" id="send-progress-wrap" style="display:none">
                    <div class="progress-bar-fill" id="send-progress-bar" style="width:0%"></div>
                </div>
                <div class="log-box" id="send-log" style="display:none"></div>
                <p class="text-xs text-muted mt-8">
                    <i class="fas fa-info-circle"></i> Delay random 3–7 detik · prioritas: kategori relevan + ramai + banyak ulasan · skip duplikat nomor.
                </p>
            </div>
        </div>
    </div>

    {{-- Tab: Daftar Target --}}
    <div id="tab-list" class="tab-panel" style="display:none">
        <div class="card">
            <div class="card-header" style="justify-content:space-between;flex-wrap:wrap;gap:8px">
                <span><i class="fas fa-list" style="color:var(--ac);margin-right:6px"></i>Target Outreach</span>
                <div class="d-flex gap-8 flex-wrap">
                    <button class="btn btn-sm btn-secondary" onclick="checkDuplicates()" title="Cek duplikat nomor telepon">
                        <i class="fas fa-copy"></i> Cek Duplikat
                    </button>
                    <select id="list-category" class="form-control" style="width:auto;font-size:12px"
                            onchange="loadTargetList()">
                        <option value="relevant">Kategori Relevan</option>
                        <option value="">Semua Kategori</option>
                    </select>
                    <select id="list-filter" class="form-control" style="width:auto;font-size:12px"
                            onchange="loadTargetList()">
                        <option value="pending">Belum dikirim — prioritas tertinggi</option>
                        <option value="sent">Sudah dikirim</option>
                        <option value="responded">Sudah respon</option>
                        <option value="interested">Berminat</option>
                        <option value="ordered">Sudah order</option>
                    </select>
                </div>
            </div>
            {{-- Bulk action bar --}}
            <div id="bulk-action-bar" style="display:none;padding:8px 12px;background:var(--acl);border-bottom:1px solid var(--bdr);align-items:center;gap:8px;flex-wrap:wrap">
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
                <button class="btn btn-sm btn-ghost" onclick="clearBulkSelection()">Batalkan Pilihan</button>
            </div>
            <div class="card-body p-0">
                <div id="target-list-wrap">
                    <p class="text-sm text-muted" style="padding:16px">Memuat...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab: Follow Up --}}
    <div id="tab-followup" class="tab-panel" style="display:none">
        <div id="followup-wrap">
            <p class="text-sm text-muted" style="padding:16px"><i class="fas fa-spinner fa-spin"></i> Memuat data follow up...</p>
        </div>
    </div>
</div>

{{-- Modal Duplikat --}}
<div id="dup-modal" style="display:none;position:fixed;inset:0;z-index:400;background:rgba(0,0,0,.5);align-items:flex-start;justify-content:center;padding:20px 12px;overflow-y:auto">
    <div class="card" style="width:min(640px,98vw);margin:auto">
        <div class="card-header" style="justify-content:space-between">
            <span><i class="fas fa-copy" style="color:var(--ac);margin-right:6px"></i>Nomor Telepon Duplikat</span>
            <button class="btn btn-ghost btn-xs" onclick="document.getElementById('dup-modal').style.display='none'"><i class="fas fa-times"></i></button>
        </div>
        <div class="card-body p-0">
            <div id="dup-modal-content" style="padding:16px">
                <p class="text-sm text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>
            </div>
        </div>
    </div>
</div>

</div>
{{-- Modal Preview Kirim --}}
<div id="preview-modal" style="display:none;position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.5);align-items:flex-start;justify-content:center;padding:20px 12px;overflow-y:auto">
    <div class="card" style="width:min(620px,98vw);margin:auto">
        <div class="card-header" style="justify-content:space-between">
            <span><i class="fas fa-eye" style="color:var(--ac);margin-right:6px"></i>Preview Target Outreach</span>
            <button class="btn btn-ghost btn-xs" onclick="closePreviewModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
            {{-- Warning jam --}}
            <div id="preview-time-warn" style="display:none;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;padding:8px 12px;font-size:12px;color:#92400e">
                <i class="fas fa-clock"></i> <strong>Perhatian:</strong> Sekarang di luar jam kerja (07:00–21:00). Pesan mungkin tidak langsung dibaca.
            </div>
            {{-- Preview pesan --}}
            <div>
                <div style="font-size:11px;color:var(--tx2);font-weight:600;margin-bottom:4px">Preview pesan (contoh untuk target pertama):</div>
                <div id="preview-message-box" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;font-size:12px;white-space:pre-wrap;line-height:1.6;max-height:140px;overflow-y:auto;color:#064e3b"></div>
            </div>
            {{-- Daftar target --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                    <div style="font-size:11px;color:var(--tx2);font-weight:600">
                        <span id="preview-count">0</span> target akan dikirim
                        <span style="color:var(--tx3);font-weight:400"> — centang/hapus yang tidak sesuai</span>
                    </div>
                    <div style="display:flex;gap:4px">
                        <button class="btn btn-xs btn-secondary" onclick="checkAll(true)">Pilih Semua</button>
                        <button class="btn btn-xs btn-ghost" onclick="checkAll(false)">Batalkan Semua</button>
                    </div>
                </div>
                <div id="preview-list" style="border:1px solid var(--bdr);border-radius:6px;overflow:hidden;max-height:260px;overflow-y:auto"></div>
            </div>
            {{-- Tombol aksi --}}
            <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:4px">
                <button class="btn btn-secondary btn-sm" onclick="closePreviewModal()">Batal</button>
                <button id="btn-confirm-send" class="btn btn-sm" style="background:var(--ac);color:#fff;border-color:var(--ac)" onclick="confirmSend()">
                    <i class="fas fa-paper-plane"></i> Konfirmasi Kirim (<span id="preview-selected-count">0</span>)
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.tab-btn { padding:8px 16px; border:none; background:none; cursor:pointer; font-size:13px; font-weight:500;
           color:var(--tx2); border-bottom:2px solid transparent; margin-bottom:-2px; transition:.15s; }
.tab-btn.active { color:var(--ac); border-bottom-color:var(--ac); }
.tab-btn:hover { color:var(--tx); }
@media(max-width:768px){
    [style*="grid-template-columns:300px"]{grid-template-columns:1fr!important}
    [style*="repeat(4,1fr)"]{grid-template-columns:repeat(2,1fr)!important}
}
</style>
@endpush

@push('scripts')
<script>
var selectedDeviceId   = '';
var selectedTemplateId = 0;
var checkAllRunning    = false;

// ── device ───────────────────────────────────────────────────────────────────
function selectDevice(id, name) {
    selectedDeviceId = id;
    document.getElementById('selected-device-id').value = id;
    document.getElementById('selected-device-name').textContent = name;
    document.querySelectorAll('.device-card').forEach(el => el.style.outline = 'none');
    const el = document.getElementById('dev-' + id);
    if (el) el.style.outline = '2px solid var(--ac)';
}

function refreshDevices() {
    fetch('{{ route("whatsapp.devices") }}')
        .then(r => r.json())
        .then(d => {
            const list = document.getElementById('device-list');
            list.innerHTML = d.devices.map(dev => `
                <div class="device-card" onclick="selectDevice('${dev.id}','${dev.name}')"
                     id="dev-${dev.id}" style="cursor:pointer;${dev.status !== 'ready' ? 'opacity:.5' : ''}">
                    <div class="device-dot ${dev.status === 'ready' ? 'dot-ready' : 'dot-off'}"></div>
                    <div class="device-info">
                        <div class="device-name">${dev.name}</div>
                        <div class="device-num">${dev.number || '—'}</div>
                    </div>
                    <span class="device-badge ${dev.status === 'ready' ? 'badge-ready' : 'badge-off'}">
                        ${dev.status === 'ready' ? 'Online' : 'Offline'}
                    </span>
                </div>
            `).join('');
        });
}

// ── tab ──────────────────────────────────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.tab-panel').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + name).style.display = 'block';
    event.target.closest('.tab-btn').classList.add('active');
    if (name === 'list') loadTargetList();
    if (name === 'followup') loadFollowup();
    if (name === 'outreach') loadTemplateStats();
}

// ── template ─────────────────────────────────────────────────────────────────
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

function closeTplModal() {
    document.getElementById('tpl-modal').style.display = 'none';
}

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

// ── cek WA ───────────────────────────────────────────────────────────────────
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
            logCheck(`✓ Batch selesai: ${d.results.has_wa} punya WA, ${d.results.no_wa} tidak, ${d.results.error} error`);
            logCheck(`  Sisa belum dicek: ${d.remaining}`);
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
    document.getElementById('daily-bar').style.width = pct + '%';
    document.getElementById('daily-bar').style.background = pct >= 90 ? '#ef4444' : pct >= 70 ? '#f97316' : 'var(--ac)';
    document.getElementById('sent-today').textContent = sentToday;
    const warn = document.getElementById('daily-warn');
    warn.style.display = pct >= 80 ? 'inline' : 'none';
}

// ── Preview Modal ──────────────────────────────────────────────────────────────
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

    // Peringatan jam
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

        const listEl = document.getElementById('preview-list');
        listEl.innerHTML = previewData.map((p, i) => `
            <label style="display:flex;align-items:center;gap:10px;padding:8px 12px;cursor:pointer;
                          ${i < previewData.length-1 ? 'border-bottom:1px solid var(--bdr)' : ''};
                          transition:.1s" onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background=''">
                <input type="checkbox" id="pchk-${p.id}" checked onchange="updateSelectedCount()" style="width:14px;height:14px;flex-shrink:0;cursor:pointer">
                <div style="flex:1;min-width:0">
                    <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(p.name)}</div>
                    <div style="font-size:10px;color:var(--tx3)">${escHtml(p.category || '—')} · ${p.phone}</div>
                </div>
                <div style="font-size:10px;color:var(--tx3);text-align:right;flex-shrink:0;white-space:nowrap">
                    ${p.rating ? '★' + p.rating : ''}${p.review_count ? ' · ' + p.review_count + ' ulasan' : ''}
                </div>
            </label>`).join('');

        updateSelectedCount();
        document.getElementById('preview-modal').style.display = 'flex';
    } catch(e) {
        alert('Gagal memuat preview: ' + e.message);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-eye"></i> Preview & Kirim';
}

function closePreviewModal() {
    document.getElementById('preview-modal').style.display = 'none';
}

function checkAll(checked) {
    previewData.forEach(p => {
        const el = document.getElementById('pchk-' + p.id);
        if (el) el.checked = checked;
    });
    updateSelectedCount();
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
        logSend(`Mengirim ke ${placeIds.length} target... (delay 3–7 detik per pesan)`);
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
            logSend(`✓ Terkirim: ${d.results.sent} | Gagal: ${d.results.failed}`);
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
function loadTargetList() {
    const filter   = document.getElementById('list-filter').value;
    const category = document.getElementById('list-category').value;
    const wrap     = document.getElementById('target-list-wrap');
    wrap.innerHTML = '<p class="text-sm text-muted" style="padding:16px"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>';

    fetch(`{{ route('whatsapp.target-list') }}?filter=${filter}&category=${encodeURIComponent(category)}`)
        .then(r => r.json())
        .then(d => {
            if (!d.data || d.data.length === 0) {
                wrap.innerHTML = '<p class="text-sm text-muted" style="padding:16px">Tidak ada data untuk filter ini.</p>';
                return;
            }
            const statusBadge = {
                sent:           '<span style="color:#3b82f6;font-weight:600;font-size:11px">Terkirim</span>',
                replied:        '<span style="color:#06b6d4;font-weight:600;font-size:11px">Respon</span>',
                responded:      '<span style="color:#06b6d4;font-weight:600;font-size:11px">Respon</span>',
                interested:     '<span style="color:#f97316;font-weight:600;font-size:11px">Berminat</span>',
                not_interested: '<span style="color:#9ca3af;font-weight:600;font-size:11px">Tidak Berminat</span>',
                ordered:        '<span style="color:#10b981;font-weight:600;font-size:11px">Order ✓</span>',
            };
            const scoreColor = s => s >= 50 ? '#10b981' : s >= 30 ? '#f97316' : '#9ca3af';
            const rows = d.data.map(p => {
                const score = p.priority_score || 0;
                const waLink = p.phone ? `<a href="https://wa.me/${p.phone.replace(/\D/g,'')}" target="_blank" class="btn btn-xs" style="background:#22c55e;color:#fff;border-color:#22c55e" title="Buka WA"><i class="fab fa-whatsapp"></i></a>` : '';
                const detailLink = `<a href="/mafaza/public/places/${p.id}" target="_blank" class="btn btn-xs btn-ghost" title="Detail"><i class="fas fa-eye"></i></a>`;
                const actionBtn = p.outreach_status === 'sent'
                    ? `<button class="btn btn-xs btn-secondary" onclick="markStatus(${p.id},'replied',this)">↩ Respon</button>`
                    : p.outreach_status === 'replied' || p.outreach_status === 'responded'
                    ? `<button class="btn btn-xs" style="background:#f97316;color:#fff;border-color:#f97316" onclick="markStatus(${p.id},'interested',this)">👍</button>`
                    : '';
                return `<tr>
                    <td style="padding:7px 12px">
                        <div class="fw-600" style="font-size:12px">${escHtml(p.name)}</div>
                        <div style="font-size:10px;color:var(--tx3)">${escHtml(p.category || '—')}</div>
                    </td>
                    <td style="padding:7px 12px;font-size:11px;color:var(--tx2)">${escHtml(p.phone || '—')}</td>
                    <td style="padding:7px 12px;text-align:center">
                        <span style="font-size:11px;font-weight:700;color:${scoreColor(score)}">${score}</span>
                    </td>
                    <td style="padding:7px 12px">${statusBadge[p.outreach_status] || '<span style="color:var(--tx3);font-size:11px">Belum</span>'}</td>
                    <td style="padding:7px 12px;font-size:10px;color:var(--tx3)">${p.outreach_sent_at ? p.outreach_sent_at.replace('T',' ').slice(0,10) : '—'}</td>
                    <td style="padding:7px 12px"><div style="display:flex;gap:4px">${waLink}${detailLink}${actionBtn}</div></td>
                </tr>`;
            }).join('');
            wrap.innerHTML = `
                <div style="font-size:12px;color:var(--tx2);padding:6px 12px;border-bottom:1px solid var(--bdr)">${d.count} tempat</div>
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead><tr style="border-bottom:1px solid var(--bdr);background:var(--bg2)">
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Nama / Kategori</th>
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Telepon</th>
                        <th style="padding:7px 12px;text-align:center;font-size:11px;font-weight:600">Score</th>
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Status</th>
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Kirim</th>
                        <th style="padding:7px 12px"></th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>
                </div>`;
        })
        .catch(() => { wrap.innerHTML = '<p style="padding:16px;color:var(--rd)">Gagal memuat.</p>'; });
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function markStatus(id, status, btn) {
    btn.disabled = true;
    fetch(`{{ url('/whatsapp/mark-status') }}/${id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status })
    }).then(r => r.json()).then(d => {
        if (d.status === 'ok') loadTargetList();
    });
}

// ── stats refresh ─────────────────────────────────────────────────────────────
function refreshStats() {
    fetch('{{ route("whatsapp.stats") }}')
        .then(r => r.json())
        .then(d => {
            document.getElementById('stat-has-wa').textContent    = d.has_wa;
            document.getElementById('stat-no-wa').textContent     = d.no_wa;
            document.getElementById('stat-unchecked').textContent = d.unchecked;
            document.getElementById('stat-sent').textContent      = d.outreach_sent;
            document.getElementById('stat-replied').textContent = d.replied;
            document.getElementById('unchecked-count').textContent = d.unchecked;
            document.getElementById('remaining-count').textContent = d.remaining;
        });
}

// Auto-refresh stats setiap 30 detik
setInterval(refreshStats, 30000);

// ── target list (bulk selection) ──────────────────────────────────────────────
let selectedIds = new Set();

function updateBulkBar() {
    const bar = document.getElementById('bulk-action-bar');
    const lbl = document.getElementById('bulk-count-label');
    if (selectedIds.size > 0) {
        bar.style.display = 'flex';
        bar.style.flexWrap = 'wrap';
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

// Override loadTargetList to add checkboxes
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
                sent:           '<span style="color:#3b82f6;font-weight:600;font-size:11px">Terkirim</span>',
                replied:        '<span style="color:#06b6d4;font-weight:600;font-size:11px">Respon</span>',
                responded:      '<span style="color:#06b6d4;font-weight:600;font-size:11px">Respon</span>',
                interested:     '<span style="color:#f97316;font-weight:600;font-size:11px">Berminat</span>',
                not_interested: '<span style="color:#9ca3af;font-weight:600;font-size:11px">Tidak Berminat</span>',
                ordered:        '<span style="color:#10b981;font-weight:600;font-size:11px">Order ✓</span>',
            };
            const scoreColor = s => s >= 50 ? '#10b981' : s >= 30 ? '#f97316' : '#9ca3af';
            const rows = d.data.map(p => {
                const score = p.priority_score || 0;
                const waLink = p.phone ? `<a href="https://wa.me/${p.phone.replace(/\D/g,'')}" target="_blank" class="btn btn-xs" style="background:#22c55e;color:#fff;border-color:#22c55e" title="Buka WA"><i class="fab fa-whatsapp"></i></a>` : '';
                const detailLink = `<a href="/mafaza/public/places/${p.id}" target="_blank" class="btn btn-xs btn-ghost" title="Detail"><i class="fas fa-eye"></i></a>`;
                const actionBtn = p.outreach_status === 'sent'
                    ? `<button class="btn btn-xs btn-secondary" onclick="markStatus(${p.id},'replied',this)">↩ Respon</button>`
                    : p.outreach_status === 'replied' || p.outreach_status === 'responded'
                    ? `<button class="btn btn-xs" style="background:#f97316;color:#fff;border-color:#f97316" onclick="markStatus(${p.id},'interested',this)">👍</button>`
                    : '';
                return `<tr>
                    <td style="padding:7px 8px;text-align:center">
                        <input type="checkbox" class="bulk-chk" data-id="${p.id}" onchange="onBulkChkChange(this)"
                               style="width:14px;height:14px;cursor:pointer">
                    </td>
                    <td style="padding:7px 12px">
                        <div class="fw-600" style="font-size:12px">${escHtml(p.name)}</div>
                        <div style="font-size:10px;color:var(--tx3)">${escHtml(p.category || '—')}</div>
                    </td>
                    <td style="padding:7px 12px;font-size:11px;color:var(--tx2)">${escHtml(p.phone || '—')}</td>
                    <td style="padding:7px 12px;text-align:center">
                        <span style="font-size:11px;font-weight:700;color:${scoreColor(score)}">${score}</span>
                    </td>
                    <td style="padding:7px 12px">${statusBadge[p.outreach_status] || '<span style="color:var(--tx3);font-size:11px">Belum</span>'}</td>
                    <td style="padding:7px 12px;font-size:10px;color:var(--tx3)">${p.outreach_sent_at ? p.outreach_sent_at.replace('T',' ').slice(0,10) : '—'}</td>
                    <td style="padding:7px 12px"><div style="display:flex;gap:4px">${waLink}${detailLink}${actionBtn}</div></td>
                </tr>`;
            }).join('');
            wrap.innerHTML = `
                <div style="font-size:12px;color:var(--tx2);padding:6px 12px;border-bottom:1px solid var(--bdr)">${d.count} tempat</div>
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead><tr style="border-bottom:1px solid var(--bdr);background:var(--bg2)">
                        <th style="padding:7px 8px;text-align:center"><input type="checkbox" id="select-all-chk" onchange="selectAllBulk(this)" style="width:14px;height:14px;cursor:pointer"></th>
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Nama / Kategori</th>
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Telepon</th>
                        <th style="padding:7px 12px;text-align:center;font-size:11px;font-weight:600">Score</th>
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Status</th>
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Kirim</th>
                        <th style="padding:7px 12px"></th>
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

// ── FITUR 1: Follow Up ───────────────────────────────────────────────────────
function loadFollowup() {
    const wrap = document.getElementById('followup-wrap');
    wrap.innerHTML = '<p class="text-sm text-muted" style="padding:16px"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>';

    fetch('{{ route("whatsapp.followup-list") }}')
        .then(r => r.json())
        .then(d => {
            const daysBadge = days => {
                if (!days && days !== 0) return '<span style="font-size:10px;color:var(--tx3)">—</span>';
                const col = days > 7 ? 'var(--rd)' : days > 3 ? '#f97316' : 'var(--tx3)';
                return `<span style="background:${col};color:#fff;padding:2px 7px;border-radius:10px;font-size:10px;font-weight:600">${days} hari lalu</span>`;
            };
            const buildTable = (items, emptyMsg) => {
                if (!items || items.length === 0) return `<p class="text-sm text-muted" style="padding:8px 0">${emptyMsg}</p>`;
                const rows = items.map(p => {
                    const waLink = p.phone ? `<a href="https://wa.me/${p.phone.replace(/\D/g,'')}" target="_blank" class="btn btn-xs" style="background:#22c55e;color:#fff;border-color:#22c55e"><i class="fab fa-whatsapp"></i></a>` : '';
                    return `<tr>
                        <td style="padding:7px 12px">
                            <div class="fw-600" style="font-size:12px">${escHtml(p.name)}</div>
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
                    <thead><tr style="background:var(--bg2);border-bottom:1px solid var(--bdr)">
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Nama / Kategori</th>
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Dikirim</th>
                        <th style="padding:7px 12px;text-align:left;font-size:11px;font-weight:600">Aksi</th>
                    </tr></thead><tbody>${rows}</tbody></table></div>`;
            };

            wrap.innerHTML = `
                <div class="card mb-12">
                    <div class="card-header" style="background:linear-gradient(to right,#fee2e2,#fff1f2)">
                        <span><i class="fas fa-exclamation-circle" style="color:var(--rd);margin-right:6px"></i>
                        Perlu Di-Follow Up <span style="background:var(--rd);color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px">${d.followup.length}</span></span>
                        <span class="text-xs text-muted">Dikirim >3 hari lalu, belum ada respon</span>
                    </div>
                    <div class="card-body p-0">${buildTable(d.followup, 'Tidak ada yang perlu di-follow up.')}</div>
                </div>
                <div class="card mb-12">
                    <div class="card-header" style="background:linear-gradient(to right,#fff7ed,#fff)">
                        <span><i class="fas fa-thumbs-up" style="color:#f97316;margin-right:6px"></i>
                        Berminat – Belum Order <span style="background:#f97316;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px">${d.interested.length}</span></span>
                    </div>
                    <div class="card-body p-0">${buildTable(d.interested, 'Tidak ada yang berminat saat ini.')}</div>
                </div>
                <div class="card">
                    <div class="card-header" style="background:linear-gradient(to right,#eff6ff,#fff)">
                        <span><i class="fas fa-reply" style="color:var(--ac);margin-right:6px"></i>
                        Sudah Respon – Belum Berminat <span style="background:var(--ac);color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px">${d.replied.length}</span></span>
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
        if (d.status === 'ok') {
            loadFollowup();
            refreshStats();
        }
    }).catch(() => { btn.disabled = false; });
}

// ── FITUR 2: Template stats ───────────────────────────────────────────────────
function loadTemplateStats() {
    fetch('{{ route("whatsapp.template-stats") }}')
        .then(r => r.json())
        .then(d => {
            if (!d.data || d.data.length === 0) return;
            document.getElementById('template-stats-wrap').style.display = 'block';
            const rows = d.data.map(t => {
                const conv = t.sent > 0 ? (t.ordered / t.sent * 100).toFixed(1) : '0';
                return `<tr style="border-bottom:1px solid var(--bdr)">
                    <td style="padding:6px 10px;font-size:12px;font-weight:500">${escHtml(t.template_name)}</td>
                    <td style="padding:6px 10px;text-align:center;font-size:12px">${t.sent}</td>
                    <td style="padding:6px 10px;text-align:center;font-size:12px;color:#06b6d4">${t.replied}</td>
                    <td style="padding:6px 10px;text-align:center;font-size:12px;color:#f97316">${t.interested}</td>
                    <td style="padding:6px 10px;text-align:center;font-size:12px;color:#10b981">${t.ordered}</td>
                    <td style="padding:6px 10px;text-align:center;font-size:12px;font-weight:600;color:${parseFloat(conv)>=5?'#10b981':'#6b7280'}">${conv}%</td>
                </tr>`;
            }).join('');
            document.getElementById('template-stats-body').innerHTML = rows;
        });
}

// ── FITUR 5: Re-Check WA ─────────────────────────────────────────────────────
function loadRecheckCount() {
    fetch('{{ route("whatsapp.recheck-count") }}')
        .then(r => r.json())
        .then(d => {
            document.getElementById('recheck-count').textContent = d.count;
        });
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
            box.textContent += `✓ Selesai: ${d.results.has_wa} sekarang punya WA, ${d.results.no_wa} masih tidak, ${d.results.error} error\n`;
            box.textContent += `Sisa untuk re-cek: ${d.remaining}\n`;
            document.getElementById('recheck-count').textContent = d.remaining;
            refreshStats();
        } else {
            box.textContent += '✗ Error\n';
        }
    } catch(e) {
        box.textContent += '✗ Error: ' + e.message + '\n';
    }
}

// ── FITUR 6: Duplikat ─────────────────────────────────────────────────────────
function checkDuplicates() {
    document.getElementById('dup-modal').style.display = 'flex';
    document.getElementById('dup-modal-content').innerHTML = '<p class="text-sm text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>';

    fetch('{{ route("whatsapp.duplicates") }}')
        .then(r => r.json())
        .then(d => {
            if (d.count === 0) {
                document.getElementById('dup-modal-content').innerHTML = '<p class="text-sm text-muted">Tidak ditemukan nomor duplikat. Bagus!</p>';
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
                    Ditemukan <strong>${d.count}</strong> nomor telepon duplikat. Hapus entri ganda (pertahankan yang pertama).
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
        btn.closest('[style*="border:1px solid var(--bdr)"]')?.remove() || btn.closest('div[style]')?.remove();
        checkDuplicates(); // reload
    } else {
        btn.disabled = false;
        alert('Gagal menghapus.');
    }
}

// Load recheck count on page load
loadRecheckCount();
// Load template stats on page load when outreach tab

// ── Webhook ──────────────────────────────────────────────────────────────────
async function checkWebhookStatus() {
    try {
        const d = await fetch('{{ route("whatsapp.webhook-status") }}').then(r => r.json());
        const badge  = document.getElementById('webhook-badge');
        const btnReg = document.getElementById('btn-webhook-reg');
        const btnUnreg = document.getElementById('btn-webhook-unreg');
        const urlEl  = document.getElementById('webhook-url');
        if (urlEl) urlEl.textContent = d.webhook_url;
        if (d.registered) {
            badge.textContent = '✅ Webhook Terdaftar';
            badge.style.background = '#dcfce7';
            badge.style.color = '#16a34a';
            if (btnReg) btnReg.style.display = 'none';
            if (btnUnreg) btnUnreg.style.display = 'inline-flex';
        } else {
            badge.textContent = '⚠️ Belum Terdaftar';
            badge.style.background = '#fef3c7';
            badge.style.color = '#92400e';
            if (btnReg) btnReg.style.display = 'inline-flex';
            if (btnUnreg) btnUnreg.style.display = 'none';
        }
    } catch(e) {
        const badge = document.getElementById('webhook-badge');
        if (badge) { badge.textContent = 'WA API tidak terhubung'; badge.style.background='#fee2e2'; badge.style.color='#dc2626'; }
    }
}

async function registerWebhook() {
    const r = await fetch('{{ route("whatsapp.register-webhook") }}', {
        method: 'POST', headers: {'X-CSRF-TOKEN': CSRF_TOKEN}
    }).then(r => r.json());
    if (r.status === 'ok') { showToast('Webhook berhasil didaftarkan!', 'success'); checkWebhookStatus(); }
    else showToast('Gagal mendaftarkan webhook.', 'error');
}

async function unregisterWebhook() {
    if (!confirm('Cabut webhook? Pesan masuk tidak akan diproses otomatis.')) return;
    const r = await fetch('{{ route("whatsapp.unregister-webhook") }}', {
        method: 'POST', headers: {'X-CSRF-TOKEN': CSRF_TOKEN}
    }).then(r => r.json());
    if (r.status === 'ok') { showToast('Webhook dicabut.', 'success'); checkWebhookStatus(); }
    else showToast('Gagal.', 'error');
}

async function loadIncoming() {
    const el = document.getElementById('incoming-list');
    if (!el) return;
    el.innerHTML = '<div style="padding:20px;text-align:center;color:var(--tx3)">Memuat...</div>';
    const d = await fetch('{{ route("whatsapp.incoming-messages") }}').then(r => r.json()).catch(() => ({data:[]}));
    if (!d.data.length) {
        el.innerHTML = '<div style="padding:28px;text-align:center;color:var(--tx3);font-size:13px">Belum ada pesan masuk dari prospek.</div>';
        return;
    }
    const statusLabel = {sent:'Terkirim',replied:'Respon',interested:'Berminat',ordered:'Order',not_interested:'Tdk Minat'};
    const statusColor = {sent:'var(--or)',replied:'#06b6d4',interested:'#16a34a',ordered:'#7c3aed',not_interested:'var(--rd)'};
    el.innerHTML = d.data.map(m => `
        <div style="padding:10px 14px;border-bottom:1px solid var(--bdr);display:flex;gap:10px;align-items:flex-start">
            <div style="width:34px;height:34px;border-radius:50%;background:var(--acl);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;font-weight:700;color:var(--ac)">
                ${(m.place?.name || m.from_number).charAt(0).toUpperCase()}
            </div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px">
                    <span style="font-size:13px;font-weight:600">${m.place?.name || m.from_number}</span>
                    ${m.place?.outreach_status ? `<span style="font-size:10px;font-weight:600;color:${statusColor[m.place.outreach_status]||'var(--tx3)'};">${statusLabel[m.place.outreach_status]||m.place.outreach_status}</span>` : ''}
                    ${m.action_taken==='status_updated'?'<span style="font-size:10px;background:#dcfce7;color:#16a34a;padding:1px 5px;border-radius:4px">auto-updated</span>':''}
                </div>
                <div style="font-size:12px;color:var(--tx2)">${m.message||'(pesan media)'}</div>
            </div>
            <div style="font-size:11px;color:var(--tx3);white-space:nowrap;flex-shrink:0">${new Date(m.received_at).toLocaleString('id-ID',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'})}</div>
        </div>
    `).join('');
}

checkWebhookStatus();
</script>
@endpush
