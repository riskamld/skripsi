@extends('layouts.app')
@section('title', 'Jadwal Scraping')
@section('page-title', 'Jadwal Scraping')

@push('topbar-actions')
<button onclick="openAddModal()" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Jadwal</button>
@endpush

@section('content')

<div class="card" style="margin-bottom:16px">
  <div class="card-header">
    <span><i class="fas fa-clock"></i> Jadwal Aktif</span>
    <span style="font-size:11.5px;color:var(--tx2)">Scraping otomatis berjalan via cron scheduler</span>
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
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Nama</th>
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Query / Area</th>
          <th style="padding:9px 14px;text-align:center;font-weight:600;border-bottom:1px solid var(--bdr)">Limit</th>
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Frekuensi</th>
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Terakhir Jalan</th>
          <th style="padding:9px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--bdr)">Hasil</th>
          <th style="padding:9px 14px;text-align:center;font-weight:600;border-bottom:1px solid var(--bdr)">Aktif</th>
          <th style="padding:9px 14px;border-bottom:1px solid var(--bdr)"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($schedules as $s)
        @php
          $res = $s->last_result;
          $resOk = $res && ($res['status'] ?? '') === 'success';
        @endphp
        <tr id="row-{{ $s->id }}" style="border-bottom:1px solid var(--bdr)">
          <td style="padding:10px 14px;font-weight:600">{{ $s->name }}</td>
          <td style="padding:10px 14px">
            <div>{{ $s->query }}</div>
            @if($s->area)<div style="font-size:11px;color:var(--tx2)">{{ $s->area }}</div>@endif
          </td>
          <td style="padding:10px 14px;text-align:center">{{ $s->limit }}</td>
          <td style="padding:10px 14px;font-size:12px">{{ $s->frequencyLabel() }}</td>
          <td style="padding:10px 14px;font-size:12px;color:var(--tx2)">
            {{ $s->last_run_at ? $s->last_run_at->diffForHumans() : '—' }}
          </td>
          <td style="padding:10px 14px;font-size:12px">
            @if($res)
              @if($resOk)
                <span style="color:var(--gn)"><i class="fas fa-check-circle"></i> {{ $res['processed'] ?? 0 }} tempat</span>
              @else
                <span style="color:var(--rd)"><i class="fas fa-times-circle"></i> Error</span>
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
            <button onclick="editSchedule({{ $s->id }}, {{ json_encode($s->toArray()) }})" class="btn btn-xs" title="Edit">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteSchedule({{ $s->id }}, '{{ $s->name }}')" class="btn btn-xs btn-danger" title="Hapus">
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

<div class="card">
  <div class="card-header"><i class="fas fa-terminal"></i> Setup Cron</div>
  <div class="card-body" style="font-size:13px">
    <p style="color:var(--tx2);margin-bottom:10px">Tambahkan baris ini ke crontab server agar jadwal berjalan otomatis:</p>
    <div style="background:#1e293b;color:#e2e8f0;padding:12px 16px;border-radius:6px;font-family:monospace;font-size:12.5px;display:flex;align-items:center;justify-content:space-between;gap:12px">
      <span>* * * * * cd {{ base_path() }} &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</span>
      <button onclick="copyCron()" style="background:#334155;color:#94a3b8;border:none;border-radius:4px;padding:4px 10px;cursor:pointer;font-size:11px;white-space:nowrap" title="Copy">
        <i class="fas fa-copy"></i> Copy
      </button>
    </div>
    <p style="color:var(--tx3);font-size:11.5px;margin-top:8px">Jalankan <code>crontab -e</code> sebagai user <b>fezora</b> dan tambahkan baris di atas.</p>
  </div>
</div>

{{-- Modal Tambah/Edit --}}
<div id="modal-bg" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;display:none;align-items:center;justify-content:center">
  <div style="background:var(--sur);border-radius:10px;width:460px;max-width:95vw;max-height:90vh;overflow-y:auto">
    <div style="padding:14px 16px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between">
      <span style="font-weight:700;font-size:14px" id="modal-title">Tambah Jadwal</span>
      <button onclick="closeModal()" style="background:none;border:none;cursor:pointer;color:var(--tx2);font-size:16px"><i class="fas fa-times"></i></button>
    </div>
    <form id="sched-form" onsubmit="submitForm(event)" style="padding:16px;display:flex;flex-direction:column;gap:12px">
      <input type="hidden" id="edit-id" value="">
      <div>
        <label class="flabel">Nama Jadwal</label>
        <input type="text" id="f-name" required maxlength="100" placeholder="Contoh: Restoran Surabaya Harian" class="finput">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <label class="flabel">Query / Kata Kunci</label>
          <input type="text" id="f-query" required maxlength="100" placeholder="restoran buah" class="finput">
        </div>
        <div>
          <label class="flabel">Area (opsional)</label>
          <input type="text" id="f-area" maxlength="100" placeholder="Surabaya" class="finput">
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
          <option value="1">Senin</option>
          <option value="2">Selasa</option>
          <option value="3">Rabu</option>
          <option value="4">Kamis</option>
          <option value="5">Jumat</option>
          <option value="6">Sabtu</option>
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
</style>

<script>
const CSRF = '{{ csrf_token() }}';

function openAddModal() {
  document.getElementById('edit-id').value = '';
  document.getElementById('modal-title').textContent = 'Tambah Jadwal';
  document.getElementById('sched-form').reset();
  document.getElementById('f-run-hour').value = 8;
  onFreqChange();
  showModal();
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
  showModal();
}

function showModal() {
  const m = document.getElementById('modal-bg');
  m.style.display = 'flex';
}

function closeModal() {
  document.getElementById('modal-bg').style.display = 'none';
}

function onFreqChange() {
  const freq = document.getElementById('f-frequency').value;
  document.getElementById('freq-hours').style.display = freq === 'every_n_hours' ? 'block' : 'none';
  document.getElementById('freq-hour-row').style.display = freq === 'every_n_hours' ? 'none' : 'block';
  document.getElementById('freq-day').style.display = freq === 'weekly' ? 'block' : 'none';
}

async function submitForm(e) {
  e.preventDefault();
  const id = document.getElementById('edit-id').value;
  const freq = document.getElementById('f-frequency').value;
  const body = {
    _token: CSRF,
    name: document.getElementById('f-name').value,
    query: document.getElementById('f-query').value,
    area: document.getElementById('f-area').value,
    limit: document.getElementById('f-limit').value,
    frequency: freq,
    interval_hours: freq === 'every_n_hours' ? document.getElementById('f-interval-hours').value : null,
    run_hour: freq !== 'every_n_hours' ? document.getElementById('f-run-hour').value : 0,
    day_of_week: freq === 'weekly' ? document.getElementById('f-day-of-week').value : null,
  };

  const url = id
    ? `/jadwal-scraping/${id}`
    : '/jadwal-scraping';
  const method = id ? 'PUT' : 'POST';

  const r = await fetch(url, {
    method,
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
    body: JSON.stringify(body),
  }).then(r=>r.json());

  if (r.status === 'ok') {
    showToast('Jadwal disimpan.', true);
    closeModal();
    setTimeout(() => location.reload(), 500);
  } else {
    showToast('Gagal menyimpan.', false);
  }
}

async function deleteSchedule(id, name) {
  if (!confirm(`Hapus jadwal "${name}"?`)) return;
  const r = await fetch(`/jadwal-scraping/${id}`, {
    method: 'DELETE',
    headers: {'X-CSRF-TOKEN':CSRF},
  }).then(r=>r.json());
  if (r.status === 'ok') {
    document.getElementById(`row-${id}`)?.remove();
    showToast('Jadwal dihapus.', true);
  }
}

async function toggleSchedule(id, cb) {
  const r = await fetch(`/jadwal-scraping/${id}/toggle`, {
    method: 'POST',
    headers: {'X-CSRF-TOKEN':CSRF},
  }).then(r=>r.json());
  if (r.status !== 'ok') cb.checked = !cb.checked;
}

function copyCron() {
  const txt = `* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1`;
  navigator.clipboard.writeText(txt).then(() => showToast('Disalin!', true));
}

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = ok ? '#16a34a' : '#dc2626';
  t.style.display = 'block';
  setTimeout(() => t.style.display = 'none', 3000);
}
</script>
@endsection
