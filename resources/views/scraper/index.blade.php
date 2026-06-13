@extends('layouts.app')
@section('title', 'Scraping — Mafaza Fortuna')
@section('page-title', 'Scraping Google Maps')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
<style>
.stats-bar{display:flex;gap:1px;background:#dee2e6;border-radius:8px;overflow:hidden;margin-bottom:20px}
.stat-item{flex:1;background:#fff;padding:14px 18px}
.stat-item .label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#6c757d;font-weight:600;margin-bottom:2px}
.stat-item .value{font-size:24px;font-weight:700;color:#212529;line-height:1}
.stat-item .value span{font-size:13px;font-weight:400;color:#6c757d;margin-left:4px}

.preset-chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px}
.chip{display:inline-block;padding:4px 12px;border-radius:20px;border:1px solid #ced4da;
      font-size:12px;color:#495057;background:#f8f9fa;cursor:pointer;transition:.15s;user-select:none}
.chip:hover{background:var(--ac);border-color:var(--ac);color:#fff}
.chip.active{background:var(--ac);border-color:var(--ac);color:#fff}

/* Map area picker */
#area-map{height:320px;border-radius:8px;border:2px solid var(--bdr);cursor:crosshair;z-index:1}
#area-map.has-pin{border-color:var(--ac)}
.map-legend{display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;font-size:11px;color:var(--tx2)}
.map-legend span{display:flex;align-items:center;gap:4px}
.map-legend i{width:10px;height:10px;border-radius:50%;display:inline-block;flex-shrink:0}
.leaflet-locate-btn{background:#fff;border:2px solid rgba(0,0,0,.2);border-radius:4px;padding:4px 6px;cursor:pointer;font-size:14px;line-height:1;box-shadow:0 1px 5px rgba(0,0,0,.2)}
.leaflet-locate-btn:hover{background:#f4f4f4}
.area-size-btns{display:flex;gap:6px;margin-top:8px}
.size-btn{flex:1;padding:6px 4px;border:1px solid var(--bdr);border-radius:6px;background:var(--bg);
          font-size:12px;font-weight:500;cursor:pointer;text-align:center;transition:.15s;color:var(--tx2)}
.size-btn:hover{border-color:var(--ac);color:var(--ac)}
.size-btn.active{background:var(--ac);border-color:var(--ac);color:#fff}
.area-hint{font-size:11px;color:var(--tx3);margin-top:6px;text-align:center}
#selected-area-info{font-size:12px;color:var(--ac);font-weight:600;margin-top:6px;min-height:18px}

/* Terminal */
.terminal-wrap{background:#0d1117;border-radius:0 0 8px 8px;overflow:hidden}
.terminal-topbar{background:#161b22;padding:8px 16px;display:flex;align-items:center;justify-content:space-between}
.terminal-dots{display:flex;gap:6px}
.terminal-dots span{width:11px;height:11px;border-radius:50%;display:inline-block}
.terminal-dots .dot-r{background:#ff5f56}
.terminal-dots .dot-y{background:#ffbd2e}
.terminal-dots .dot-g{background:#27c93f}
.terminal-title{font-size:12px;color:#6e7681;font-family:monospace}
.terminal-log{font-family:'Courier New',monospace;font-size:12.5px;color:#c9d1d9;
              height:260px;overflow-y:auto;padding:14px 16px;white-space:pre-wrap;word-break:break-all}
.terminal-log .t-ok{color:#3fb950}
.terminal-log .t-err{color:#f85149}
.terminal-log .t-warn{color:#d29922}
.terminal-log .t-info{color:#58a6ff}
.terminal-log .t-dim{color:#6e7681}

.progress-wrap{background:#161b22;padding:8px 16px 10px;border-top:1px solid #21262d}
.progress-label{display:flex;justify-content:space-between;font-size:11px;color:#6e7681;margin-bottom:5px;font-family:monospace}
.progress{height:4px;border-radius:2px;background:#21262d}
.progress-bar{background:#3fb950;border-radius:2px;transition:width .3s}

.result-strip{background:#161b22;border-top:1px solid #21262d;padding:12px 16px;
              display:flex;align-items:center;gap:20px;font-size:13px}
.result-strip .rs-item{color:#c9d1d9}
.result-strip .rs-item strong{color:#fff}
.result-strip .rs-item.success strong{color:#3fb950}
.result-strip .rs-item.warn strong{color:#d29922}
.result-strip .rs-action{margin-left:auto}

.status-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:5px}
.status-dot.running{background:#d29922;animation:pulse 1s infinite}
.status-dot.success{background:#3fb950}
.status-dot.error{background:#f85149}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

#panel-running{display:none}

@media(max-width:768px){
    .stats-bar{flex-wrap:wrap}
    .stat-item{flex:calc(50% - 1px)}
}
</style>
@endpush

@section('content')

{{-- Stats bar --}}
<div class="stats-bar" id="stats-bar">
    <div class="stat-item">
        <div class="label">Total Tempat</div>
        <div class="value" id="st-total">{{ number_format($stats['total']) }}<span>tempat</span></div>
    </div>
    <div class="stat-item">
        <div class="label">Punya Telepon</div>
        <div class="value" id="st-phone">{{ number_format($stats['phone']) }}<span>nomor</span></div>
    </div>
    <div class="stat-item">
        <div class="label">Scraped Hari Ini</div>
        <div class="value" id="st-today">{{ $stats['today'] }}<span>baru</span></div>
    </div>
    <div class="stat-item">
        <div class="label">Kategori Unik</div>
        <div class="value" id="st-cat">{{ $stats['category'] }}<span>kategori</span></div>
    </div>
</div>

{{-- Main scraper card --}}
<div class="card" style="border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.1)">
    <div class="card-header d-flex align-center justify-between">
        <div class="d-flex align-center" style="gap:8px">
            <span class="status-dot" id="status-dot"></span>
            <span style="font-size:14px;font-weight:600" id="status-label">Siap</span>
        </div>
        <button id="btn-reset" class="btn btn-sm btn-secondary" style="display:none;font-size:12px">
            <i class="fas fa-redo"></i> Scraping Baru
        </button>
    </div>

    {{-- Form --}}
    <div class="card-body" id="panel-form">

        {{-- Preset keyword chips --}}
        <div class="preset-chips">
            <span class="chip active" data-q="toko buah">toko buah</span>
            <span class="chip" data-q="distributor buah">distributor buah</span>
            <span class="chip" data-q="supplier buah">supplier buah</span>
            <span class="chip" data-q="grosir buah">grosir buah</span>
            <span class="chip" data-q="pasar buah">pasar buah</span>
            <span class="chip" data-q="restoran">restoran</span>
            <span class="chip" data-q="supermarket">supermarket</span>
        </div>

        {{-- Keyword + Jumlah --}}
        <div style="display:flex;gap:10px;align-items:flex-end;margin-bottom:16px">
            <div style="flex:1">
                <label style="font-size:12px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Kata Kunci</label>
                <input type="text" id="inp-query" class="form-control" value="toko buah" placeholder="cth: toko buah" style="font-size:14px;height:38px">
            </div>
            <div style="flex:0 0 90px">
                <label style="font-size:12px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Jumlah</label>
                <input type="number" id="inp-limit" class="form-control text-center" value="20" min="1" max="100" style="height:38px">
            </div>
        </div>

        {{-- Map area picker --}}
        <label style="font-size:12px;font-weight:600;color:var(--tx2);display:block;margin-bottom:6px">
            <i class="fas fa-map-marker-alt" style="color:var(--ac)"></i>
            Area Scraping — klik peta untuk pilih lokasi
        </label>
        <div id="area-map"></div>
        <div class="map-legend">
            <span><i style="background:#22c55e"></i> Punya WA</span>
            <span><i style="background:#ef4444"></i> Tidak Ada WA</span>
            <span><i style="background:#9ca3af"></i> Belum Dicek</span>
            <span style="margin-left:auto;color:var(--tx3)">{{ $existingPlaces->count() }} tempat ditampilkan</span>
        </div>

        {{-- Area size selector --}}
        <div class="area-size-btns">
            <button class="size-btn" data-zoom="15" data-radius="1000" onclick="setAreaSize(this)">
                <div>Kelurahan</div><div style="font-size:10px;opacity:.7">~1 km</div>
            </button>
            <button class="size-btn active" data-zoom="14" data-radius="3000" onclick="setAreaSize(this)">
                <div>Kecamatan</div><div style="font-size:10px;opacity:.7">~3 km</div>
            </button>
            <button class="size-btn" data-zoom="13" data-radius="8000" onclick="setAreaSize(this)">
                <div>Kota</div><div style="font-size:10px;opacity:.7">~8 km</div>
            </button>
            <button class="size-btn" data-zoom="11" data-radius="30000" onclick="setAreaSize(this)">
                <div>Kabupaten</div><div style="font-size:10px;opacity:.7">~30 km</div>
            </button>
        </div>
        <div id="selected-area-info">Klik peta untuk pilih titik pusat area scraping</div>

        <div style="margin-top:14px;text-align:right">
            <button id="btn-start" class="btn btn-primary" style="height:38px;padding:0 24px">
                <i class="fas fa-play"></i> Mulai Scraping
            </button>
        </div>
    </div>

    {{-- Terminal --}}
    <div id="panel-running" class="terminal-wrap">
        <div class="progress-wrap" id="progress-wrap" style="display:none">
            <div class="progress-label">
                <span id="prog-text">Memproses...</span>
                <span id="prog-pct">0%</span>
            </div>
            <div class="progress">
                <div class="progress-bar" id="prog-bar" style="width:0%"></div>
            </div>
        </div>
        <div class="terminal-topbar">
            <div class="terminal-dots">
                <span class="dot-r"></span><span class="dot-y"></span><span class="dot-g"></span>
            </div>
            <span class="terminal-title" id="terminal-title">gmaps-scraper</span>
        </div>
        <div class="terminal-log" id="log-output"></div>
        <div class="result-strip" id="result-strip" style="display:none">
            <div class="rs-item success"><strong id="rs-total">0</strong> berhasil</div>
            <span style="color:#30363d">·</span>
            <div class="rs-item warn"><strong id="rs-failed">0</strong> gagal</div>
            <span style="color:#30363d">·</span>
            <div class="rs-item"><strong id="rs-phone">0</strong> punya telepon</div>
            <span style="color:#30363d">·</span>
            <div class="rs-item">rating rata-rata <strong id="rs-rating">–</strong></div>
            <div class="rs-action">
                <a href="{{ route('places.index') }}" class="btn btn-sm btn-success">Lihat Data →</a>
            </div>
        </div>
    </div>
</div>

{{-- Google Cookies --}}
<div class="card" style="margin-top:16px" id="cookie-card">
    <div class="card-header d-flex align-center justify-between">
        <span style="font-weight:600;font-size:13px">
            <i class="fas fa-cookie-bite" style="color:#f59e0b;margin-right:6px"></i>Google Cookies
            <span class="text-xs text-muted" style="margin-left:6px">untuk akses data Jam Ramai</span>
        </span>
        <span id="cookie-status-badge" style="font-size:11px"></span>
    </div>
    <div class="card-body" style="padding:12px 16px" id="cookie-body-collapsed">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <span class="text-xs text-muted">Export cookies dari Chrome saat login ke Google Maps, paste di sini.</span>
            <button class="btn btn-sm btn-warning" onclick="toggleCookieForm()">
                <i class="fas fa-upload"></i> Paste Cookies
            </button>
            <button class="btn btn-sm btn-secondary" id="btn-check-cookies" onclick="checkCookies()">
                <i class="fas fa-plug"></i> Cek Valid
            </button>
            <span id="check-cookie-msg" style="font-size:12px"></span>
        </div>
    </div>
    <div id="cookie-form-wrap" style="display:none;padding:0 16px 16px">
        <p class="text-xs text-muted" style="margin:8px 0">
            1. Install <strong>Cookie-Editor</strong> di Chrome &nbsp;·&nbsp;
            2. Buka <strong>maps.google.com</strong> (pastikan login) &nbsp;·&nbsp;
            3. Klik Cookie-Editor → <strong>Export → Export as JSON</strong> &nbsp;·&nbsp;
            4. Paste hasilnya di bawah
        </p>
        <textarea id="cookie-input" rows="5" class="form-control"
            style="font-size:11px;font-family:monospace;resize:vertical"
            placeholder='[{"name":"SID","value":"...","domain":".google.com",...}, ...]'></textarea>
        <div style="display:flex;gap:8px;margin-top:8px;align-items:center">
            <button class="btn btn-sm btn-success" onclick="saveCookies()">
                <i class="fas fa-save"></i> Simpan
            </button>
            <button class="btn btn-sm btn-secondary" onclick="toggleCookieForm()">Batal</button>
            <span id="cookie-msg" style="font-size:12px;margin-left:4px"></span>
        </div>
    </div>
</div>

{{-- Rescrape incomplete data --}}
<div class="card" style="margin-top:16px" id="rescrape-card">
    <div class="card-header d-flex align-center justify-between">
        <div>
            <span style="font-weight:600;font-size:13px"><i class="fas fa-sync-alt" style="color:var(--or);margin-right:6px"></i>Perbarui Data Tidak Lengkap</span>
            <span class="text-muted text-xs" style="margin-left:8px">
                <span id="rescrape-count">…</span> tempat tanpa jam buka / ulasan
            </span>
        </div>
        <div class="d-flex align-center gap-8">
            <select id="rescrape-limit" class="form-control" style="width:100px;font-size:12.5px;padding:4px 28px 4px 8px">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="250">250</option>
                <option value="500">500</option>
                <option value="1000">Semua</option>
            </select>
            <button id="btn-rescrape" class="btn btn-sm" style="background:var(--or);color:#fff;border-color:var(--or)">
                <i class="fas fa-play"></i> Jalankan
            </button>
        </div>
    </div>
    <div id="rescrape-terminal" class="terminal-wrap" style="display:none">
        <div class="progress-wrap" id="rp-progress-wrap" style="display:none">
            <div class="progress-label">
                <span id="rp-prog-text">Memproses...</span>
                <span id="rp-prog-pct">0%</span>
            </div>
            <div class="progress"><div class="progress-bar" id="rp-prog-bar" style="width:0%;background:var(--or)"></div></div>
        </div>
        <div class="terminal-topbar">
            <div class="terminal-dots"><span class="dot-r"></span><span class="dot-y"></span><span class="dot-g"></span></div>
            <span class="terminal-title">gmaps-rescraper</span>
        </div>
        <div class="terminal-log" id="rp-log" style="height:200px"></div>
        <div class="result-strip" id="rp-result" style="display:none">
            <div class="rs-item success"><strong id="rp-ok">0</strong> diperbarui</div>
            <span style="color:#30363d">·</span>
            <div class="rs-item warn"><strong id="rp-fail">0</strong> gagal</div>
            <div class="rs-action">
                <button class="btn btn-sm btn-secondary" id="btn-rescrape-reset">Selesai</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
const $ = id => document.getElementById(id);

// ── Map setup ─────────────────────────────────────────────────────────────────
const existingPlaces = @json($existingPlaces);

// Default center
let defaultLat = -7.5, defaultLng = 112.5, defaultZoom = 10;
if (existingPlaces.length > 0) {
    const lats = existingPlaces.map(p => p[0]);
    const lngs = existingPlaces.map(p => p[1]);
    defaultLat = (Math.min(...lats) + Math.max(...lats)) / 2;
    defaultLng = (Math.min(...lngs) + Math.max(...lngs)) / 2;
    defaultZoom = 11;
}

const map = L.map('area-map', { zoomControl: true }).setView([defaultLat, defaultLng], defaultZoom);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 18
}).addTo(map);

// ── Place dots with cluster, color by WA status, popup ───────────────────────
function waColor(wa) {
    if (wa === true)  return '#22c55e';  // punya WA
    if (wa === false) return '#ef4444';  // tidak ada WA
    return '#9ca3af';                    // belum dicek
}

if (existingPlaces.length > 0) {
    const cluster = L.markerClusterGroup({
        maxClusterRadius: 40,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        iconCreateFunction: count => L.divIcon({
            html: `<div style="background:var(--ac);color:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;border:2px solid #fff;box-shadow:0 1px 5px rgba(0,0,0,.3)">${count.getChildCount()}</div>`,
            className: '', iconSize: [32, 32],
        }),
    });

    existingPlaces.forEach(p => {
        const [lat, lng, name, cat, rating, reviews, wa] = p;
        const color = waColor(wa);
        const marker = L.circleMarker([lat, lng], {
            radius: 5, color: '#fff', weight: 1,
            fillColor: color, fillOpacity: 0.85,
        });

        const waLabel = wa === true ? '<span style="color:#22c55e">✓ Punya WA</span>'
                      : wa === false ? '<span style="color:#ef4444">✗ Tidak Ada WA</span>'
                      : '<span style="color:#9ca3af">? Belum Dicek</span>';
        const stars = rating ? `⭐ ${rating}${reviews ? ` <span style="color:#9ca3af">(${reviews})</span>` : ''}` : '';

        marker.bindPopup(`
            <div style="min-width:160px;font-size:12px;line-height:1.6">
                <strong style="font-size:13px">${name || '—'}</strong><br>
                ${cat ? `<span style="color:#6b7280">${cat}</span><br>` : ''}
                ${stars ? `${stars}<br>` : ''}
                ${waLabel}
            </div>
        `, { maxWidth: 220 });

        cluster.addLayer(marker);
    });

    map.addLayer(cluster);
}

// ── Tombol Lokasi Saya ────────────────────────────────────────────────────────
const LocateControl = L.Control.extend({
    options: { position: 'topright' },
    onAdd() {
        const btn = L.DomUtil.create('button', 'leaflet-locate-btn');
        btn.title = 'Lokasi Saya';
        btn.innerHTML = '📍';
        L.DomEvent.on(btn, 'click', L.DomEvent.stopPropagation);
        L.DomEvent.on(btn, 'click', () => {
            btn.innerHTML = '⏳';
            navigator.geolocation.getCurrentPosition(
                pos => {
                    const { latitude: lat, longitude: lng } = pos.coords;
                    map.setView([lat, lng], 14);
                    L.circleMarker([lat, lng], {
                        radius: 8, color: '#3b82f6', weight: 3,
                        fillColor: '#93c5fd', fillOpacity: 0.9,
                    }).addTo(map).bindPopup('📍 Lokasi Anda').openPopup();
                    btn.innerHTML = '📍';
                },
                () => { alert('Izin lokasi ditolak.'); btn.innerHTML = '📍'; }
            );
        });
        return btn;
    }
});
new LocateControl().addTo(map);

// State
let pinMarker = null;
let radiusCircle = null;
let selectedLat = null, selectedLng = null;
let selectedZoom = 14;
let selectedRadius = 3000;

const pinIcon = L.divIcon({
    className: '',
    html: `<div style="width:16px;height:16px;border-radius:50%;background:var(--ac);border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>`,
    iconSize: [16, 16],
    iconAnchor: [8, 8]
});

function setPin(lat, lng) {
    selectedLat = lat;
    selectedLng = lng;

    if (pinMarker) pinMarker.setLatLng([lat, lng]);
    else pinMarker = L.marker([lat, lng], { icon: pinIcon, draggable: true }).addTo(map);

    pinMarker.on('dragend', e => {
        const pos = e.target.getLatLng();
        setPin(pos.lat, pos.lng);
    });

    updateCircle();
    document.getElementById('area-map').classList.add('has-pin');
    updateAreaInfo();
}

function updateCircle() {
    if (!selectedLat) return;
    if (radiusCircle) radiusCircle.setLatLng([selectedLat, selectedLng]).setRadius(selectedRadius);
    else radiusCircle = L.circle([selectedLat, selectedLng], {
        radius: selectedRadius,
        color: 'var(--ac)', fillColor: 'var(--ac)', fillOpacity: 0.08, weight: 1.5, dashArray: '4 4'
    }).addTo(map);
}

function updateAreaInfo() {
    if (!selectedLat) return;
    const active = document.querySelector('.size-btn.active');
    const label = active ? active.querySelector('div:first-child').textContent : 'Kecamatan';
    $('selected-area-info').textContent =
        `📍 ${selectedLat.toFixed(5)}, ${selectedLng.toFixed(5)} · ${label} (zoom ${selectedZoom})`;
}

function setAreaSize(btn) {
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedZoom   = parseInt(btn.dataset.zoom);
    selectedRadius = parseInt(btn.dataset.radius);
    updateCircle();
    updateAreaInfo();
}

map.on('click', e => setPin(e.latlng.lat, e.latlng.lng));

// ── Preset chips ──────────────────────────────────────────────────────────────
document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', function () {
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        $('inp-query').value = this.dataset.q;
    });
});
$('inp-query').addEventListener('input', function () {
    document.querySelectorAll('.chip').forEach(c => {
        c.classList.toggle('active', c.dataset.q === this.value.trim());
    });
});

// ── Log helpers ───────────────────────────────────────────────────────────────
let logLines = [], jobId = null, pollInterval = null, jobLimit = 20;

function appendLine(line) {
    const div = document.createElement('div');
    const l = line.trim();
    if (!l) return;
    if (l.match(/✗|Error|error|Fatal|gagal/i)) div.className = 't-err';
    else if (l.match(/⚠|Warn/))               div.className = 't-warn';
    else if (l.match(/✓|✅|Selesai|berhasil/)) div.className = 't-ok';
    else if (l.match(/🌐|📋|📊|🔍|📍|Buka|Mencari/)) div.className = 't-info';
    else                                        div.className = 't-dim';
    div.textContent = line;
    $('log-output').appendChild(div);
    $('log-output').scrollTop = $('log-output').scrollHeight;
}

function updateProgress(processed) {
    if (!processed || !jobLimit) return;
    const pct = Math.min(Math.round((processed / jobLimit) * 100), 99);
    $('prog-bar').style.width = pct + '%';
    $('prog-pct').textContent = pct + '%';
    $('prog-text').textContent = `Memproses ${processed} dari ${jobLimit}...`;
}

function parseSummary(lines) {
    let total = 0, failed = 0, phone = 0, rating = '–';
    lines.forEach(l => {
        let m;
        if ((m = l.match(/Selesai:\s*(\d+)\s*berhasil,\s*(\d+)\s*gagal/))) { total = +m[1]; failed = +m[2]; }
        if ((m = l.match(/Punya nomor telepon:\s*(\d+)/)))  phone  = +m[1];
        if ((m = l.match(/Rata-rata rating:\s*([\d.]+)/)))   rating = m[1];
    });
    return { total, failed, phone, rating };
}

function setStatus(state, label) {
    $('status-dot').className = 'status-dot ' + state;
    $('status-label').textContent = label;
}

function showRunning(query, loc) {
    $('panel-form').style.display    = 'none';
    $('panel-running').style.display = 'block';
    $('progress-wrap').style.display = 'block';
    $('result-strip').style.display  = 'none';
    $('btn-reset').style.display     = 'none';
    $('terminal-title').textContent  = `"${query}"${loc ? ' — ' + loc : ''}`;
    $('log-output').innerHTML        = '';
    logLines = [];
}

function showDone(status, lines) {
    clearInterval(pollInterval);
    $('progress-wrap').style.display = 'none';
    $('btn-reset').style.display     = 'inline-block';
    if (status === 'success') {
        setStatus('success', 'Selesai');
        $('prog-bar').style.width = '100%';
        const s = parseSummary(lines);
        $('rs-total').textContent  = s.total;
        $('rs-failed').textContent = s.failed;
        $('rs-phone').textContent  = s.phone;
        $('rs-rating').textContent = s.rating;
        $('result-strip').style.display = 'flex';
        refreshStats();
    } else {
        setStatus('error', 'Error — cek log');
    }
}

function refreshStats() {
    fetch('{{ route("scraper.stats") }}')
        .then(r => r.json())
        .then(d => {
            $('st-total').innerHTML = d.total.toLocaleString('id') + '<span>tempat</span>';
            $('st-phone').innerHTML = d.phone.toLocaleString('id') + '<span>nomor</span>';
            $('st-today').innerHTML = d.today + '<span>baru</span>';
            $('st-cat').innerHTML   = d.category + '<span>kategori</span>';
        }).catch(() => {});
}

function poll() {
    fetch(`{{ url('/scraper/log') }}/${jobId}`)
        .then(r => r.json())
        .then(data => {
            data.lines.slice(logLines.length).forEach(l => { logLines.push(l); appendLine(l); });
            if (data.processed) updateProgress(data.processed);
            if (data.done) showDone(data.status, logLines);
        }).catch(() => {});
}

// ── Start ─────────────────────────────────────────────────────────────────────
$('btn-start').addEventListener('click', function () {
    const query = $('inp-query').value.trim();
    const limit = parseInt($('inp-limit').value) || 20;
    if (!query) { $('inp-query').focus(); return; }
    if (!selectedLat) {
        $('selected-area-info').textContent = '⚠ Klik peta terlebih dahulu untuk memilih area!';
        $('selected-area-info').style.color = 'var(--rd)';
        document.getElementById('area-map').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memulai...';
    setStatus('running', 'Menghubungi Google Maps...');

    const active = document.querySelector('.size-btn.active');
    const areaLabel = active ? active.querySelector('div:first-child').textContent : '';

    fetch('{{ route("scraper.start") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ query, lat: selectedLat, lng: selectedLng, zoom: selectedZoom, limit }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            $('btn-start').disabled = false;
            $('btn-start').innerHTML = '<i class="fas fa-play"></i> Mulai Scraping';
            setStatus('', 'Siap');
            return;
        }
        jobId    = data.job_id;
        jobLimit = data.limit;
        showRunning(query, areaLabel);
        setStatus('running', 'Berjalan...');
        pollInterval = setInterval(poll, 2000);
        setTimeout(poll, 800);
    })
    .catch(e => {
        alert('Gagal: ' + e.message);
        $('btn-start').disabled = false;
        $('btn-start').innerHTML = '<i class="fas fa-play"></i> Mulai Scraping';
    });
});

$('btn-reset').addEventListener('click', function () {
    clearInterval(pollInterval);
    jobId = null; logLines = [];
    $('panel-form').style.display    = 'block';
    $('panel-running').style.display = 'none';
    $('btn-reset').style.display     = 'none';
    $('btn-start').disabled = false;
    $('btn-start').innerHTML = '<i class="fas fa-play"></i> Mulai Scraping';
    setStatus('', 'Siap');
    setTimeout(() => map.invalidateSize(), 50);
});

// ── Rescrape ──────────────────────────────────────────────────────────────────
let rpJobId = null, rpPoll = null, rpLines = [], rpLimit = 20;

fetch('{{ route("scraper.rescrape-count") }}')
    .then(r => r.json())
    .then(d => { $('rescrape-count').textContent = d.count.toLocaleString('id'); })
    .catch(() => { $('rescrape-count').textContent = '?'; });

function rpAppend(line) {
    const div = document.createElement('div');
    if (!line.trim()) return;
    if (line.match(/✗|Error|Gagal/i)) div.className = 't-err';
    else if (line.match(/⚠|missing/i))  div.className = 't-warn';
    else if (line.match(/✓|Updated/i))  div.className = 't-ok';
    else if (line.match(/🌐|📋|🔄|📊/)) div.className = 't-info';
    else                                 div.className = 't-dim';
    div.textContent = line;
    $('rp-log').appendChild(div);
    $('rp-log').scrollTop = $('rp-log').scrollHeight;
}

function rpUpdateProg(n) {
    const pct = Math.min(Math.round((n / rpLimit) * 100), 99);
    $('rp-prog-bar').style.width = pct + '%';
    $('rp-prog-pct').textContent = pct + '%';
    $('rp-prog-text').textContent = `Memproses ${n} dari ${rpLimit}...`;
}

function rpDone(status, lines) {
    clearInterval(rpPoll);
    $('rp-progress-wrap').style.display = 'none';
    $('btn-rescrape').disabled = false;
    $('btn-rescrape').innerHTML = '<i class="fas fa-play"></i> Jalankan';
    if (status === 'success') {
        const m = lines.find(l => l.match(/Selesai:/));
        let ok = 0, fail = 0;
        if (m) { const x = m.match(/(\d+) berhasil.*?(\d+) gagal/); if(x){ok=+x[1];fail=+x[2];} }
        $('rp-ok').textContent   = ok;
        $('rp-fail').textContent = fail;
        $('rp-result').style.display = 'flex';
        fetch('{{ route("scraper.rescrape-count") }}').then(r=>r.json()).then(d=>{
            $('rescrape-count').textContent = d.count.toLocaleString('id');
        }).catch(()=>{});
    }
}

$('btn-rescrape').addEventListener('click', function () {
    rpLimit = parseInt($('rescrape-limit').value) || 20;
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    $('rescrape-terminal').style.display = 'block';
    $('rp-progress-wrap').style.display  = 'block';
    $('rp-result').style.display         = 'none';
    $('rp-log').innerHTML = '';
    rpLines = [];

    fetch('{{ route("scraper.rescrape") }}', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ limit: rpLimit }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) { alert(d.error); $('btn-rescrape').disabled=false; $('btn-rescrape').innerHTML='<i class="fas fa-play"></i> Jalankan'; return; }
        rpJobId = d.job_id;
        rpPoll  = setInterval(() => {
            fetch(`{{ url('/scraper/log') }}/${rpJobId}`)
                .then(r=>r.json())
                .then(d => {
                    d.lines.slice(rpLines.length).forEach(l=>{rpLines.push(l);rpAppend(l);});
                    if (d.processed) rpUpdateProg(d.processed);
                    if (d.done) rpDone(d.status, rpLines);
                }).catch(()=>{});
        }, 2500);
        setTimeout(() => {
            fetch(`{{ url('/scraper/log') }}/${rpJobId}`)
                .then(r=>r.json())
                .then(d=>{d.lines.forEach(l=>{rpLines.push(l);rpAppend(l);});if(d.done)rpDone(d.status,rpLines);}).catch(()=>{});
        }, 1000);
    })
    .catch(e => { alert('Gagal: '+e.message); $('btn-rescrape').disabled=false; $('btn-rescrape').innerHTML='<i class="fas fa-play"></i> Jalankan'; });
});

$('btn-rescrape-reset').addEventListener('click', function () {
    clearInterval(rpPoll);
    rpJobId = null; rpLines = [];
    $('rescrape-terminal').style.display = 'none';
    $('btn-rescrape').disabled = false;
    $('btn-rescrape').innerHTML = '<i class="fas fa-play"></i> Jalankan';
});

// ── Google Cookies ────────────────────────────────────────────────────────────
function toggleCookieForm() {
    const wrap = $('cookie-form-wrap');
    const body = $('cookie-body-collapsed');
    const showing = wrap.style.display !== 'none';
    wrap.style.display = showing ? 'none' : 'block';
    body.style.display = showing ? 'block' : 'none';
}

function saveCookies() {
    const val = $('cookie-input').value.trim();
    if (!val) { $('cookie-msg').textContent = 'Paste cookies dulu.'; return; }
    $('cookie-msg').textContent = 'Menyimpan...';
    fetch('{{ route("scraper.save-cookies") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ cookies: val }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) {
            $('cookie-msg').style.color = '#ef4444';
            $('cookie-msg').textContent = d.error;
        } else {
            $('cookie-msg').style.color = '#22c55e';
            $('cookie-msg').textContent = d.message;
            $('cookie-input').value = '';
            setTimeout(() => { toggleCookieForm(); loadCookieStatus(); }, 1500);
        }
    })
    .catch(e => { $('cookie-msg').textContent = 'Error: ' + e.message; });
}

function checkCookies() {
    const btn = $('btn-check-cookies');
    const msg = $('check-cookie-msg');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengecek...';
    msg.style.color = '#6b7280';
    msg.textContent = 'Membuka Google Maps, tunggu ~15 detik...';
    fetch('{{ route("scraper.check-cookies") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({}),
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> Cek Valid';
        if (d.valid) {
            msg.style.color = '#22c55e';
            msg.textContent = d.message;
        } else {
            msg.style.color = '#ef4444';
            msg.textContent = d.message;
        }
    })
    .catch(e => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> Cek Valid';
        msg.style.color = '#ef4444';
        msg.textContent = 'Error: ' + e.message;
    });
}

function loadCookieStatus() {
    fetch('{{ route("scraper.cookie-status") }}')
    .then(r => r.json())
    .then(d => {
        const badge = $('cookie-status-badge');
        if (d.exists) {
            badge.innerHTML = `<span style="color:#22c55e"><i class="fas fa-check-circle"></i> ${d.count} cookies aktif &nbsp;·&nbsp; disimpan ${d.saved_at}</span>`;
        } else {
            badge.innerHTML = `<span style="color:#f59e0b"><i class="fas fa-exclamation-triangle"></i> Belum ada cookies</span>`;
        }
    });
}
loadCookieStatus();
</script>
@endpush
