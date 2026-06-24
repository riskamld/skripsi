#!/bin/bash
set -e

echo "=== Mafaza Fortuna: setup & run otomatis ==="

if [ ! -f "artisan" ]; then
    echo "Error: jalankan script ini dari root project Laravel (folder yang berisi 'artisan')"
    exit 1
fi

echo "[1/6] Install dependency PHP (composer)..."
composer install

echo "[2/6] Install dependency Node.js..."
npm install
(cd scraper && npm install)

if [ ! -f ".env" ]; then
    echo "[2b] Membuat .env dari .env.example..."
    cp .env.example .env
    php artisan key:generate
fi

echo "[3/6] Migrasi database..."
php artisan migrate --force

echo "[4/6] Build asset frontend..."
npm run build

echo "[5/6] Commit perubahan ke git..."
COMMIT_MSG="${1:-chore: update project}"
git add -A
if git diff --cached --quiet; then
    echo "Tidak ada perubahan untuk di-commit."
else
    git commit -m "$COMMIT_MSG"
fi

echo "[6/6] Push ke GitHub..."
git push

echo ""
echo "=== Selesai! Jalankan 'php artisan serve' untuk start server lokal ==="
