# 🗺️ SISMAP (Sistem Informasi Manajemen Aset Peta)
**Dinas Pekerjaan Umum & Penataan Ruang (PUPR)**

SISMAP adalah platform terpadu berbasis Web GIS untuk manajemen aset jalan dan jembatan. Sistem ini mendukung sinkronisasi real-time antara panel administrasi dan dashboard visualisasi peta spasial.

---

## 🛠️ Persyaratan Sistem (System Requirements)
Sebelum melakukan instalasi, pastikan server/komputer Anda telah terinstall perangkat lunak berikut:
1. **PHP** versi 8.1 atau lebih baru.
2. **Composer** (untuk manajemen dependensi PHP).
3. **Node.js** (minimal v16) & **NPM** (untuk *build* frontend Vue/Vite).
4. **MySQL** versi 8.0+ atau MariaDB 10.2+ (Wajib mendukung fungsi Spasial/Geometry seperti `ST_Intersects` dan `ST_GeomFromText`).
5. Web Server (Apache/Nginx) jika untuk Production.

---

## 🚀 Panduan Instalasi (Instalation Guide)

### 1. Kloning Repositori
Clone *source code* dari repositori GitHub ke komputer/server lokal:
```bash
git clone https://github.com/alumnisteman/PU.git
cd PU
```

### 2. Instalasi Dependensi Backend (PHP)
Jalankan perintah Composer untuk mengunduh semua paket pihak ketiga Laravel:
```bash
composer install --optimize-autoloader --no-dev
```
*(Catatan: hilangkan `--no-dev` jika menginstall di komputer lokal untuk keperluan development).*

### 3. Instalasi Dependensi Frontend (Node.js)
Jalankan NPM untuk mengunduh paket Vue, Leaflet, dan TailwindCSS:
```bash
npm install
```

### 4. Konfigurasi Environment (`.env`)
Salin file konfigurasi bawaan dan sesuaikan dengan *database* Anda:
```bash
cp .env.example .env
```
Buka file `.env` menggunakan teks editor (Notepad/VSCode/Nano) dan isi bagian database:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sismap     # Ganti dengan nama database Anda
DB_USERNAME=root          # Username database
DB_PASSWORD=rahasia       # Password database
```

### 5. Generate Application Key & Migrasi
Generate kunci keamanan Laravel dan buat struktur tabel database (pastikan database kosong yang Anda tulis di `.env` sudah dibuat di MySQL):
```bash
php artisan key:generate
php artisan migrate
```
*(Opsional: Jika Anda memiliki *seeder* data awal, jalankan `php artisan db:seed`).*

### 6. Build Frontend Assets (Wajib)
Sistem ini menggunakan Vite. File Vue dan Tailwind harus di-*compile* menjadi Javascript murni sebelum dapat ditampilkan di *browser*:
```bash
npm run build
```

### 7. Jalankan Server
**Untuk Development Lokal:**
```bash
php artisan serve
```
Akses aplikasi melalui browser di `http://localhost:8000`.

**Untuk Production:** Arahkan root folder web server (Apache/Nginx) Anda ke folder `PU/public`.

---

## 🔧 Panduan Maintenance (Pemeliharaan Sistem)

Sistem SISMAP memiliki skrip khusus untuk pengecekan kesehatan server dan *deployment*. Berikut adalah langkah-langkah pemeliharaan rutin jika terjadi *error* atau jika ada update kode baru:

### 1. Membersihkan Cache Server (Jika sistem error/lemot)
Jika aplikasi tiba-tiba memunculkan halaman kosong atau 500 Server Error setelah pembaruan, *cache* yang korup sering menjadi penyebabnya. Jalankan:
```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
```

### 2. Build Ulang Frontend (Jika merubah tampilan)
Jika Anda mengubah teks, warna, komponen `.vue` (seperti `MapView.vue`), atau file CSS, Anda **wajib** melakukan proses *build* ulang agar perubahan terlihat di *browser*:
```bash
npm run build
```

### 3. Mengeksekusi Health-Check (Uji Kesehatan Sistem)
Kami telah menyediakan skrip diagnosa pintar `deploy_check.php` di *root folder*. Skrip ini akan memeriksa koneksi database, syntax error, dan anomali data (misal: jalan yang kondisinya ilegal):
```bash
php deploy_check.php
```
Jika semuanya berstatus `OK`, maka sistem 100% stabil untuk digunakan.

### 4. Menjalankan Server Realtime/Socket (Jika digunakan)
Peta *dashboard* menggunakan *socket* untuk mengupdate warna dan animasi secara instan tanpa perlu me-refresh halaman web. Pastikan server Node.js pembantu berjalan:
```bash
node server.js
```
*(Pastikan port 3000 tidak diblokir oleh Firewall).*

---
**Tim Pengembang** - Hak Cipta © PUPR
