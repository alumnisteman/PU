#!/bin/bash

echo "==================================================="
echo "  SISMAP AUTOMATION DEPLOYMENT v1.3 (LINUX)"
echo "==================================================="
echo ""

# Masuk ke direktori script ini berada
cd "$(dirname "$0")"

echo "[1/5] Mengambil update terbaru dari GitHub..."
git fetch origin main
git reset --hard origin/main

echo ""
echo "[2/5] Sinkronisasi Database (Migrations)..."
php artisan migrate --force

echo ""
echo "[3/5] Membersihkan Cache System..."
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

echo ""
echo "[4/5] Memastikan Izin Folder Storage..."
chmod -R 777 storage bootstrap/cache

echo ""
echo "==================================================="
echo "  UPDATE SELESAI! SISMAP SIAP DIGUNAKAN."
echo "==================================================="
