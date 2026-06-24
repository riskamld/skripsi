# Tutorial Menjalankan Program — Mafaza Fortuna

Panduan menjalankan aplikasi ini secara lokal di XAMPP (Windows).

## Prasyarat

- PHP 8.2+ (disarankan 8.4, sesuai yang terpasang di XAMPP)
- Composer
- Node.js + npm (untuk build asset & scraper)
- MySQL (via XAMPP, database bernama `skripsi`)
- Git Bash / WSL jika ingin menjalankan script `.sh`

## 1. Clone & Install Dependency

```bash
git clone git@github.com:riskamld/skripsi.git
cd skripsi

composer install
npm install
cd scraper && npm install && cd ..
```

## 2. Konfigurasi Environment

```bash
cp .env.example .env   # lewati jika .env sudah ada
php artisan key:generate
```

Pastikan `.env` berisi:

```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skripsi
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `skripsi` di phpMyAdmin/MySQL sebelum lanjut ke langkah migrasi.

## 3. Migrasi Database

```bash
php artisan migrate
```

## 4. Build Asset Frontend

```bash
npm run build
```

Untuk mode development dengan hot-reload, gunakan `npm run dev` di terminal terpisah.

## 5. Jalankan Server Laravel

```bash
php artisan serve
```

Akses aplikasi di `http://localhost:8000`.

Alternatif: taruh project di `C:\xampp\htdocs\skripsi` dan akses via Apache XAMPP di `http://localhost/skripsi/public`.

## 6. Cookie Google Maps (untuk Scraper)

Scraper butuh cookie akun Google yang sudah login agar tidak diblokir. Simpan dalam format JSON di `scraper/google-cookies.json`. Validasi dengan:

```bash
cd scraper
node check-cookies.js
```

## 7. Menjalankan Scraper Manual

```bash
cd scraper
node gmaps-scraper.js      # scrape tempat baru
node gmaps-rescraper.js    # update data tempat yang sudah ada
```

## 8. Jadwal Otomatis (Cron / Task Scheduler)

Scheduler Laravel perlu dijalankan setiap menit:

```bash
php artisan schedule:run
```

Di server Linux, daftarkan sebagai cron job:

```
* * * * * /usr/bin/php /path/to/skripsi/artisan schedule:run >> /path/to/storage/logs/scheduler.log 2>&1
```

## Cara Cepat (All-in-One)

Gunakan script otomatis `run-and-push.sh` (lihat file di root project) untuk install dependency, migrate, build asset, lalu commit & push ke GitHub dalam satu langkah:

```bash
bash run-and-push.sh "pesan commit"
```

## Troubleshooting

- **Error koneksi database**: pastikan service MySQL XAMPP aktif dan database `skripsi` sudah dibuat.
- **Asset tidak muncul (CSS/JS kosong)**: jalankan ulang `npm run build`.
- **Scraper diblokir Google**: perbarui `scraper/google-cookies.json` dengan cookie akun yang masih aktif.
