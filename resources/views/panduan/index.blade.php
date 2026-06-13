@extends('layouts.app')
@section('title', 'Panduan — Mafaza Fortuna')
@section('page-title', 'Panduan Penggunaan')

@push('styles')
<style>
.guide-step{display:flex;gap:16px;margin-bottom:24px;align-items:flex-start}
.guide-num{flex-shrink:0;width:36px;height:36px;border-radius:50%;background:var(--ac);color:#fff;
  display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;margin-top:2px}
.guide-body{flex:1}
.guide-title{font-weight:700;font-size:14px;color:var(--tx);margin-bottom:4px}
.guide-desc{font-size:12.5px;color:var(--tx2);line-height:1.6}
.guide-desc ul{margin:6px 0 0 16px;padding:0}
.guide-desc li{margin-bottom:3px}
.guide-tag{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:4px;
  font-size:11px;font-weight:600;margin-top:6px;margin-right:4px}
.tag-go{background:#dcfce7;color:#15803d}
.tag-loc{background:#eff6ff;color:#1d4ed8}
.tag-wa{background:#d1fae5;color:#065f46}
.tag-warn{background:#fef3c7;color:#92400e}
.flow-arrow{text-align:center;color:var(--tx3);font-size:18px;margin:-8px 0 8px}
.status-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.status-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:500}
.tip-box{background:var(--acl);border-left:3px solid var(--ac);border-radius:0 6px 6px 0;
  padding:10px 14px;font-size:12.5px;color:var(--tx2);margin-top:8px;line-height:1.6}
.tip-box strong{color:var(--ac)}
.section-divider{border:none;border-top:1px solid var(--bdr);margin:28px 0}
.faq-item{margin-bottom:16px}
.faq-q{font-weight:600;font-size:13px;color:var(--tx);margin-bottom:4px}
.faq-q::before{content:"T: ";color:var(--ac)}
.faq-a{font-size:12.5px;color:var(--tx2);line-height:1.6;padding-left:18px}
.faq-a::before{content:"J: ";color:var(--tx3);font-weight:600}
</style>
@endpush

@section('content')

{{-- Header ringkasan --}}
<div class="card mb-20">
    <div class="card-body" style="padding:20px 24px">
        <div class="d-flex align-center gap-12 mb-12">
            <div style="width:44px;height:44px;border-radius:10px;background:var(--acl);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-book-open" style="color:var(--ac);font-size:20px"></i>
            </div>
            <div>
                <div style="font-size:16px;font-weight:700;color:var(--tx)">Mafaza Fortuna — Sistem Prospek Pelanggan</div>
                <div style="font-size:12.5px;color:var(--tx2);margin-top:2px">
                    Aplikasi ini membantu Anda menemukan toko buah, pasar, dan tempat usaha di sekitar area target,
                    lalu menghubungi mereka via WhatsApp sebagai calon pelanggan Mafaza.
                </div>
            </div>
        </div>

        {{-- Alur ringkas --}}
        <div style="display:flex;align-items:center;gap:0;flex-wrap:wrap;margin-top:4px">
            @foreach([
                ['fa-robot','Scraping','#eff6ff','#1d4ed8'],
                ['fa-whatsapp fab','Cek WA','#d1fae5','#065f46'],
                ['fa-paper-plane','Kirim Pesan','#fef3c7','#92400e'],
                ['fa-clock','Pantau','#f3e8ff','#7e22ce'],
                ['fa-handshake','Follow Up','#dcfce7','#15803d'],
            ] as [$icon, $label, $bg, $col])
            <div style="display:flex;align-items:center;gap:0">
                <div style="display:flex;align-items:center;gap:6px;background:{{ $bg }};color:{{ $col }};
                    padding:5px 10px;border-radius:6px;font-size:11.5px;font-weight:600;white-space:nowrap">
                    <i class="{{ str_starts_with($icon,'fa-whatsapp') ? 'fab' : 'fas' }} {{ str_starts_with($icon,'fab') ? '' : $icon }}"
                       class="fas fa-{{ $icon }}"></i>
                    <i class="fas fa-{{ $icon }}"></i> {{ $label }}
                </div>
                @if(!$loop->last)
                <i class="fas fa-chevron-right" style="color:var(--tx3);font-size:11px;margin:0 4px"></i>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row" style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
<div>

{{-- LANGKAH LANGKAH --}}
<div class="card mb-20">
    <div class="card-header"><i class="fas fa-list-ol" style="color:var(--ac)"></i> Langkah-Langkah Kerja Admin</div>
    <div class="card-body" style="padding:20px 24px">

        {{-- Step 1 --}}
        <div class="guide-step">
            <div class="guide-num">1</div>
            <div class="guide-body">
                <div class="guide-title"><i class="fas fa-robot" style="color:var(--ac)"></i> Scraping — Kumpulkan Data Tempat</div>
                <div class="guide-desc">
                    Scraping adalah proses mengumpulkan data toko buah, pasar, dan tempat usaha dari Google Maps secara otomatis.
                    <ul>
                        <li>Buka menu <strong>Scraping</strong> di sidebar</li>
                        <li>Klik titik di peta untuk pilih area target</li>
                        <li>Pilih ukuran area: Kelurahan / Kecamatan / Kota / Kabupaten</li>
                        <li>Ketik kata kunci pencarian, contoh: <em>"toko buah"</em>, <em>"pasar buah"</em></li>
                        <li>Klik <strong>Mulai Scraping</strong> dan tunggu selesai</li>
                    </ul>
                    <span class="guide-tag tag-go"><i class="fas fa-check"></i> Hasil langsung masuk ke Data Tempat</span>
                    <span class="guide-tag tag-warn"><i class="fas fa-clock"></i> Proses 20–50 tempat butuh ±5 menit</span>
                </div>
                <div class="tip-box">
                    <strong>Tips:</strong> Lakukan scraping per kecamatan agar hasilnya spesifik. Gunakan kata kunci berbeda
                    ("toko buah", "buah segar", "pasar") untuk hasil yang lebih banyak.
                </div>
            </div>
        </div>

        <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>

        {{-- Step 2 --}}
        <div class="guide-step">
            <div class="guide-num">2</div>
            <div class="guide-body">
                <div class="guide-title"><i class="fab fa-whatsapp" style="color:#16a34a"></i> Cek WhatsApp — Temukan yang Bisa Dihubungi</div>
                <div class="guide-desc">
                    Setelah scraping, banyak tempat yang punya nomor telepon tapi belum diketahui apakah terdaftar di WhatsApp.
                    Fitur <strong>Rescrape</strong> akan mengecek semua nomor secara otomatis.
                    <ul>
                        <li>Buka menu <strong>Scraping</strong></li>
                        <li>Gulir ke bawah ke bagian <strong>Rescrape Data</strong></li>
                        <li>Atur jumlah tempat yang ingin dicek</li>
                        <li>Klik <strong>Mulai Rescrape</strong> dan tunggu selesai</li>
                    </ul>
                    <span class="guide-tag tag-wa"><i class="fab fa-whatsapp"></i> Punya WA → siap dihubungi</span>
                    <span class="guide-tag" style="background:#fee2e2;color:#991b1b"><i class="fas fa-times"></i> Tidak ada WA → dilewati</span>
                </div>
                <div class="tip-box">
                    <strong>Catatan:</strong> Proses ini berjalan di latar belakang. Bisa ditinggal dan hasilnya tetap tersimpan.
                </div>
            </div>
        </div>

        <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>

        {{-- Step 3 --}}
        <div class="guide-step">
            <div class="guide-num">3</div>
            <div class="guide-body">
                <div class="guide-title"><i class="fas fa-paper-plane" style="color:#d97706"></i> Kirim Pesan — Outreach ke Prospek</div>
                <div class="guide-desc">
                    Saatnya menghubungi toko-toko yang punya WhatsApp. Gunakan filter cepat untuk menemukan antrian.
                    <ul>
                        <li>Buka menu <strong>Data Tempat</strong></li>
                        <li>Klik filter <strong>"Belum Kirim"</strong> — ini daftar yang siap dihubungi</li>
                        <li>Urutkan berdasarkan <strong>Score ↓</strong> untuk prioritaskan yang paling ramai</li>
                        <li>Klik nama tempat → buka halaman detail</li>
                        <li>Klik tombol <strong>Kirim via WhatsApp</strong> → pesan otomatis terbuka</li>
                        <li>Kirim pesan → status berubah jadi <strong>"Sudah Kirim"</strong></li>
                    </ul>
                    <span class="guide-tag tag-warn"><i class="fas fa-bullseye"></i> Prioritaskan yang Score-nya tinggi</span>
                    <span class="guide-tag tag-go"><i class="fas fa-check"></i> Target: 10–20 pesan per hari</span>
                </div>
                <div class="tip-box">
                    <strong>Tips:</strong> Jangan kirim ke terlalu banyak sekaligus. Fokus ke tempat yang ramai (review &gt; 50)
                    dan kategori relevan seperti toko buah, pasar, minimarket buah.
                </div>
            </div>
        </div>

        <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>

        {{-- Step 4 --}}
        <div class="guide-step">
            <div class="guide-num">4</div>
            <div class="guide-body">
                <div class="guide-title"><i class="fas fa-clock" style="color:#7e22ce"></i> Pantau — Cek Respon Harian</div>
                <div class="guide-desc">
                    Setiap hari buka daftar yang sudah dikirim dan tandai siapa yang sudah membalas.
                    <ul>
                        <li>Buka <strong>Data Tempat</strong> → filter <strong>"Sudah Kirim"</strong></li>
                        <li>Cek di HP/WA mana yang sudah balas</li>
                        <li>Di aplikasi: buka detail tempat → ubah status ke <strong>"Ada Respon"</strong></li>
                        <li>Yang belum balas setelah 3 hari → bisa dikirim follow up</li>
                    </ul>
                    <span class="guide-tag tag-loc"><i class="fas fa-redo"></i> Follow up setelah 3 hari tidak balas</span>
                </div>
            </div>
        </div>

        <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>

        {{-- Step 5 --}}
        <div class="guide-step">
            <div class="guide-num" style="background:#16a34a">5</div>
            <div class="guide-body">
                <div class="guide-title"><i class="fas fa-handshake" style="color:#16a34a"></i> Follow Up — Closing Pelanggan</div>
                <div class="guide-desc">
                    Tempat yang sudah balas adalah prospek hangat — prioritas utama.
                    <ul>
                        <li>Buka <strong>Data Tempat</strong> → filter <strong>"Ada Respon"</strong></li>
                        <li>Hubungi lanjut: tanya kebutuhan, tawarkan harga, kirim katalog</li>
                        <li>Kalau deal → catat sebagai pelanggan tetap di luar aplikasi ini</li>
                        <li>Kalau tidak tertarik → ubah status ke <strong>"Tidak Tertarik"</strong> agar tidak dihubungi lagi</li>
                    </ul>
                </div>
                <div class="tip-box">
                    <strong>Kunci closing:</strong> Respons cepat saat mereka balas. Jangan biarkan lebih dari 1 jam.
                </div>
            </div>
        </div>

    </div>
</div>

{{-- FAQ --}}
<div class="card">
    <div class="card-header"><i class="fas fa-question-circle" style="color:var(--ac)"></i> Pertanyaan Umum</div>
    <div class="card-body" style="padding:20px 24px">

        <div class="faq-item">
            <div class="faq-q">Berapa tempat yang bisa discrape sekaligus?</div>
            <div class="faq-a">Maksimal 100 tempat per sesi scraping. Untuk area besar, lakukan beberapa kali dengan kata kunci berbeda.</div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Kenapa ada tempat yang tidak punya koordinat di peta?</div>
            <div class="faq-a">Google Maps terkadang tidak menampilkan koordinat untuk tempat tertentu. Data tetap tersimpan tapi tidak muncul di halaman Peta.</div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Apakah pesan WA dikirim otomatis?</div>
            <div class="faq-a">Tidak sepenuhnya. Admin tetap harus menekan tombol kirim di WhatsApp. Aplikasi hanya membuka WA dengan pesan yang sudah disiapkan.</div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Berapa target pesan per hari yang ideal?</div>
            <div class="faq-a">10–20 pesan per hari sudah cukup. Terlalu banyak sekaligus berisiko akun WA dibatasi oleh WhatsApp.</div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Apa itu Score?</div>
            <div class="faq-a">Score adalah nilai otomatis sistem berdasarkan rating bintang + jumlah ulasan + jam ramai tempat. Semakin tinggi score, semakin ramai tempat itu — artinya lebih potensial sebagai pelanggan aktif.</div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Apakah data akan terhapus jika scraping ulang di area yang sama?</div>
            <div class="faq-a">Tidak. Sistem cek duplikat otomatis. Tempat yang sudah ada akan diperbarui datanya, bukan dibuat baru.</div>
        </div>

    </div>
</div>

</div>

{{-- Sidebar kanan --}}
<div style="display:flex;flex-direction:column;gap:16px">

    {{-- Status referensi --}}
    <div class="card">
        <div class="card-header"><i class="fas fa-info-circle" style="color:var(--ac)"></i> Referensi Status</div>
        <div class="card-body" style="padding:16px">
            <div style="font-size:11px;font-weight:600;color:var(--tx3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:10px">Status WA Tempat</div>
            <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:16px">
                <div class="d-flex align-center gap-8">
                    <span class="badge badge-green"><i class="fab fa-whatsapp"></i></span>
                    <span style="font-size:12px;color:var(--tx2)">Punya WhatsApp</span>
                </div>
                <div class="d-flex align-center gap-8">
                    <span class="badge badge-red" style="font-size:10px">Tidak</span>
                    <span style="font-size:12px;color:var(--tx2)">Tidak ada WA</span>
                </div>
                <div class="d-flex align-center gap-8">
                    <span class="badge badge-gray" style="font-size:10px">?</span>
                    <span style="font-size:12px;color:var(--tx2)">Belum dicek</span>
                </div>
            </div>

            <div style="font-size:11px;font-weight:600;color:var(--tx3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:10px">Status Outreach</div>
            <div style="display:flex;flex-direction:column;gap:7px">
                <div class="d-flex align-center gap-8">
                    <span class="badge badge-gray" style="font-size:10px;min-width:70px;justify-content:center">Belum</span>
                    <span style="font-size:12px;color:var(--tx2)">Belum pernah dihubungi</span>
                </div>
                <div class="d-flex align-center gap-8">
                    <span class="badge badge-yellow" style="font-size:10px;min-width:70px;justify-content:center">Terkirim</span>
                    <span style="font-size:12px;color:var(--tx2)">Pesan sudah dikirim</span>
                </div>
                <div class="d-flex align-center gap-8">
                    <span class="badge badge-green" style="font-size:10px;min-width:70px;justify-content:center">Respon</span>
                    <span style="font-size:12px;color:var(--tx2)">Sudah membalas</span>
                </div>
                <div class="d-flex align-center gap-8">
                    <span class="badge badge-red" style="font-size:10px;min-width:70px;justify-content:center">Tdk Tertarik</span>
                    <span style="font-size:12px;color:var(--tx2)">Menolak / tidak minat</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Rutinitas harian --}}
    <div class="card">
        <div class="card-header"><i class="fas fa-calendar-check" style="color:var(--ac)"></i> Rutinitas Harian Admin</div>
        <div class="card-body" style="padding:16px">
            <div style="display:flex;flex-direction:column;gap:10px">
                @foreach([
                    ['Pagi','08:00 – 09:00','Cek respon dari kemarin (filter Ada Respon)','#fef3c7','#92400e','fa-sun'],
                    ['Pagi','09:00 – 10:30','Kirim 10–20 pesan baru (filter Belum Kirim, sort Score ↓)','#eff6ff','#1d4ed8','fa-paper-plane'],
                    ['Siang','12:00 – 13:00','Follow up yang belum balas lebih dari 3 hari','#f3e8ff','#7e22ce','fa-redo'],
                    ['Sore','16:00 – 17:00','Scraping area baru jika perlu tambah data','#dcfce7','#15803d','fa-robot'],
                ] as [$label, $time, $task, $bg, $col, $icon])
                <div style="background:{{ $bg }};border-radius:6px;padding:9px 11px">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                        <i class="fas {{ $icon }}" style="color:{{ $col }};font-size:11px"></i>
                        <span style="font-size:10px;font-weight:700;color:{{ $col }};text-transform:uppercase;letter-spacing:.4px">{{ $label }}</span>
                        <span style="font-size:10px;color:{{ $col }};opacity:.7;margin-left:auto">{{ $time }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--tx2)">{{ $task }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Shortcut menu --}}
    <div class="card">
        <div class="card-header"><i class="fas fa-bolt" style="color:var(--ac)"></i> Akses Cepat</div>
        <div class="card-body" style="padding:12px;display:flex;flex-direction:column;gap:6px">
            <a href="{{ route('scraper.index') }}" class="btn btn-secondary btn-sm" style="justify-content:flex-start">
                <i class="fas fa-robot"></i> Mulai Scraping
            </a>
            <a href="{{ route('places.index', ['qf'=>'unsent', 'sort'=>'busyness_score', 'direction'=>'desc']) }}" class="btn btn-info btn-sm" style="justify-content:flex-start">
                <i class="fas fa-paper-plane"></i> Antrian Kirim Pesan
            </a>
            <a href="{{ route('places.index', ['qf'=>'replied']) }}" class="btn btn-orange btn-sm" style="justify-content:flex-start">
                <i class="fas fa-reply"></i> Cek Respon
            </a>
            <a href="{{ route('map.index') }}" class="btn btn-secondary btn-sm" style="justify-content:flex-start">
                <i class="fas fa-map-marked-alt"></i> Lihat Peta
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="justify-content:flex-start">
                <i class="fas fa-chart-bar"></i> Dasbor
            </a>
        </div>
    </div>

</div>
</div>

@endsection
