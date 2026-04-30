@echo off
setlocal enabledelayedexpansion

echo ===================================================
echo   SISMAP AUTOMATION DEPLOYMENT v1.3
echo ===================================================
echo.

cd /d %~dp0

:: --- DETEKSI PHP ---
set PHP_BIN=php
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [!] PHP tidak ditemukan di PATH. Mencoba mencari manual...
    if exist "C:\php\php.exe" set PHP_BIN=C:\php\php.exe
    if exist "D:\xampp\php\php.exe" set PHP_BIN=D:\xampp\php\php.exe
    if exist "C:\xampp\php\php.exe" set PHP_BIN=C:\xampp\php\php.exe
    
    if "!PHP_BIN!"=="php" (
        echo [X] ERROR: PHP tetap tidak ditemukan. Mohon hubungi developer.
        pause
        exit /b
    )
)

echo [+] Menggunakan PHP: !PHP_BIN!

echo.
echo [1/5] Mengambil update terbaru dari GitHub...
git fetch origin main
git reset --hard origin/main

echo.
echo [2/5] Sinkronisasi Database (Migrations)...
!PHP_BIN! artisan migrate --force

echo.
echo [3/5] Membersihkan Cache System...
!PHP_BIN! artisan view:clear
!PHP_BIN! artisan route:clear
!PHP_BIN! artisan config:clear
!PHP_BIN! artisan cache:clear

echo.
echo [4/5] Memastikan Autoload...
:: Composer seringkali tidak ada di server produksi, kita gunakan dump-autoload jika ada
where composer >nul 2>nul
if %errorlevel% == 0 (
    echo [+] Menjalankan Composer Dump...
    composer dump-autoload -o
)

echo.
echo [5/5] Memastikan Izin Folder Storage...
icacls "storage" /grant Everyone:(OI)(CI)F /T >nul
icacls "bootstrap/cache" /grant Everyone:(OI)(CI)F /T >nul

echo.
echo ===================================================
echo   UPDATE SELESAI! SISMAP SIAP DIGUNAKAN.
echo ===================================================
pause
