#!/bin/bash
set -e

echo "=== Mafaza Fortuna: setup & run otomatis ==="

if [ ! -f "artisan" ]; then
    echo "Error: jalankan script ini dari root project Laravel (folder yang berisi 'artisan')"
    exit 1
fi

echo "[1/5] Install dependency PHP (composer)..."
composer install

echo "[2/5] Install dependency Node.js..."
npm install
(cd scraper && npm install)

if [ ! -f ".env" ]; then
    echo "[2b] Membuat .env dari .env.example..."
    cp .env.example .env
    php artisan key:generate
fi

echo "[3/5] Migrasi database..."
php artisan migrate --force

echo "[4/5] Build asset frontend..."
npm run build

echo "[5/5] Menjalankan server Laravel di http://127.0.0.1:8000 ..."
echo "(Tekan Ctrl+C untuk berhenti)"
php artisan serve
