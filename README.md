# 🗺️ SISMAP Modern - Infrastructure Control Center (ICC)
**Dinas Pekerjaan Umum & Penataan Ruang (PUPR) Maluku Utara**

SISMAP Modern adalah platform terpadu berbasis Web GIS yang telah ditingkatkan untuk manajemen aset infrastruktur berskala besar. Sistem ini kini mendukung integrasi real-time dengan SISMAP PULSE, analisis kesehatan jalan (Health Index), dan pelaporan otomatis.

---

## ✨ Fitur Unggulan (Modern Features)
1. **Peta Real-time Terpadu:** Visualisasi 4.000+ aset jalan dengan teknologi *Marker Clustering*.
2. **Live Data Linking:** Terhubung langsung ke database SISMAP PULSE tanpa perlu sinkronisasi manual.
3. **Road Health Index:** Penilaian otomatis kondisi jalan (0-100) berbasis data spasial.
4. **Professional Reporting:** Generate laporan PDF resmi siap cetak dalam satu klik.
5. **Progressive Web App (PWA):** Dapat diinstal di HP (Android/iOS) seperti aplikasi asli.
6. **Street View Hybrid:** Integrasi Mapillary dan Google Street View untuk verifikasi visual lapangan.

---

## 🚀 Panduan Sinkronisasi (Git Workflow)
Sistem ini menggunakan **Segitiga Emas Sinkronisasi** (Lokal ↔ GitHub ↔ Server) untuk menjaga integritas data.

### 1. Update dari Komputer Lokal ke GitHub
Setiap kali ada perubahan kode di komputer lokal, jalankan:
```bash
git add .
git commit -m "Deskripsi perubahan Anda"
git push origin main
```

### 2. Update Server dari GitHub
Masuk ke terminal server dan tarik kode terbaru:
```bash
cd /var/www/sismap
git pull origin main
php artisan view:clear
```

---

## 📱 Panduan Instalasi di HP (PWA)
Anda dapat membawa Dashboard ini ke lapangan dengan menginstalnya di ponsel:
1. Buka browser (Chrome di Android / Safari di iOS) di HP Anda.
2. Akses alamat Dashboard Admin Portal.
3. Klik **Menu Browser** (titik tiga atau ikon berbagi).
4. Pilih **"Install App"** atau **"Add to Home Screen"**.
5. Ikon SISMAP akan muncul di layar utama ponsel Anda.

---

## 🤖 Automasi & Maintenance (Pemeliharaan)

### 1. Robot Sinkronisasi Otomatis (Cron Job)
Sistem kini memiliki "Robot Pelacak" yang otomatis mendaftarkan jalan baru dari Pulse ke Admin setiap 5 menit.
*   **Manual Run:** `php artisan sismap:sync-roads`
*   **Log Check:** Lihat di `storage/logs/laravel.log` untuk aktivitas robot.

### 2. Penanganan Error 500
Sistem telah dilengkapi perisai *Try-Catch*. Jika terjadi Error 500 saat refresh:
1. Jalankan `php artisan optimize:clear` di server.
2. Pastikan koneksi database ke `pu_halsel` atau `sismap` tetap aktif.
3. Cek log dengan `tail -f storage/logs/laravel.log`.

### 3. Pembersihan Berkala
Hapus file *scratch* atau sementara secara rutin:
```bash
rm -rf scratch/*.php
php artisan view:clear
```

---

## 📊 Cara Menggunakan Laporan (Reporting)
1. Buka **Dashboard Admin**.
2. Klik tombol **"Generate Report"** (Berwarna Hijau Emerald).
3. Browser akan membuka jendela cetak. Pilih **"Save as PDF"** atau pilih Printer Anda.
4. Laporan akan tersusun rapi secara otomatis (hanya menampilkan data esensial).

---
**Tim Pengembang Modernisasi** - Hak Cipta © PUPR Maluku Utara
