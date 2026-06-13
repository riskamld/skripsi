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
    <div style="display:flex;gap:0;border-bottom:2px solid var(--bdr);margin-bottom:16px">
        <button class="tab-btn active" onclick="switchTab('cek-wa')">
            <i class="fas fa-search"></i> Cek WA
        </button>
        <button class="tab-btn" onclick="switchTab('outreach')">
            <i class="fas fa-paper-plane"></i> Kirim Pesan
        </button>
        <button class="tab-btn" onclick="switchTab('list')">
            <i class="fas fa-list"></i> Daftar Target
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
            <div class="card-header">
                <span><i class="fas fa-paper-plane" style="color:var(--ac);margin-right:6px"></i>Kirim Outreach</span>
            </div>
            <div class="card-body">
                <p class="text-sm text-muted mb-12">
                    Target tersisa (punya WA, belum dikirim): <strong id="remaining-count">{{ $stats['remaining'] }}</strong>
                </p>
                <div class="d-flex align-center gap-8 mb-12">
                    <label class="text-xs text-muted">Kirim:</label>
                    <select id="send-limit" class="form-control" style="width:80px;font-size:13px">
                        <option value="3">3</option>
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                    </select>
                    <label class="text-xs text-muted">pesan</label>
                    <button id="btn-send" class="btn btn-sm" style="background:var(--ac);color:#fff;border-color:var(--ac)"
                            onclick="runSendOutreach()">
                        <i class="fas fa-paper-plane"></i> Kirim Sekarang
                    </button>
                </div>
                <div class="progress-bar-wrap" id="send-progress-wrap" style="display:none">
                    <div class="progress-bar-fill" id="send-progress-bar" style="width:0%"></div>
                </div>
                <div class="log-box" id="send-log" style="display:none"></div>
                <p class="text-xs text-muted mt-8">
                    <i class="fas fa-info-circle"></i> Delay random 3–7 detik antar pesan otomatis diterapkan.
                </p>
            </div>
        </div>
    </div>

    {{-- Tab: Daftar Target --}}
    <div id="tab-list" class="tab-panel" style="display:none">
        <div class="card">
            <div class="card-header" style="justify-content:space-between">
                <span><i class="fas fa-list" style="color:var(--ac);margin-right:6px"></i>Target Outreach</span>
                <div class="d-flex gap-8">
                    <select id="list-filter" class="form-control" style="width:auto;font-size:12px"
                            onchange="loadTargetList()">
                        <option value="pending">Belum dikirim (ada WA)</option>
                        <option value="sent">Sudah dikirim</option>
                        <option value="responded">Sudah respon</option>
                        <option value="all">Semua punya WA</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="target-list-wrap">
                    <p class="text-sm text-muted" style="padding:16px">Memuat...</p>
                </div>
            </div>
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

async function runSendOutreach() {
    if (!selectedDeviceId) { alert('Pilih device terlebih dahulu'); return; }
    const remaining = parseInt(document.getElementById('remaining-count').textContent) || 0;
    if (remaining === 0) { alert('Tidak ada target tersisa.'); return; }
    const limit = parseInt(document.getElementById('send-limit').value);
    if (!confirm(`Kirim ${limit} pesan menggunakan template yang dipilih?`)) return;

    const btn = document.getElementById('btn-send');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    document.getElementById('send-log').textContent = '';
    document.getElementById('send-progress-wrap').style.display = 'block';
    document.getElementById('send-progress-bar').style.width = '10%';

    try {
        logSend(`Mengirim ${limit} pesan... (delay 3–7 detik per pesan)`);
        const resp = await fetch('{{ route("whatsapp.send-outreach") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                device_id:   selectedDeviceId,
                template_id: selectedTemplateId,
                limit
            })
        });
        const d = await resp.json();
        if (d.status === 'ok') {
            document.getElementById('send-progress-bar').style.width = '100%';
            logSend(`✓ Terkirim: ${d.results.sent} | Gagal: ${d.results.failed}`);
            logSend(`Sisa target: ${d.remaining}`);
            document.getElementById('remaining-count').textContent = d.remaining;
            refreshStats();
        } else {
            logSend('✗ ' + (d.error || 'Gagal'));
        }
    } catch(e) {
        logSend('✗ Error: ' + e.message);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Sekarang';
}

// ── target list ───────────────────────────────────────────────────────────────
function loadTargetList() {
    const filter = document.getElementById('list-filter').value;
    const wrap = document.getElementById('target-list-wrap');
    wrap.innerHTML = '<p class="text-sm text-muted" style="padding:16px"><i class="fas fa-spinner fa-spin"></i> Memuat...</p>';

    fetch(`{{ route('whatsapp.target-list') }}?filter=${filter}`)
        .then(r => r.json())
        .then(d => {
            if (!d.data || d.data.length === 0) {
                wrap.innerHTML = '<p class="text-sm text-muted" style="padding:16px">Tidak ada data untuk filter ini.</p>';
                return;
            }
            const statusLabel = { sent: '<span style="color:var(--ac);font-weight:600">Terkirim</span>',
                                  responded: '<span style="color:var(--gn);font-weight:600">Respon</span>',
                                  null: '—' };
            const rows = d.data.map(p => `
                <tr>
                    <td style="padding:8px 12px;font-weight:500">${escHtml(p.name)}</td>
                    <td style="padding:8px 12px;color:var(--tx2);font-size:12px">${escHtml(p.category || '—')}</td>
                    <td style="padding:8px 12px;font-size:12px"><a href="tel:${escHtml(p.phone)}">${escHtml(p.phone)}</a></td>
                    <td style="padding:8px 12px;font-size:12px">${statusLabel[p.outreach_status] || '—'}</td>
                    <td style="padding:8px 12px;font-size:11px;color:var(--tx2)">${p.outreach_sent_at ? p.outreach_sent_at.replace('T',' ').slice(0,16) : '—'}</td>
                    <td style="padding:8px 12px">
                        ${p.outreach_status === 'sent' ? `<button class="btn btn-xs" style="background:var(--gn);color:#fff;border-color:var(--gn)" onclick="markStatus(${p.id},'responded',this)">✓ Respon</button>` : ''}
                    </td>
                </tr>
            `).join('');
            wrap.innerHTML = `
                <div style="font-size:12px;color:var(--tx2);padding:8px 12px;border-bottom:1px solid var(--bdr)">${d.count} data</div>
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:13px">
                    <thead>
                        <tr style="border-bottom:1px solid var(--bdr);background:var(--bg)">
                            <th style="padding:8px 12px;text-align:left;font-weight:600;font-size:12px">Nama</th>
                            <th style="padding:8px 12px;text-align:left;font-weight:600;font-size:12px">Kategori</th>
                            <th style="padding:8px 12px;text-align:left;font-weight:600;font-size:12px">Telepon</th>
                            <th style="padding:8px 12px;text-align:left;font-weight:600;font-size:12px">Status</th>
                            <th style="padding:8px 12px;text-align:left;font-weight:600;font-size:12px">Dikirim</th>
                            <th style="padding:8px 12px"></th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
                </div>
            `;
        })
        .catch(() => {
            wrap.innerHTML = '<p class="text-sm text-muted" style="padding:16px;color:var(--rd)">Gagal memuat data.</p>';
        });
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
            document.getElementById('stat-responded').textContent = d.responded;
            document.getElementById('unchecked-count').textContent = d.unchecked;
            document.getElementById('remaining-count').textContent = d.remaining;
        });
}

// Auto-refresh stats setiap 30 detik
setInterval(refreshStats, 30000);
</script>
@endpush
