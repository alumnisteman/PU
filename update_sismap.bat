@echo off
echo ===================================================
echo   SISMAP AUTOMATION DEPLOYMENT & SYNC (v1.2)
echo ===================================================
echo.

cd /d %~dp0

echo [1/5] Mengambil update terbaru dari GitHub...
git fetch origin main
git reset --hard origin/main

echo.
echo [2/5] Sinkronisasi Database (Migrations)...
php artisan migrate --force

echo.
echo [3/5] Membersihkan Cache System & View...
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

echo.
echo [4/5] Mengoptimalkan Autoload...
composer dump-autoload -o

echo.
echo [5/5] Memastikan Izin Folder Storage...
icacls "storage" /grant Everyone:(OI)(CI)F /T >nul
icacls "bootstrap/cache" /grant Everyone:(OI)(CI)F /T >nul

echo.
echo ===================================================
echo   UPDATE SELESAI! SISMAP SIAP DIGUNAKAN.
echo ===================================================
pause
