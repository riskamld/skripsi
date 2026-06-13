@extends('layouts.app')
@section('title', 'Scraping — Mafaza Fortuna')
@section('page-title', 'Scraping Google Maps')

@push('styles')
<style>
/* ── Stats bar ─────────────────────────────────────── */
.stats-bar { display:flex; gap:1px; background:#dee2e6; border-radius:8px; overflow:hidden; margin-bottom:20px; }
.stat-item { flex:1; background:#fff; padding:14px 18px; }
.stat-item .label { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#6c757d; font-weight:600; margin-bottom:2px; }
.stat-item .value { font-size:24px; font-weight:700; color:#212529; line-height:1; }
.stat-item .value span { font-size:13px; font-weight:400; color:#6c757d; margin-left:4px; }

/* ── Form card ─────────────────────────────────────── */
.scraper-card { border:none; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.1); }
.scraper-card .card-header { background:#fff; border-bottom:1px solid #f0f0f0; border-radius:10px 10px 0 0 !important; padding:14px 20px; }
.scraper-card .card-body { padding:20px; }

.preset-chips { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px; }
.chip { display:inline-block; padding:4px 12px; border-radius:20px; border:1px solid #ced4da;
        font-size:12px; color:#495057; background:#f8f9fa; cursor:pointer; transition:.15s; user-select:none; }
.chip:hover { background:#007bff; border-color:#007bff; color:#fff; }
.chip.active { background:#007bff; border-color:#007bff; color:#fff; }

.form-row-compact { display:flex; gap:10px; align-items:flex-end; }
.form-row-compact .fg-query { flex:2; }
.form-row-compact .fg-area  { flex:1.5; }
.form-row-compact .fg-limit { flex:0 0 90px; }
.form-row-compact .fg-btn   { flex:0 0 auto; }
.form-row-compact label { font-size:12px; font-weight:600; color:#495057; margin-bottom:4px; display:block; }
.form-row-compact .form-control { font-size:14px; height:38px; }

/* ── Terminal ──────────────────────────────────────── */
.terminal-wrap { background:#0d1117; border-radius:0 0 10px 10px; overflow:hidden; }
.terminal-topbar { background:#161b22; padding:8px 16px; display:flex; align-items:center; justify-content:space-between; }
.terminal-dots { display:flex; gap:6px; }
.terminal-dots span { width:11px; height:11px; border-radius:50%; display:inline-block; }
.terminal-dots .dot-r { background:#ff5f56; }
.terminal-dots .dot-y { background:#ffbd2e; }
.terminal-dots .dot-g { background:#27c93f; }
.terminal-title { font-size:12px; color:#6e7681; font-family:monospace; }
.terminal-log { font-family:'Courier New',monospace; font-size:12.5px; color:#c9d1d9;
                height:260px; overflow-y:auto; padding:14px 16px; white-space:pre-wrap; word-break:break-all; }
.terminal-log .t-ok   { color:#3fb950; }
.terminal-log .t-err  { color:#f85149; }
.terminal-log .t-warn { color:#d29922; }
.terminal-log .t-info { color:#58a6ff; }
.terminal-log .t-dim  { color:#6e7681; }

/* ── Progress bar ──────────────────────────────────── */
.progress-wrap { background:#161b22; padding:8px 16px 10px; border-top:1px solid #21262d; }
.progress-label { display:flex; justify-content:space-between; font-size:11px; color:#6e7681; margin-bottom:5px; font-family:monospace; }
.progress { height:4px; border-radius:2px; background:#21262d; }
.progress-bar { background:#3fb950; border-radius:2px; transition:width .3s; }

/* ── Result strip ──────────────────────────────────── */
.result-strip { background:#161b22; border-top:1px solid #21262d; padding:12px 16px;
                display:flex; align-items:center; gap:20px; font-size:13px; }
.result-strip .rs-item { color:#c9d1d9; }
.result-strip .rs-item strong { color:#fff; }
.result-strip .rs-item.success strong { color:#3fb950; }
.result-strip .rs-item.warn    strong { color:#d29922; }
.result-strip .rs-sep { color:#30363d; }
.result-strip .rs-action { margin-left:auto; }

/* ── Status badge ──────────────────────────────────── */
.status-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:5px; }
.status-dot.running { background:#d29922; animation:pulse 1s infinite; }
.status-dot.success { background:#3fb950; }
.status-dot.error   { background:#f85149; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

#panel-running { display:none; }
</style>
@endpush

@section('content')
<div>

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

    {{-- Scraper card --}}
    <div class="card scraper-card">
        <div class="card-header d-flex align-center justify-between">
            <div class="d-flex align-center" style="gap:8px;">
                <span class="status-dot" id="status-dot"></span>
                <span style="font-size:14px;font-weight:600;" id="status-label">Siap</span>
            </div>
            <button id="btn-reset" class="btn btn-sm btn-secondary" style="display:none;font-size:12px;">
                <i class="fas fa-redo"></i>Scraping Baru
            </button>
        </div>

        <div class="card-body" id="panel-form">
            {{-- Preset chips --}}
            <div class="preset-chips">
                <span class="chip active" data-q="toko buah">toko buah</span>
                <span class="chip" data-q="distributor buah">distributor buah</span>
                <span class="chip" data-q="supplier buah">supplier buah</span>
                <span class="chip" data-q="pasar buah">pasar buah</span>
                <span class="chip" data-q="agen buah">agen buah</span>
            </div>

            {{-- Form --}}
            <div class="form-row-compact">
                <div class="fg-query">
                    <label>Kata Kunci</label>
                    <input type="text" id="inp-query" class="form-control" value="toko buah" placeholder="cth: toko buah">
                </div>
                <div class="fg-area">
                    <label>Area / Kota</label>
                    <input type="text" id="inp-area" class="form-control" placeholder="cth: Bandung">
                </div>
                <div class="fg-limit">
                    <label>Jumlah</label>
                    <input type="number" id="inp-limit" class="form-control text-center" value="20" min="1" max="100">
                </div>
                <div class="fg-btn">
                    <button id="btn-start" class="btn btn-primary" style="height:38px;white-space:nowrap;">
                        <i class="fas fa-play"></i>Mulai
                    </button>
                </div>
            </div>
        </div>

        {{-- Terminal panel (muncul saat berjalan) --}}
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
                    <span class="dot-r"></span>
                    <span class="dot-y"></span>
                    <span class="dot-g"></span>
                </div>
                <span class="terminal-title" id="terminal-title">gmaps-scraper</span>
            </div>
            <div class="terminal-log" id="log-output"></div>

            {{-- Result strip (muncul saat selesai) --}}
            <div class="result-strip" id="result-strip" style="display:none">
                <div class="rs-item success"><strong id="rs-total">0</strong> berhasil</div>
                <span class="rs-sep">·</span>
                <div class="rs-item warn"><strong id="rs-failed">0</strong> gagal</div>
                <span class="rs-sep">·</span>
                <div class="rs-item"><strong id="rs-phone">0</strong> punya telepon</div>
                <span class="rs-sep">·</span>
                <div class="rs-item">rata-rata rating <strong id="rs-rating">–</strong></div>
                <div class="rs-action">
                    <a href="{{ route('places.index') }}" class="btn btn-sm btn-success">
                        Lihat Data <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Rescrape incomplete data card --}}
    <div class="card" style="margin-top:16px;" id="rescrape-card">
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
                <span class="rs-sep">·</span>
                <div class="rs-item warn"><strong id="rp-fail">0</strong> gagal</div>
                <div class="rs-action">
                    <button class="btn btn-sm btn-secondary" id="btn-rescrape-reset">Selesai</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const $ = id => document.getElementById(id);

let jobId = null, pollInterval = null, logLines = [], jobLimit = 20;

// ── Preset chips ─────────────────────────────────────
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

// ── Log rendering ────────────────────────────────────
function appendLine(line) {
    const div = document.createElement('div');
    const l = line.trim();
    if (!l) return;
    if (l.match(/✗|Error|error|Fatal|gagal/i)) div.className = 't-err';
    else if (l.match(/⚠|Warn/))               div.className = 't-warn';
    else if (l.match(/✓|✅|Selesai|berhasil/)) div.className = 't-ok';
    else if (l.match(/🌐|📋|📊|🔍|Buka|Mencari/)) div.className = 't-info';
    else                                        div.className = 't-dim';
    div.textContent = line;
    $('log-output').appendChild(div);
    $('log-output').scrollTop = $('log-output').scrollHeight;
}

// ── Progress ─────────────────────────────────────────
function updateProgress(processed) {
    if (!processed || !jobLimit) return;
    const pct = Math.min(Math.round((processed / jobLimit) * 100), 99);
    $('prog-bar').style.width = pct + '%';
    $('prog-pct').textContent = pct + '%';
    $('prog-text').textContent = `Memproses ${processed} dari ${jobLimit}...`;
}

// ── Parse summary from log lines ─────────────────────
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

// ── Status UI helpers ────────────────────────────────
function setStatus(state, label) {
    const dot = $('status-dot');
    dot.className = 'status-dot ' + state;
    $('status-label').textContent = label;
}

function showRunning(query, area) {
    $('panel-form').style.display    = 'none';
    $('panel-running').style.display = 'block';
    $('progress-wrap').style.display = 'block';
    $('result-strip').style.display  = 'none';
    $('btn-reset').style.display     = 'none';
    $('terminal-title').textContent  = area ? `"${query}" di ${area}` : `"${query}"`;
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
        setStatus('error', 'Error — cek log di atas');
    }
}

// ── Refresh stats bar ────────────────────────────────
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

// ── Poll log ─────────────────────────────────────────
function poll() {
    fetch(`{{ url('/scraper/log') }}/${jobId}`)
        .then(r => r.json())
        .then(data => {
            const newLines = data.lines.slice(logLines.length);
            newLines.forEach(l => { logLines.push(l); appendLine(l); });
            if (data.processed) updateProgress(data.processed);
            if (data.done) showDone(data.status, logLines);
        }).catch(() => {});
}

// ── Start ─────────────────────────────────────────────
$('btn-start').addEventListener('click', function () {
    const query = $('inp-query').value.trim();
    const area  = $('inp-area').value.trim();
    const limit = parseInt($('inp-limit').value) || 20;
    if (!query) { $('inp-query').focus(); return; }

    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Memulai...';
    setStatus('running', 'Menghubungi Google Maps...');

    fetch('{{ route("scraper.start") }}', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ query, area, limit }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            $('btn-start').disabled = false;
            $('btn-start').innerHTML = '<i class="fas fa-play"></i>Mulai';
            setStatus('', 'Siap');
            return;
        }
        jobId    = data.job_id;
        jobLimit = data.limit;
        showRunning(query, area);
        setStatus('running', 'Berjalan...');
        pollInterval = setInterval(poll, 2000);
        setTimeout(poll, 800);
    })
    .catch(e => {
        alert('Gagal: ' + e.message);
        $('btn-start').disabled = false;
        $('btn-start').innerHTML = '<i class="fas fa-play"></i>Mulai';
    });
});

// ── Reset ─────────────────────────────────────────────
$('btn-reset').addEventListener('click', function () {
    clearInterval(pollInterval);
    jobId = null; logLines = [];
    $('panel-form').style.display    = 'block';
    $('panel-running').style.display = 'none';
    $('btn-reset').style.display     = 'none';
    $('btn-start').disabled = false;
    $('btn-start').innerHTML = '<i class="fas fa-play"></i>Mulai';
    setStatus('', 'Siap');
});

// ── Rescrape incomplete data ──────────────────────────
let rpJobId = null, rpPoll = null, rpLines = [], rpLimit = 20;

// Load count on page load
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
        // refresh count badge
        fetch('{{ route("scraper.rescrape-count") }}').then(r=>r.json()).then(d=>{
            $('rescrape-count').textContent = d.count.toLocaleString('id');
        }).catch(()=>{});
    }
}

function rpPollFn() {
    fetch(`{{ url('/scraper/log') }}/${rpJobId}`)
        .then(r => r.json())
        .then(d => {
            d.lines.slice(rpLines.length).forEach(l => { rpLines.push(l); rpAppend(l); });
            if (d.processed) rpUpdateProg(d.processed);
            if (d.done) rpDone(d.status, rpLines);
        }).catch(()=>{});
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
        rpPoll  = setInterval(rpPollFn, 2500);
        setTimeout(rpPollFn, 1000);
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
</script>
@endpush
