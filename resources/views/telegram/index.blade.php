@extends('layouts.app')
@section('title', 'Notifikasi Telegram')
@section('page-title', 'Notifikasi Telegram')

@section('content')
<div style="max-width:780px">

<div class="card" style="margin-bottom:16px">
  <div class="card-header">
    <span><i class="fab fa-telegram" style="color:#229ED9"></i> Konfigurasi Bot Telegram</span>
    <div style="display:flex;gap:6px">
      <button onclick="testSend()" class="btn btn-sm" style="background:#229ED9;color:#fff"><i class="fas fa-paper-plane"></i> Uji Kirim</button>
      <button onclick="saveAll()" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Simpan</button>
    </div>
  </div>
  <div class="card-body">
    <div style="display:grid;gap:14px">
      <div style="display:flex;align-items:center;gap:10px">
        <label style="font-size:13px;font-weight:600;width:130px;flex-shrink:0">Aktifkan Notifikasi</label>
        <label class="toggle-wrap">
          <input type="checkbox" id="enabled" {{ $cfg->enabled ? 'checked' : '' }}>
          <span class="toggle-slider"></span>
        </label>
        <span id="enabled-label" style="font-size:12px;color:var(--tx2)">{{ $cfg->enabled ? 'Aktif' : 'Nonaktif' }}</span>
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Bot Token</label>
        <input type="password" id="bot_token" value="{{ $cfg->bot_token }}" placeholder="1234567890:ABCdefGHIjklMNOpqrSTUvwxYZ"
          style="width:100%;padding:8px 10px;border:1px solid var(--bdr);border-radius:6px;font-size:13px;font-family:monospace">
        <div style="font-size:11px;color:var(--tx3);margin-top:3px">Dari <b>@BotFather</b> di Telegram</div>
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--tx2);display:block;margin-bottom:4px">Chat ID</label>
        <input type="text" id="chat_id" value="{{ $cfg->chat_id }}" placeholder="-100123456789 atau 123456789"
          style="width:100%;padding:8px 10px;border:1px solid var(--bdr);border-radius:6px;font-size:13px;font-family:monospace">
        <div style="font-size:11px;color:var(--tx3);margin-top:3px">ID pribadi atau group. Bisa cek via <b>@userinfobot</b></div>
      </div>
    </div>
  </div>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="card-header">
    <span><i class="fas fa-bell"></i> Jenis Notifikasi</span>
  </div>
  <div class="card-body" style="padding:8px 0">
    @php
    $notifs = [
      ['notif_scrape_done',    'fas fa-robot',       '#2563eb', 'Scraping Selesai',       'Dikirim saat satu sesi scraping selesai'],
      ['notif_scraper_error',  'fas fa-exclamation-triangle','#dc2626','Scraper Error',   'Dikirim saat scraper gagal / crash'],
      ['notif_wa_checked',     'fas fa-mobile-alt',  '#16a34a', 'Cek WA Selesai',         'Hasil setelah batch pengecekan WA selesai'],
      ['notif_outreach_sent',  'fab fa-whatsapp',    '#16a34a', 'Pesan WA Terkirim',      'Konfirmasi saat batch outreach selesai'],
      ['notif_daily_limit',    'fas fa-ban',         '#ea580c', 'Limit Harian Tercapai',  'Peringatan saat 50 pesan/hari sudah habis'],
      ['notif_interested',     'fas fa-star',        '#d97706', 'Ada yang Tertarik',      'Notif instan saat status berubah ke "Tertarik"'],
      ['notif_new_order',      'fas fa-shopping-cart','#7c3aed','Order Baru Masuk',       'Saat order baru dicatat di halaman detail'],
      ['notif_duplicates',     'fas fa-copy',        '#6b7280', 'Duplikat Terdeteksi',    'Saat cek duplikat menemukan nomor ganda'],
      ['notif_daily_summary',  'fas fa-chart-bar',   '#0891b2', 'Ringkasan Harian',       'Laporan statistik otomatis setiap hari'],
    ];
    @endphp
    @foreach($notifs as [$key, $icon, $color, $label, $desc])
    <div style="display:flex;align-items:center;padding:10px 16px;gap:12px;border-bottom:1px solid var(--bdr)">
      <div style="width:30px;height:30px;border-radius:7px;background:{{ $color }}18;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="{{ $icon }}" style="color:{{ $color }};font-size:12px"></i>
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600">{{ $label }}</div>
        <div style="font-size:11.5px;color:var(--tx2)">{{ $desc }}</div>
      </div>
      <label class="toggle-wrap">
        <input type="checkbox" id="{{ $key }}" {{ $cfg->$key ? 'checked' : '' }}>
        <span class="toggle-slider"></span>
      </label>
    </div>
    @endforeach
    <div style="display:flex;align-items:center;padding:12px 16px;gap:12px">
      <div style="width:30px;height:30px;border-radius:7px;background:#0891b218;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-clock" style="color:#0891b2;font-size:12px"></i>
      </div>
      <div style="flex:1">
        <div style="font-size:13px;font-weight:600">Jam Ringkasan Harian</div>
        <div style="font-size:11.5px;color:var(--tx2)">Waktu pengiriman ringkasan otomatis</div>
      </div>
      <input type="time" id="daily_summary_time" value="{{ $cfg->daily_summary_time ?? '07:00' }}"
        style="padding:5px 8px;border:1px solid var(--bdr);border-radius:6px;font-size:13px;width:100px">
    </div>
  </div>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="card-header" onclick="this.nextElementSibling.classList.toggle('hidden')" style="cursor:pointer">
    <span><i class="fas fa-question-circle"></i> Cara Setup Bot Telegram</span>
    <i class="fas fa-chevron-down" style="color:var(--tx3)"></i>
  </div>
  <div class="card-body hidden" style="font-size:13px;line-height:1.8">
    <ol style="padding-left:18px;display:flex;flex-direction:column;gap:6px">
      <li>Buka Telegram, cari <b>@BotFather</b></li>
      <li>Kirim perintah <code>/newbot</code>, ikuti instruksi, beri nama dan username bot</li>
      <li>BotFather akan memberikan <b>Bot Token</b> — copy dan paste di atas</li>
      <li>Untuk Chat ID pribadi: chat dengan <b>@userinfobot</b>, langsung kirim pesan, bot akan balas dengan ID kamu</li>
      <li>Untuk group: tambahkan bot ke group, lalu forward pesan dari group ke @userinfobot untuk dapat Group ID (biasanya negatif, contoh: <code>-100123456789</code>)</li>
      <li>Pastikan bot sudah di-<b>start</b> (kirim /start ke bot) sebelum uji kirim</li>
    </ol>
  </div>
</div>

<div id="toast" style="display:none;position:fixed;bottom:20px;right:20px;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;z-index:999"></div>

<style>
.toggle-wrap{position:relative;display:inline-block;width:38px;height:21px;flex-shrink:0}
.toggle-wrap input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;inset:0;background:#d1d5db;border-radius:21px;cursor:pointer;transition:.2s}
.toggle-slider:before{content:'';position:absolute;width:15px;height:15px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s}
.toggle-wrap input:checked+.toggle-slider{background:var(--ac)}
.toggle-wrap input:checked+.toggle-slider:before{transform:translateX(17px)}
.hidden{display:none}
.btn{padding:6px 12px;border:1px solid var(--bdr);border-radius:6px;font-size:12.5px;font-weight:600;cursor:pointer;background:var(--sur);color:var(--tx)}
.btn-primary{background:var(--ac);color:#fff;border-color:var(--ac)}
.btn-sm{padding:5px 10px;font-size:12px}
</style>

<script>
document.getElementById('enabled').addEventListener('change', function() {
  document.getElementById('enabled-label').textContent = this.checked ? 'Aktif' : 'Nonaktif';
});

async function saveAll() {
  const fd = new FormData();
  fd.append('_token', '{{ csrf_token() }}');
  fd.append('bot_token', document.getElementById('bot_token').value);
  fd.append('chat_id', document.getElementById('chat_id').value);
  fd.append('daily_summary_time', document.getElementById('daily_summary_time').value);
  if (document.getElementById('enabled').checked) fd.append('enabled', '1');
  ['notif_scrape_done','notif_scraper_error','notif_wa_checked','notif_outreach_sent',
   'notif_daily_limit','notif_daily_summary','notif_interested','notif_new_order','notif_duplicates'
  ].forEach(k => { if (document.getElementById(k).checked) fd.append(k, '1'); });

  const r = await fetch('{{ route("telegram.save") }}', {method:'POST', body:fd}).then(r=>r.json());
  showToast(r.status==='ok' ? 'Pengaturan disimpan.' : 'Gagal menyimpan.', r.status==='ok');
}

async function testSend() {
  const r = await fetch('{{ route("telegram.test") }}', {
    method: 'POST',
    headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},
    body: JSON.stringify({}),
  }).then(r=>r.json());
  showToast(r.message, r.status==='ok');
}

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = ok ? '#16a34a' : '#dc2626';
  t.style.color = '#fff';
  t.style.display = 'block';
  setTimeout(() => t.style.display = 'none', 3500);
}
</script>
@endsection
