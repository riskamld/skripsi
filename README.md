# Mafaza Fortuna

Sistem scraping Google Maps otomatis untuk akuisisi data bisnis lokal. Mengumpulkan data tempat usaha (nama, alamat, nomor telepon, rating, kategori) dari Google Maps berdasarkan kata kunci dan area pencarian — lalu menyajikannya dalam dashboard dengan peta interaktif dan analisis pasar.

## Fitur Utama

- **Scraping Google Maps** — kumpulkan data bisnis berdasarkan keyword & lokasi
- **Rescraper** — perbarui data tempat yang sudah ada (rating, jam buka, kontak terbaru)
- **Peta interaktif** — visualisasi semua tempat hasil scrape dengan marker kategori
- **Filter & pencarian** — filter berdasarkan kategori, rating, kota
- **Market analysis** — analisis distribusi bisnis per kategori dan area
- **API token** — akses data via REST API dengan autentikasi token
- **Log scraping** — riwayat setiap sesi scraping dengan status dan jumlah data

## Tech Stack

- **Backend**: Laravel 11, PHP 8.4
- **Database**: MySQL
- **Scraper**: Node.js (Playwright + Chromium)
- **Map**: Leaflet.js
- **Server**: Apache, VPS Linux

## Struktur Scraper

| File | Fungsi |
|---|---|
| `scraper/gmaps-scraper.js` | Scrape tempat baru dari hasil pencarian Google Maps |
| `scraper/gmaps-rescraper.js` | Update data tempat yang sudah ada di DB |
| `scraper/check-cookies.js` | Validasi cookie Google aktif |

## Instalasi

```bash
git clone https://github.com/risqiRPL/mafaza.git
cd mafaza

composer install
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate

# Install scraper dependencies
cd scraper && npm install && cd ..
```

### Konfigurasi `.env`

```env
APP_URL=https://domain.com/mafaza/public
DB_DATABASE=nama_database
DB_USERNAME=user_db
DB_PASSWORD=password_db
```

### Cron Job

```
* * * * * /usr/bin/php /path/to/mafaza/artisan schedule:run >> /path/to/storage/logs/scheduler.log 2>&1
```

## Cookie Google Maps

Scraper membutuhkan cookie dari akun Google yang sudah login agar tidak diblokir. Simpan cookie dalam format JSON di `scraper/google-cookies.json`.

## Alur Kerja

```
User input keyword + area
  → gmaps-scraper.js (Playwright)
    → buka Google Maps, scroll hasil
    → ambil nama, alamat, telp, rating, kategori
    → kirim ke Laravel via API
      → simpan ke tabel places
        → tampil di dashboard + peta
```

## API

Akses data places via API dengan token:

```
GET /api/places?token=API_TOKEN&category=restoran&city=Jakarta
```

## Lisensi

Proprietary — dikembangkan untuk kebutuhan internal Mafaza Fortuna.
