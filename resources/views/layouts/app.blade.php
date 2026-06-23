<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Mafaza Fortuna')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --sw:220px; --th:52px; --bg:#f6f8fa; --sur:#fff;
  --bdr:#e5e7eb; --tx:#111827; --tx2:#6b7280; --tx3:#9ca3af;
  --ac:#2563eb; --acl:#eff6ff; --ach:#1d4ed8;
  --gn:#16a34a; --gnl:#f0fdf4; --rd:#dc2626; --rdl:#fef2f2;
  --or:#ea580c; --orl:#fff7ed;
  --r:8px; --sh:0 1px 3px rgba(0,0,0,.08);
}

html,body{height:100%;font-family:'Inter',sans-serif;font-size:14px;color:var(--tx);background:var(--bg);-webkit-font-smoothing:antialiased}

/* ── LAYOUT ── */
.layout{display:flex;height:100vh;overflow:hidden}

/* ── OVERLAY (mobile) ── */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:90}
.overlay.show{display:block}

/* ── SIDEBAR ── */
.sidebar{
  width:var(--sw);flex-shrink:0;background:var(--sur);
  border-right:1px solid var(--bdr);display:flex;flex-direction:column;
  overflow-y:auto;transition:transform .22s ease;z-index:95;
}
.sidebar-brand{
  height:var(--th);display:flex;align-items:center;padding:0 16px;
  border-bottom:1px solid var(--bdr);font-weight:700;font-size:14.5px;
  letter-spacing:-.01em;flex-shrink:0;gap:8px;
}
.brand-dot{width:8px;height:8px;border-radius:50%;background:var(--ac)}
.sidebar-nav{padding:8px 6px;flex:1}
.nav-sep{border:none;border-top:1px solid var(--bdr);margin:8px 4px}
.nav-lbl{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--tx3);padding:10px 10px 4px}
.nav-link{
  display:flex;align-items:center;gap:8px;padding:7px 10px;
  border-radius:6px;color:var(--tx2);text-decoration:none;
  font-size:13px;font-weight:500;transition:background .1s,color .1s;margin-bottom:1px;
}
.nav-link:hover{background:var(--bg);color:var(--tx)}
.nav-link.active{background:var(--acl);color:var(--ac);font-weight:600}
.nav-icon{width:15px;text-align:center;font-size:12.5px;flex-shrink:0}

/* ── MAIN ── */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0}

/* ── TOPBAR ── */
.topbar{
  height:var(--th);background:var(--sur);border-bottom:1px solid var(--bdr);
  display:flex;align-items:center;padding:0 20px;flex-shrink:0;gap:10px;
}
.topbar-menu{display:none;background:none;border:none;cursor:pointer;padding:4px;color:var(--tx2);font-size:16px;line-height:1}
.topbar-title{font-size:13.5px;font-weight:600;color:var(--tx);flex:1}
.topbar-actions{display:flex;align-items:center;gap:6px}

/* ── PAGE ── */
.page{flex:1;overflow-y:auto;padding:20px}

/* ── CARD ── */
.card{background:var(--sur);border:1px solid var(--bdr);border-radius:var(--r);box-shadow:var(--sh)}
.card-header{padding:12px 16px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;font-size:13px;font-weight:600;gap:8px;flex-wrap:wrap}
.card-body{padding:16px}
.card-footer{padding:10px 16px;border-top:1px solid var(--bdr);font-size:12.5px}

/* ── METRIC ── */
.metric{background:var(--sur);border:1px solid var(--bdr);border-radius:var(--r);padding:16px}
.metric-icon{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px;margin-bottom:10px}
.mi-blue{background:var(--acl);color:var(--ac)} .mi-green{background:var(--gnl);color:var(--gn)}
.mi-orange{background:var(--orl);color:var(--or)} .mi-red{background:var(--rdl);color:var(--rd)}
.metric-label{font-size:11px;font-weight:500;color:var(--tx2);margin-bottom:4px}
.metric-value{font-size:26px;font-weight:700;color:var(--tx);line-height:1}
.metric-value small{font-size:11px;font-weight:400;color:var(--tx3);margin-left:3px}

/* ── BUTTONS — compact ── */
.btn{
  display:inline-flex;align-items:center;gap:5px;
  padding:5px 11px;border-radius:6px;font-size:12.5px;font-weight:500;
  cursor:pointer;border:1px solid transparent;
  transition:background .1s,border-color .1s,color .1s;
  text-decoration:none;line-height:1.5;white-space:nowrap;font-family:inherit;
}
.btn-primary{background:var(--ac);color:#fff;border-color:var(--ac)}
.btn-primary:hover{background:var(--ach);border-color:var(--ach);color:#fff}
.btn-secondary{background:var(--sur);color:var(--tx2);border-color:var(--bdr)}
.btn-secondary:hover{background:var(--bg);color:var(--tx);text-decoration:none}
.btn-success{background:var(--gn);color:#fff;border-color:var(--gn)}
.btn-success:hover{background:#15803d;color:#fff}
.btn-danger{background:var(--rd);color:#fff;border-color:var(--rd)}
.btn-danger:hover{background:#b91c1c;color:#fff}
.btn-ghost{background:none;color:var(--tx2);border-color:transparent}
.btn-ghost:hover{background:var(--bg);color:var(--tx)}
.btn-sm{padding:3px 9px;font-size:11.5px;border-radius:5px}
.btn-xs{padding:2px 6px;font-size:11px;border-radius:4px}
.btn:disabled,.btn[disabled]{opacity:.45;cursor:not-allowed;pointer-events:none}
.btn-warning{background:#d97706;color:#fff;border-color:#d97706}.btn-warning:hover{background:#b45309;color:#fff}
.btn-info{background:#0891b2;color:#fff;border-color:#0891b2}.btn-info:hover{background:#0e7490;color:#fff}
.btn-orange{background:#ea580c;color:#fff;border-color:#ea580c}.btn-orange:hover{background:#c2410c;color:#fff}
.btn-outline{background:transparent!important;color:var(--tx2)!important;border-color:var(--bdr)!important}
.btn-outline:hover{background:var(--bg)!important;color:var(--tx)!important}
.btn-group{display:inline-flex}
.btn-group .btn{border-radius:0}
.btn-group .btn:first-child{border-radius:5px 0 0 5px}
.btn-group .btn:last-child{border-radius:0 5px 5px 0}

/* ── TABLE ── */
.table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
table{width:100%;border-collapse:collapse;min-width:600px}
table th{font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--tx2);padding:9px 12px;text-align:left;border-bottom:1px solid var(--bdr);background:var(--bg);white-space:nowrap}
table td{padding:9px 12px;border-bottom:1px solid var(--bdr);font-size:12.5px;color:var(--tx);vertical-align:middle}
table tr:last-child td{border-bottom:none}
table tr:hover td{background:#fafbfc}

/* ── BADGE ── */
.badge{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:20px;font-size:10.5px;font-weight:500;white-space:nowrap}
.badge-green{background:var(--gnl);color:var(--gn)}
.badge-red{background:var(--rdl);color:var(--rd)}
.badge-blue{background:var(--acl);color:var(--ac)}
.badge-gray{background:#f3f4f6;color:var(--tx2)}
.badge-orange{background:var(--orl);color:var(--or)}
.badge-yellow{background:#fefce8;color:#854d0e}

/* ── FORM ── */
.form-control{
  width:100%;padding:6px 10px;border:1px solid var(--bdr);border-radius:6px;
  font-size:13px;font-family:inherit;color:var(--tx);background:var(--sur);
  transition:border-color .1s,box-shadow .1s;line-height:1.5;
}
.form-control:focus{outline:none;border-color:var(--ac);box-shadow:0 0 0 3px rgba(37,99,235,.1)}
select.form-control:not([multiple]){appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%236b7280' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;padding-right:28px}
select.form-control[multiple]{padding:4px;background-image:none}
select.form-control[multiple] option{padding:4px 8px;border-radius:4px}
select.form-control[multiple] option:checked{background:var(--acl);color:var(--ac)}
.form-label{font-size:11.5px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px}
.form-group{margin-bottom:14px}
.input-group{display:flex}
.input-group .form-control{border-radius:6px 0 0 6px;flex:1}
.input-group .btn{border-radius:0 6px 6px 0;border-left:none}

/* ── ALERT ── */
.alert{padding:10px 14px;border-radius:6px;font-size:12.5px;display:flex;align-items:center;gap:8px;border:1px solid}
.alert-info{background:var(--acl);color:#1e40af;border-color:#bfdbfe}
.alert-warning{background:#fffbeb;color:#92400e;border-color:#fde68a}
.alert-success{background:var(--gnl);color:#14532d;border-color:#bbf7d0}
.alert-danger{background:var(--rdl);color:#991b1b;border-color:#fecaca}

/* ── PAGINATION ── */
.pagination{display:flex;gap:4px;align-items:center;flex-wrap:wrap}
.page-link{padding:4px 9px;border-radius:5px;border:1px solid var(--bdr);font-size:12px;color:var(--tx2);text-decoration:none;transition:all .1s}
.page-link:hover{background:var(--bg);color:var(--tx)}
.page-link.active{background:var(--ac);border-color:var(--ac);color:#fff}
.page-link.disabled{opacity:.4;pointer-events:none}

/* ── HELPERS ── */
.d-flex{display:flex} .d-none{display:none} .d-block{display:block}
.flex-wrap{flex-wrap:wrap} .flex-1{flex:1} .ml-auto{margin-left:auto}
.align-center{align-items:center} .justify-between{justify-content:space-between}
.gap-4{gap:4px} .gap-6{gap:6px} .gap-8{gap:8px} .gap-12{gap:12px} .gap-16{gap:16px}
.text-muted{color:var(--tx2)!important} .text-xs{font-size:11px} .text-sm{font-size:12px} .text-right{text-align:right} .text-center{text-align:center}
.fw-500{font-weight:500} .fw-600{font-weight:600} .fw-700{font-weight:700}
.mb-4{margin-bottom:4px} .mb-8{margin-bottom:8px} .mb-12{margin-bottom:12px}
.mb-16{margin-bottom:16px} .mb-20{margin-bottom:20px} .mb-24{margin-bottom:24px}
.mt-4{margin-top:4px} .mt-8{margin-top:8px} .mt-12{margin-top:12px} .mt-16{margin-top:16px}
.p-0{padding:0} .w-100{width:100%}
code{font-size:11.5px;background:var(--bg);padding:1px 5px;border-radius:3px;border:1px solid var(--bdr);font-family:'Courier New',monospace}
.divider{border:none;border-top:1px solid var(--bdr)}

/* ── GRID ── */
.grid{display:grid;gap:16px}
.grid-2{grid-template-columns:repeat(2,1fr)}
.grid-3{grid-template-columns:repeat(3,1fr)}
.grid-4{grid-template-columns:repeat(4,1fr)}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .grid-4{grid-template-columns:repeat(2,1fr)}
  .grid-3{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:768px){
  :root{--sw:260px}
  .sidebar{position:fixed;height:100%;transform:translateX(-100%);box-shadow:4px 0 16px rgba(0,0,0,.12)}
  .sidebar.open{transform:translateX(0)}
  .topbar-menu{display:block}
  .page{padding:14px}
  .grid-4,.grid-3,.grid-2{grid-template-columns:1fr}
  table{min-width:500px}
  .hide-mobile{display:none!important}
  .topbar{padding:0 14px}
}
@media(max-width:480px){
  .page{padding:10px}
  .card-header{padding:10px 12px}
  .card-body{padding:12px}
}
</style>
@stack('styles')
</head>

<body>
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>
<div class="layout">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <span class="brand-dot"></span> Mafaza Fortuna
    </div>
    <nav class="sidebar-nav">
      <div class="nav-lbl">Operasional</div>
      <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-home nav-icon"></i> Dasbor
      </a>
      <a href="{{ route('scraper.index') }}" class="nav-link {{ request()->routeIs('scraper.index','scraper.stats') ? 'active' : '' }}">
        <i class="fas fa-robot nav-icon"></i> Scraping
      </a>
      <a href="{{ route('scraper-schedule.index') }}" class="nav-link {{ request()->routeIs('scraper-schedule.*') ? 'active' : '' }}" style="padding-left:24px">
        <i class="fas fa-calendar-alt nav-icon"></i> Jadwal Scraping
      </a>
      <a href="{{ route('scrape-logs.index') }}" class="nav-link {{ request()->routeIs('scrape-logs.*') ? 'active' : '' }}" style="padding-left:24px">
        <i class="fas fa-history nav-icon"></i> Log Scraping
      </a>
      <a href="{{ route('whatsapp.index') }}" class="nav-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}" style="display:flex;align-items:center;justify-content:space-between">
        <span><i class="fab fa-whatsapp nav-icon"></i> WhatsApp</span>
        @php $respondedCount = \App\Models\Place::where('outreach_status','responded')->count(); @endphp
        @if($respondedCount > 0)
        <span id="wa-badge" style="background:var(--gn);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;line-height:1.6">{{ $respondedCount }}</span>
        @else
        <span id="wa-badge" style="display:none"></span>
        @endif
      </a>
      <a href="{{ route('places.index') }}" class="nav-link {{ request()->routeIs('places.*') ? 'active' : '' }}">
        <i class="fas fa-store nav-icon"></i> Data Tempat
      </a>
      <a href="{{ route('map.index') }}" class="nav-link {{ request()->routeIs('map.*') ? 'active' : '' }}">
        <i class="fas fa-map nav-icon"></i> Peta
      </a>
      <a href="{{ route('kmeans.index') }}" class="nav-link {{ request()->routeIs('kmeans.*') ? 'active' : '' }}">
        <i class="fas fa-circle-nodes nav-icon"></i> Analisis K-Means
      </a>

      <div class="nav-lbl">Sistem</div>
      <a href="{{ route('database.index') }}" class="nav-link {{ request()->routeIs('database.*') ? 'active' : '' }}">
        <i class="fas fa-database nav-icon"></i> Database
      </a>
      <a href="{{ route('panduan.index') }}" class="nav-link {{ request()->routeIs('panduan.*') ? 'active' : '' }}">
        <i class="fas fa-book-open nav-icon"></i> Panduan
      </a>
      <a href="{{ route('api-tokens.index') }}" class="nav-link {{ request()->routeIs('api-tokens.*') ? 'active' : '' }}">
        <i class="fas fa-key nav-icon"></i> Token API
      </a>
      <a href="{{ route('telegram.index') }}" class="nav-link {{ request()->routeIs('telegram.*') ? 'active' : '' }}">
        <i class="fab fa-telegram nav-icon" style="color:#229ED9"></i> Telegram
      </a>
    </nav>
  </aside>

  <div class="main">
    <header class="topbar">
      <button class="topbar-menu" id="menuBtn" onclick="toggleSidebar()" aria-label="Menu">
        <i class="fas fa-bars"></i>
      </button>
      <div class="topbar-title">@yield('page-title', 'Dasbor')</div>
      <div class="topbar-actions">
        <button type="button" onclick="openSearchCatat()" class="btn btn-ghost btn-sm" title="Cari & Catat Respon" style="color:var(--tx2)">
          <i class="fas fa-search-plus"></i>
        </button>
        @stack('topbar-actions')
      </div>
    </header>

    <div class="page">
      @yield('content')
    </div>
  </div>

</div>

{{-- ═══ Modal Global: Catat Respon ═══════════════════════════════════════════ --}}
<div id="g-resp-modal" style="display:none;position:fixed;inset:0;z-index:900;background:rgba(0,0,0,.5);align-items:center;justify-content:center;padding:16px">
  <div style="background:var(--bg);border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.2);width:min(440px,96vw);overflow:hidden">
    <div style="padding:14px 16px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between">
      <span style="font-size:13px;font-weight:600"><i class="fas fa-comment-dots" style="color:var(--ac);margin-right:6px"></i>Catat Respon</span>
      <button onclick="closeGRespModal()" style="background:none;border:none;cursor:pointer;color:var(--tx3);font-size:16px;padding:2px 6px">&times;</button>
    </div>
    <div style="padding:16px;display:flex;flex-direction:column;gap:12px">
      <input type="hidden" id="g-resp-place-id">
      <div>
        <div style="font-size:11px;font-weight:600;color:var(--tx2);margin-bottom:6px">Status</div>
        <div style="display:flex;flex-wrap:wrap;gap:6px">
          <label class="g-resp-opt" data-val="replied"        data-color="#06b6d4" onclick="gSelectStatus('replied')">Sudah Respon</label>
          <label class="g-resp-opt" data-val="interested"     data-color="#f97316" onclick="gSelectStatus('interested')">Berminat</label>
          <label class="g-resp-opt" data-val="not_interested" data-color="#9ca3af" onclick="gSelectStatus('not_interested')">Tidak Berminat</label>
          <label class="g-resp-opt" data-val="ordered"        data-color="#10b981" onclick="gSelectStatus('ordered')">Order ✓</label>
        </div>
      </div>
      <div>
        <label style="font-size:11px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Nama Pelanggan</label>
        <input type="text" id="g-resp-customer" style="width:100%;box-sizing:border-box;border:1px solid var(--bdr);border-radius:6px;padding:7px 10px;font-size:13px;background:var(--bg2);color:var(--tx);font-family:inherit" placeholder="Nama kontak / pemilik toko" maxlength="100">
      </div>
      <div>
        <label style="font-size:11px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Keterangan</label>
        <textarea id="g-resp-notes" rows="3" maxlength="2000" style="width:100%;box-sizing:border-box;border:1px solid var(--bdr);border-radius:6px;padding:7px 10px;font-size:13px;background:var(--bg2);color:var(--tx);font-family:inherit;resize:vertical" placeholder="Isi percakapan, kebutuhan, atau catatan…"></textarea>
      </div>
      <div>
        <label style="font-size:11px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Tugas Selanjutnya</label>
        <textarea id="g-resp-tugas" rows="2" maxlength="2000" style="width:100%;box-sizing:border-box;border:1px solid var(--bdr);border-radius:6px;padding:7px 10px;font-size:13px;background:var(--bg2);color:var(--tx);font-family:inherit;resize:vertical" placeholder="Langkah berikutnya, mis. kirim harga grosir, follow up minggu depan…"></textarea>
      </div>
      <div>
        <label style="font-size:11px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Skor Progres Closing (0–100)</label>
        <input type="number" id="g-resp-skor" min="0" max="100" style="width:100%;box-sizing:border-box;border:1px solid var(--bdr);border-radius:6px;padding:7px 10px;font-size:13px;background:var(--bg2);color:var(--tx);font-family:inherit" placeholder="cth: 70">
      </div>
      <div style="display:flex;gap:10px">
        <div style="flex:1">
          <label style="font-size:11px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Admin yang mencatat</label>
          <input type="text" id="g-resp-admin" style="width:100%;box-sizing:border-box;border:1px solid var(--bdr);border-radius:6px;padding:7px 10px;font-size:13px;background:var(--bg2);color:var(--tx);font-family:inherit" placeholder="Nama admin" maxlength="80">
        </div>
        <div style="flex:1">
          <label style="font-size:11px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Tanggal Respon</label>
          <input type="datetime-local" id="g-resp-date" style="width:100%;box-sizing:border-box;border:1px solid var(--bdr);border-radius:6px;padding:7px 10px;font-size:13px;background:var(--bg2);color:var(--tx);font-family:inherit">
        </div>
      </div>
    </div>
    <div style="padding:12px 16px;border-top:1px solid var(--bdr);display:flex;justify-content:flex-end;gap:8px">
      <button onclick="closeGRespModal()" style="padding:6px 14px;border:1px solid var(--bdr);border-radius:6px;background:var(--bg2);color:var(--tx2);cursor:pointer;font-size:13px">Batal</button>
      <button id="g-resp-save" onclick="saveGResp()" style="padding:6px 16px;border:1px solid var(--ac);border-radius:6px;background:var(--ac);color:#fff;cursor:pointer;font-size:13px;font-weight:600">
        <i class="fas fa-save"></i> Simpan
      </button>
    </div>
  </div>
</div>

{{-- ═══ Modal Global: Cari & Catat ════════════════════════════════════════════ --}}
<div id="g-search-modal" style="display:none;position:fixed;inset:0;z-index:910;background:rgba(0,0,0,.55);align-items:flex-start;justify-content:center;padding:60px 16px 16px">
  <div style="background:var(--bg);border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.25);width:min(520px,96vw);overflow:hidden">
    <div style="padding:14px 16px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;gap:10px">
      <i class="fas fa-search-plus" style="color:var(--ac);font-size:14px"></i>
      <input id="g-search-input" type="text" placeholder="Ketik nama atau nomor HP tempat…"
        style="flex:1;border:none;outline:none;font-size:13px;background:transparent;color:var(--tx);font-family:inherit"
        oninput="gSearchDebounce()">
      <button onclick="closeSearchCatat()" style="background:none;border:none;cursor:pointer;color:var(--tx3);font-size:16px;padding:2px 6px">&times;</button>
    </div>
    <div id="g-search-results" style="max-height:380px;overflow-y:auto">
      <div style="padding:24px;text-align:center;color:var(--tx3);font-size:12px">Ketik minimal 2 karakter untuk mencari</div>
    </div>
  </div>
</div>

<script>
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('show');
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('show');
}

// ── Global Catat Respon ───────────────────────────────────────────────────────
var gRespStatus = null;
var gRespCallback = null;

var gStatusColors = { replied:'#06b6d4', interested:'#f97316', not_interested:'#9ca3af', ordered:'#10b981' };

function openGRespModal(placeId, preselect, prefill, callback) {
  gRespStatus   = preselect || null;
  gRespCallback = callback  || null;
  document.getElementById('g-resp-place-id').value = placeId;
  document.getElementById('g-resp-customer').value  = (prefill && prefill.customer_name)   || '';
  document.getElementById('g-resp-notes').value     = (prefill && prefill.notes)            || '';
  document.getElementById('g-resp-tugas').value     = (prefill && prefill.tugas_selanjutnya) || '';
  document.getElementById('g-resp-skor').value      = (prefill && prefill.skor != null) ? prefill.skor : '';
  document.getElementById('g-resp-admin').value     = (prefill && prefill.response_admin)   || '';
  // Default tanggal = sekarang (format datetime-local: YYYY-MM-DDTHH:mm)
  var now = new Date(); now.setSeconds(0,0);
  document.getElementById('g-resp-date').value = (prefill && prefill.responded_at)
    ? prefill.responded_at.slice(0,16)
    : now.toISOString().slice(0,16);
  document.querySelectorAll('.g-resp-opt').forEach(function(el) { gStyleOpt(el, el.dataset.val === preselect); });
  document.getElementById('g-resp-modal').style.display = 'flex';
  setTimeout(function(){ document.getElementById('g-resp-customer').focus(); }, 80);
}
function closeGRespModal() { document.getElementById('g-resp-modal').style.display = 'none'; }

function gStyleOpt(el, active) {
  var c = el.dataset.color;
  el.style.borderColor = active ? c : 'var(--bdr)';
  el.style.background  = active ? c + '20' : '';
  el.style.color       = active ? c : '';
  el.style.fontWeight  = active ? '700' : '';
}
function gSelectStatus(val) {
  gRespStatus = val;
  document.querySelectorAll('.g-resp-opt').forEach(function(el) { gStyleOpt(el, el.dataset.val === val); });
}

async function saveGResp() {
  if (!gRespStatus) { alert('Pilih status terlebih dahulu.'); return; }
  var btn = document.getElementById('g-resp-save');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  try {
    var id = document.getElementById('g-resp-place-id').value;
    var resp = await fetch('{{ url('/whatsapp/mark-status') }}/' + id, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
      body: JSON.stringify({
        status:             gRespStatus,
        customer_name:      document.getElementById('g-resp-customer').value.trim(),
        notes:              document.getElementById('g-resp-notes').value.trim(),
        tugas_selanjutnya:  document.getElementById('g-resp-tugas').value.trim(),
        skor:               document.getElementById('g-resp-skor').value || null,
        response_admin:     document.getElementById('g-resp-admin').value.trim(),
        responded_at:       document.getElementById('g-resp-date').value || null,
      })
    });
    var d = await resp.json();
    if (d.status === 'ok') {
      closeGRespModal();
      if (typeof gRespCallback === 'function') gRespCallback(d);
    } else { alert('Gagal menyimpan.'); }
  } catch(e) { alert('Error: ' + e.message); }
  btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') { closeGRespModal(); closeSearchCatat(); }
});

// ── Global Cari & Catat ───────────────────────────────────────────────────────
var gSearchTimer;
var gStatusLabel = { sent:'Terkirim', replied:'Respon', interested:'Berminat', not_interested:'Tidak Berminat', ordered:'Order ✓' };
var gStatusColor = { sent:'#3b82f6', replied:'#06b6d4', interested:'#f97316', not_interested:'#9ca3af', ordered:'#10b981' };

function openSearchCatat() {
  document.getElementById('g-search-modal').style.display = 'flex';
  document.getElementById('g-search-results').innerHTML = '<div style="padding:24px;text-align:center;color:var(--tx3);font-size:12px">Ketik minimal 2 karakter untuk mencari</div>';
  setTimeout(function(){ document.getElementById('g-search-input').focus(); }, 60);
}
function closeSearchCatat() {
  document.getElementById('g-search-modal').style.display = 'none';
  document.getElementById('g-search-input').value = '';
}

function gSearchDebounce() {
  clearTimeout(gSearchTimer);
  var q = document.getElementById('g-search-input').value.trim();
  if (q.length < 2) {
    document.getElementById('g-search-results').innerHTML = '<div style="padding:24px;text-align:center;color:var(--tx3);font-size:12px">Ketik minimal 2 karakter untuk mencari</div>';
    return;
  }
  document.getElementById('g-search-results').innerHTML = '<div style="padding:16px;text-align:center;color:var(--tx3);font-size:12px"><i class="fas fa-spinner fa-spin"></i> Mencari…</div>';
  gSearchTimer = setTimeout(function(){ gDoSearch(q); }, 350);
}

async function gDoSearch(q) {
  try {
    var resp = await fetch('{{ route('places.quick-search') }}?q=' + encodeURIComponent(q), {
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    });
    var data = await resp.json();
    var el = document.getElementById('g-search-results');
    if (!data.length) {
      el.innerHTML = '<div style="padding:24px;text-align:center;color:var(--tx3);font-size:12px">Tidak ada tempat ditemukan.</div>';
      return;
    }
    el.innerHTML = data.map(function(p) {
      var statusHtml = p.outreach_status && gStatusLabel[p.outreach_status]
        ? '<span style="font-size:10px;font-weight:600;color:' + gStatusColor[p.outreach_status] + '">' + gStatusLabel[p.outreach_status] + '</span>'
        : '<span style="font-size:10px;color:var(--tx3)">Belum dikirim</span>';
      var thumb = p.thumb
        ? '<img src="' + p.thumb + '" style="width:36px;height:36px;border-radius:6px;object-fit:cover;flex-shrink:0;border:1px solid var(--bdr)" onerror="this.style.display=\'none\'">'
        : '<div style="width:36px;height:36px;border-radius:6px;background:var(--bg2);flex-shrink:0;display:flex;align-items:center;justify-content:center;color:var(--tx3);font-size:13px"><i class=\'fas fa-store\'></i></div>';
      var custLabel = p.customer_name ? '<span style="font-size:10px;color:var(--tx3)"> · ' + p.customer_name + '</span>' : '';
      return '<div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--bdr);cursor:pointer;transition:.1s" onmouseover="this.style.background=\'var(--bg2)\'" onmouseout="this.style.background=\'\'" onclick="gPickResult(' + JSON.stringify(p).replace(/'/g, '&#39;') + ')">'
        + thumb
        + '<div style="flex:1;min-width:0">'
        + '<div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + gEsc(p.name) + custLabel + '</div>'
        + '<div style="font-size:10px;color:var(--tx3)">' + gEsc(p.category || '—') + ' · ' + gEsc(p.phone || '—') + '</div>'
        + '</div>'
        + '<div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0">'
        + statusHtml
        + '<a href="' + p.detail_url + '" target="_blank" onclick="event.stopPropagation()" style="font-size:10px;color:var(--ac)">Detail <i class="fas fa-external-link-alt" style="font-size:9px"></i></a>'
        + '</div>'
        + '</div>';
    }).join('');
  } catch(e) {
    document.getElementById('g-search-results').innerHTML = '<div style="padding:16px;text-align:center;color:var(--rd);font-size:12px">Gagal mencari.</div>';
  }
}

function gPickResult(p) {
  closeSearchCatat();
  openGRespModal(p.id, p.outreach_status || null, { customer_name: p.customer_name, response_admin: p.response_admin }, function() {
    // reload jika di halaman places atau whatsapp
    if (typeof loadTargetList === 'function') loadTargetList();
  });
}

function gEsc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

{{-- Style untuk g-resp-opt labels --}}
<style>
.g-resp-opt {
  display:inline-block;cursor:pointer;font-size:12px;padding:5px 11px;
  border-radius:6px;border:1.5px solid var(--bdr);user-select:none;
  transition:border-color .15s,background .15s,color .15s;
}
</style>

@stack('scripts')
</body>
</html>
