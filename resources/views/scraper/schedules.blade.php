@extends('layouts.app')
@section('title', 'Jadwal Scraping')
@section('page-title', 'Jadwal Scraping')

@push('topbar-actions')
<button onclick="openAddModal()" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Jadwal</button>
@endpush

@section('content')

{{-- Status bar running --}}
<div id="status-bar" style="display:none;background:var(--acl,#eff6ff);border:1px solid var(--ac);border-radius:8px;padding:10px 14px;margin-bottom:14px;align-items:center;gap:10px;font-size:13px">
  <span class="spin-icon" style="display:inline-block;animation:spin 1s linear infinite;color:var(--ac)"><i class="fas fa-circle-notch"></i></span>
  <span id="status-bar-text">Scraper sedang berjalan...</span>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="card-header">
    <span><i class="fas fa-clock"></i> Jadwal Aktif</span>
    <span style="font-size:11.5px;color:var(--tx2)">Auto-refresh setiap 20 detik &bull; <span id="next-refresh">20</span> dtk</span>
  </div>

  @if($schedules->isEmpty())
  <div class="card-body" style="text-align:center;padding:40px;color:var(--tx2)">
    <i class="fas fa-calendar-times" style="font-size:32px;margin-bottom:10px;display:block;color:var(--tx3)"></i>
    Belum ada jadwal. Klik <b>Tambah Jadwal</b> untuk membuat yang pertama.
  </div>
  @else
  <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <thead>
        <tr style="background:var(--bg)">
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Status</th>
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Nama</th>
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Metode</th>
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Query / Area</th>
          <th style="padding:9px 14px;text-align:center;font-weight:600;border-bottom:1px solid var(--bdr)">Limit</th>
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Terakhir Jalan</th>
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Hasil</th>
          <th style="padding:9px 14px;text-align:center;font-weight:600;border-bottom:1px solid var(--bdr)">Aktif</th>
          <th style="padding:9px 14px;border-bottom:1px solid var(--bdr)"></th>
        </tr>
      </thead>
      <tbody id="schedule-tbody">
        @foreach($schedules as $s)
        @php
          $res      = $s->last_result;
          $resStatus = $res['status'] ?? '';
          $resOk    = $resStatus === 'success';
          $resBroken = $resStatus === 'selector_broken';
          $isKec    = $s->isKecamatanLevel();
          $radiusKm = $s->radiusKm();
        @endphp
        <tr id="row-{{ $s->id }}" class="sched-row{{ $s->is_running ? ' row-running' : '' }}" style="border-bottom:1px solid var(--bdr){{ $s->is_running ? ';background:var(--acl,#eff6ff)' : '' }}" data-id="{{ $s->id }}" data-running="{{ $s->is_running ? 'true' : 'false' }}">
          <td style="padding:10px 14px;white-space:nowrap">
            @if($s->is_running)
              <span class="badge-running"><span class="pulse-dot"></span> Running</span>
            @elseif($resBroken)
              <span style="color:#b91c1c;font-size:12px" title="Selector HTML Google Maps berubah — scraper perlu diperbaiki"><i class="fas fa-exclamation-triangle"></i> Selector Rusak</span>
            @elseif($res)
              @if($resOk && ($res['processed'] ?? 0) === 0)
                <span style="color:#d97706;font-size:12px" title="Scraper jalan tapi tidak ada tempat baru ditemukan"><i class="fas fa-exclamation-circle"></i> Kosong</span>
              @elseif($resOk)
                <span style="color:var(--gn);font-size:12px"><i class="fas fa-check-circle"></i> Selesai</span>
              @else
                <span style="color:var(--rd);font-size:12px"><i class="fas fa-times-circle"></i> Error</span>
              @endif
            @else
              <span style="color:var(--tx3);font-size:12px"><i class="fas fa-clock"></i> Menunggu</span>
            @endif
          </td>
          <td style="padding:10px 14px;font-weight:600">{{ $s->name }}</td>
          <td style="padding:10px 14px;font-size:12px;min-width:130px">
            @if($isKec)
              <span class="badge-kec"><i class="fas fa-map-pin"></i> Kecamatan</span>
              <div style="margin-top:4px;color:var(--tx2);font-size:11px;line-height:1.6">
                zoom&nbsp;<b>{{ $s->zoom }}</b> &bull; r&nbsp;<b>{{ $radiusKm }}km</b><br>
                <span class="coord-text" title="{{ number_format($s->lat,6) }}, {{ number_format($s->lng,6) }}">
                  {{ number_format($s->lat,4) }},&nbsp;{{ number_format($s->lng,4) }}
                </span>
              </div>
            @else
              <span class="badge-kota"><i class="fas fa-city"></i> Kota</span>
              <div style="margin-top:4px;color:var(--tx2);font-size:11px;line-height:1.6">
                zoom&nbsp;<b>{{ $s->zoom ?? 13 }}</b> &bull; r&nbsp;<b>{{ $radiusKm }}km</b><br>
                <span style="color:var(--tx3)">otomatis Google</span>
              </div>
            @endif
          </td>
          <td style="padding:10px 14px">
            <div>{{ $s->query }}</div>
            @if($s->area)<div style="font-size:11px;color:var(--tx2)">{{ $s->area }}</div>@endif
          </td>
          <td style="padding:10px 14px;text-align:center">{{ $s->limit }}</td>
          <td style="padding:10px 14px;font-size:12px;color:var(--tx2)">
            {{ $s->last_run_at ? $s->last_run_at->diffForHumans() : '—' }}
          </td>
          <td style="padding:10px 14px;font-size:12px">
            @if($s->is_running)
              <span style="color:var(--ac);font-size:12px">sedang berjalan...</span>
            @elseif($resBroken)
              <span style="color:#b91c1c;font-size:11px">DOM berubah</span>
            @elseif($res)
              @if($resOk && ($res['processed'] ?? 0) === 0)
                <span style="color:#d97706">0 tempat baru</span>
              @elseif($resOk)
                <span style="color:var(--gn)">{{ $res['processed'] ?? 0 }} tempat</span>
              @else
                <span style="color:var(--rd)">Gagal</span>
              @endif
            @else
              <span style="color:var(--tx3)">—</span>
            @endif
          </td>
          <td style="padding:10px 14px;text-align:center">
            <label class="toggle-wrap">
              <input type="checkbox" onchange="toggleSchedule({{ $s->id }}, this)" {{ $s->enabled ? 'checked' : '' }}>
              <span class="toggle-slider"></span>
            </label>
          </td>
          <td style="padding:10px 14px;white-space:nowrap">
            @if($s->current_log_file)
            <button onclick="openLog({{ $s->id }}, '{{ addslashes($s->name) }}')" class="btn btn-xs btn-log" title="Lihat Log">
              <i class="fas fa-terminal"></i>
            </button>
            @endif
            <button onclick="editSchedule({{ $s->id }}, {{ json_encode($s->toArray()) }})" class="btn btn-xs" title="Edit">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteSchedule({{ $s->id }}, '{{ addslashes($s->name) }}')" class="btn btn-xs btn-danger" title="Hapus">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- Log Modal --}}
<div id="log-modal-bg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center">
  <div style="background:var(--sur);border-radius:10px;width:860px;max-width:96vw;max-height:92vh;display:flex;flex-direction:column">
    <div style="padding:12px 16px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;gap:12px">
      <div style="display:flex;align-items:center;gap:10px">
        <span style="font-weight:700;font-size:14px" id="log-title">Log Scraper</span>
        <span id="log-running-badge" style="display:none" class="badge-running"><span class="pulse-dot"></span> Running</span>
        <span id="log-processed" style="font-size:12px;color:var(--tx2)"></span>
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <button id="log-scroll-btn" onclick="scrollToBottom()" class="btn btn-xs" title="Scroll ke bawah"><i class="fas fa-arrow-down"></i></button>
        <button onclick="closeLog()" style="background:none;border:none;cursor:pointer;color:var(--tx2);font-size:16px"><i class="fas fa-times"></i></button>
      </div>
    </div>
    <div id="log-content" style="flex:1;overflow-y:auto;background:#0f172a;color:#e2e8f0;font-family:monospace;font-size:12px;padding:14px;white-space:pre-wrap;word-break:break-all;min-height:300px;max-height:70vh">
      <span style="color:#64748b">Memuat log...</span>
    </div>
    <div style="padding:8px 14px;border-top:1px solid var(--bdr);font-size:11.5px;color:var(--tx3)" id="log-footer">
      Log diperbarui otomatis setiap 3 detik selama scraper berjalan
    </div>
  </div>
</div>

{{-- Modal Tambah/Edit --}}
<div id="modal-bg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;align-items:center;justify-content:center">
  <div style="background:var(--sur);border-radius:10px;width:460px;max-width:95vw;max-height:90vh;overflow-y:auto">
    <div style="padding:14px 16px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between">
      <span style="font-weight:700;font-size:14px" id="modal-title">Tambah Jadwal</span>
      <button onclick="closeModal()" style="background:none;border:none;cursor:pointer;color:var(--tx2);font-size:16px"><i class="fas fa-times"></i></button>
    </div>
    <form id="sched-form" onsubmit="submitForm(event)" style="padding:16px;display:flex;flex-direction:column;gap:12px">
      <input type="hidden" id="edit-id" value="">
      <div>
        <label class="flabel">Nama Jadwal</label>
        <input type="text" id="f-name" required maxlength="100" placeholder="Contoh: Toko Buah Malang" class="finput">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <label class="flabel">Query / Kata Kunci</label>
          <input type="text" id="f-query" required maxlength="100" placeholder="toko buah" class="finput">
        </div>
        <div>
          <label class="flabel">Area (opsional)</label>
          <input type="text" id="f-area" maxlength="100" placeholder="Malang" class="finput">
        </div>
      </div>
      <div>
        <label class="flabel">Limit Tempat per Sesi</label>
        <input type="number" id="f-limit" value="20" min="1" max="100" class="finput" style="width:100px">
      </div>
      <div>
        <label class="flabel">Frekuensi</label>
        <select id="f-frequency" onchange="onFreqChange()" class="finput">
          <option value="daily">Setiap hari (jam tertentu)</option>
          <option value="every_n_hours">Setiap N jam</option>
          <option value="weekly">Setiap minggu (hari + jam)</option>
        </select>
      </div>
      <div id="freq-hours" style="display:none">
        <label class="flabel">Interval (jam)</label>
        <input type="number" id="f-interval-hours" value="6" min="1" max="168" class="finput" style="width:100px">
        <span style="font-size:12px;color:var(--tx2);margin-left:6px">jam sekali</span>
      </div>
      <div id="freq-hour-row">
        <label class="flabel">Jam Mulai</label>
        <select id="f-run-hour" class="finput" style="width:120px">
          @for($h=0;$h<24;$h++)
          <option value="{{ $h }}" {{ $h===8?'selected':'' }}>{{ str_pad($h,2,'0',STR_PAD_LEFT) }}:00</option>
          @endfor
        </select>
      </div>
      <div id="freq-day" style="display:none">
        <label class="flabel">Hari</label>
        <select id="f-day-of-week" class="finput" style="width:140px">
          <option value="1">Senin</option><option value="2">Selasa</option><option value="3">Rabu</option>
          <option value="4">Kamis</option><option value="5">Jumat</option><option value="6">Sabtu</option>
          <option value="7">Minggu</option>
        </select>
      </div>
      <div style="display:flex;gap:8px;margin-top:4px">
        <button type="submit" class="btn btn-primary" style="flex:1">Simpan</button>
        <button type="button" onclick="closeModal()" class="btn" style="flex:1">Batal</button>
      </div>
    </form>
  </div>
</div>

<div id="toast" style="display:none;position:fixed;bottom:20px;right:20px;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;z-index:999;color:#fff"></div>

<style>
.btn{padding:6px 12px;border:1px solid var(--bdr);border-radius:6px;font-size:12.5px;font-weight:600;cursor:pointer;background:var(--sur);color:var(--tx)}
.btn-primary{background:var(--ac);color:#fff;border-color:var(--ac)}
.btn-danger{background:var(--rdl);color:var(--rd);border-color:var(--rd)20}
.btn-log{background:#0f172a20;color:#0f172a;border-color:#0f172a30}
.btn-sm{padding:5px 10px;font-size:12px}
.btn-xs{padding:4px 8px;font-size:11px}
.flabel{font-size:12px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px}
.finput{width:100%;padding:7px 10px;border:1px solid var(--bdr);border-radius:6px;font-size:13px}
.toggle-wrap{position:relative;display:inline-block;width:38px;height:21px;flex-shrink:0}
.toggle-wrap input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;inset:0;background:#d1d5db;border-radius:21px;cursor:pointer;transition:.2s}
.toggle-slider:before{content:'';position:absolute;width:15px;height:15px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s}
.toggle-wrap input:checked+.toggle-slider{background:var(--ac)}
.toggle-wrap input:checked+.toggle-slider:before{transform:translateX(17px)}
.badge-running{display:inline-flex;align-items:center;gap:5px;background:#dcfce7;color:#15803d;font-size:11.5px;font-weight:700;padding:2px 8px;border-radius:20px;border:1px solid #bbf7d0}
.badge-kec{display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:700;padding:2px 7px;border-radius:20px;border:1px solid #bbf7d0}
.badge-kota{display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;color:#475569;font-size:11px;font-weight:700;padding:2px 7px;border-radius:20px;border:1px solid #cbd5e1}
.coord-text{font-family:monospace;font-size:10.5px;color:#64748b;cursor:default}
.pulse-dot{width:7px;height:7px;background:#22c55e;border-radius:50%;display:inline-block;animation:pulse 1.2s ease-in-out infinite}
.row-running td{font-weight:500}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.8)}}
@keyframes spin{to{transform:rotate(360deg)}}
</style>

<script>
const CSRF = '{{ csrf_token() }}';
// Base path yang benar (support /mafaza/public/ prefix)
const SCHED_BASE = window.location.pathname.replace(/\/jadwal-scraping.*/, '/jadwal-scraping');
let logPollTimer = null;
let currentLogId = null;
let autoScrollLog = true;
let refreshTimer = null;
let refreshCountdown = 20;

// ── Status polling (setiap 20 detik reload tabel) ──────────────────────────
function startRefreshCountdown() {
  clearInterval(refreshTimer);
  refreshCountdown = 20;
  refreshTimer = setInterval(() => {
    refreshCountdown--;
    const el = document.getElementById('next-refresh');
    if (el) el.textContent = refreshCountdown + 'd';
    if (refreshCountdown <= 0) {
      refreshCountdown = 20;
      pollStatus();
    }
  }, 1000);
}

async function pollStatus() {
  try {
    const rows = await fetch(`${SCHED_BASE}/status`).then(r => r.json());
    let anyRunning = false;
    rows.forEach(r => {
      const row = document.getElementById('row-' + r.id);
      if (!row) return;
      const isRunning = !!r.is_running;
      if (isRunning) anyRunning = true;

      // update class & style
      row.className = 'sched-row' + (isRunning ? ' row-running' : '');
      row.style.background = isRunning ? 'var(--acl,#eff6ff)' : '';
      row.dataset.running = isRunning ? 'true' : 'false';

      // status cell (col 0)
      const statusCell = row.cells[0];
      const st = r.last_result?.status ?? '';
      if (isRunning) {
        statusCell.innerHTML = '<span class="badge-running"><span class="pulse-dot"></span> Running</span>';
      } else if (st === 'selector_broken') {
        statusCell.innerHTML = '<span style="color:#b91c1c;font-size:12px" title="Selector HTML Google Maps berubah"><i class="fas fa-exclamation-triangle"></i> Selector Rusak</span>';
      } else if (st === 'success' && (r.last_result?.processed ?? 0) === 0) {
        statusCell.innerHTML = '<span style="color:#d97706;font-size:12px" title="Tidak ada tempat baru ditemukan"><i class="fas fa-exclamation-circle"></i> Kosong</span>';
      } else if (st === 'success') {
        statusCell.innerHTML = '<span style="color:var(--gn);font-size:12px"><i class="fas fa-check-circle"></i> Selesai</span>';
      } else if (st === 'error') {
        statusCell.innerHTML = '<span style="color:var(--rd);font-size:12px"><i class="fas fa-times-circle"></i> Error</span>';
      } else {
        statusCell.innerHTML = '<span style="color:var(--tx3);font-size:12px"><i class="fas fa-clock"></i> Menunggu</span>';
      }

      // hasil cell (col 6 — setelah tambah kolom Metode)
      const hasilCell = row.cells[6];
      if (isRunning) {
        hasilCell.innerHTML = '<span style="color:var(--ac);font-size:12px">sedang berjalan...</span>';
      } else if (st === 'selector_broken') {
        hasilCell.innerHTML = '<span style="color:#b91c1c;font-size:11px">DOM berubah</span>';
      } else if (st === 'success' && (r.last_result?.processed ?? 0) === 0) {
        hasilCell.innerHTML = '<span style="color:#d97706">0 tempat baru</span>';
      } else if (st === 'success') {
        hasilCell.innerHTML = `<span style="color:var(--gn)">${r.last_result.processed ?? 0} tempat</span>`;
      } else if (st === 'error') {
        hasilCell.innerHTML = '<span style="color:var(--rd)">Gagal</span>';
      } else {
        hasilCell.innerHTML = '<span style="color:var(--tx3)">—</span>';
      }

      // terakhir jalan cell (col 5)
      if (r.last_run_at) {
        row.cells[5].innerHTML = `<span style="font-size:12px;color:var(--tx2)">${timeAgo(r.last_run_at)}</span>`;
      }
    });

    // status bar
    const bar = document.getElementById('status-bar');
    if (anyRunning) {
      const running = rows.filter(r => r.is_running);
      bar.style.display = 'flex';
      document.getElementById('status-bar-text').textContent =
        `Scraper sedang berjalan: ${running.map(r => r.name).join(', ')}`;
    } else {
      bar.style.display = 'none';
    }
  } catch(e) {}
}

function timeAgo(isoStr) {
  const diff = Math.floor((Date.now() - new Date(isoStr)) / 1000);
  if (diff < 60)  return diff + ' detik lalu';
  if (diff < 3600) return Math.floor(diff/60) + ' menit lalu';
  if (diff < 86400) return Math.floor(diff/3600) + ' jam lalu';
  return Math.floor(diff/86400) + ' hari lalu';
}

// ── Log viewer ──────────────────────────────────────────────────────────────
function openLog(id, name) {
  currentLogId = id;
  document.getElementById('log-title').textContent = 'Log: ' + name;
  document.getElementById('log-content').textContent = 'Memuat log...';
  document.getElementById('log-running-badge').style.display = 'none';
  document.getElementById('log-processed').textContent = '';
  document.getElementById('log-modal-bg').style.display = 'flex';
  autoScrollLog = true;
  fetchLog();
  logPollTimer = setInterval(fetchLog, 3000);
}

async function fetchLog() {
  if (!currentLogId) return;
  try {
    const data = await fetch(`${SCHED_BASE}/${currentLogId}/log`).then(r => r.json());
    const el = document.getElementById('log-content');
    const badge = document.getElementById('log-running-badge');
    const proc  = document.getElementById('log-processed');
    const footer = document.getElementById('log-footer');

    el.textContent = data.content || '(log kosong)';
    badge.style.display = data.running ? 'inline-flex' : 'none';
    proc.textContent = data.processed ? `${data.processed} tempat ditemukan` : '';
    footer.textContent = data.running
      ? 'Log diperbarui otomatis setiap 3 detik selama scraper berjalan'
      : 'Scraper selesai. Log tidak diperbarui lagi.';

    if (autoScrollLog) el.scrollTop = el.scrollHeight;

    if (!data.running) {
      clearInterval(logPollTimer);
      logPollTimer = null;
    }
  } catch(e) {}
}

function scrollToBottom() {
  const el = document.getElementById('log-content');
  el.scrollTop = el.scrollHeight;
  autoScrollLog = true;
}

document.getElementById('log-content').addEventListener('scroll', function() {
  const el = this;
  autoScrollLog = el.scrollTop + el.clientHeight >= el.scrollHeight - 40;
});

function closeLog() {
  document.getElementById('log-modal-bg').style.display = 'none';
  clearInterval(logPollTimer);
  logPollTimer = null;
  currentLogId = null;
}

// ── Modal Tambah/Edit ───────────────────────────────────────────────────────
function openAddModal() {
  document.getElementById('edit-id').value = '';
  document.getElementById('modal-title').textContent = 'Tambah Jadwal';
  document.getElementById('sched-form').reset();
  document.getElementById('f-run-hour').value = 8;
  onFreqChange();
  document.getElementById('modal-bg').style.display = 'flex';
}

function editSchedule(id, data) {
  document.getElementById('edit-id').value = id;
  document.getElementById('modal-title').textContent = 'Edit Jadwal';
  document.getElementById('f-name').value = data.name || '';
  document.getElementById('f-query').value = data.query || '';
  document.getElementById('f-area').value = data.area || '';
  document.getElementById('f-limit').value = data.limit || 20;
  document.getElementById('f-frequency').value = data.frequency || 'daily';
  document.getElementById('f-interval-hours').value = data.interval_hours || 6;
  document.getElementById('f-run-hour').value = data.run_hour ?? 8;
  document.getElementById('f-day-of-week').value = data.day_of_week || 1;
  onFreqChange();
  document.getElementById('modal-bg').style.display = 'flex';
}

function closeModal() {
  document.getElementById('modal-bg').style.display = 'none';
}

function onFreqChange() {
  const freq = document.getElementById('f-frequency').value;
  document.getElementById('freq-hours').style.display     = freq === 'every_n_hours' ? 'block' : 'none';
  document.getElementById('freq-hour-row').style.display  = freq === 'every_n_hours' ? 'none'  : 'block';
  document.getElementById('freq-day').style.display       = freq === 'weekly'        ? 'block' : 'none';
}

async function submitForm(e) {
  e.preventDefault();
  const id   = document.getElementById('edit-id').value;
  const freq = document.getElementById('f-frequency').value;
  const body = {
    _token: CSRF,
    name:           document.getElementById('f-name').value,
    query:          document.getElementById('f-query').value,
    area:           document.getElementById('f-area').value,
    limit:          document.getElementById('f-limit').value,
    frequency:      freq,
    interval_hours: freq === 'every_n_hours' ? document.getElementById('f-interval-hours').value : null,
    run_hour:       freq !== 'every_n_hours' ? document.getElementById('f-run-hour').value : 0,
    day_of_week:    freq === 'weekly' ? document.getElementById('f-day-of-week').value : null,
  };
  const r = await fetch(id ? `${SCHED_BASE}/${id}` : SCHED_BASE, {
    method:  id ? 'PUT' : 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
    body:    JSON.stringify(body),
  }).then(r => r.json());
  if (r.status === 'ok') { showToast('Jadwal disimpan.', true); closeModal(); setTimeout(() => location.reload(), 500); }
  else showToast('Gagal menyimpan.', false);
}

async function deleteSchedule(id, name) {
  if (!confirm(`Hapus jadwal "${name}"?`)) return;
  const r = await fetch(`${SCHED_BASE}/${id}`, {
    method:  'DELETE',
    headers: {'X-CSRF-TOKEN': CSRF},
  }).then(r => r.json());
  if (r.status === 'ok') { document.getElementById(`row-${id}`)?.remove(); showToast('Jadwal dihapus.', true); }
}

async function toggleSchedule(id, cb) {
  const r = await fetch(`${SCHED_BASE}/${id}/toggle`, {
    method:  'POST',
    headers: {'X-CSRF-TOKEN': CSRF},
  }).then(r => r.json());
  if (r.status !== 'ok') cb.checked = !cb.checked;
}

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = ok ? '#16a34a' : '#dc2626';
  t.style.display = 'block';
  setTimeout(() => t.style.display = 'none', 3000);
}

// Init
pollStatus();
startRefreshCountdown();
</script>
@endsection
