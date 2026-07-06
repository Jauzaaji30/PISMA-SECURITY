NAMA  : AHMAD JAUZA ANNAJI
NIM   : 101230025
KELAS : TF 23 C

# PISMA Security System

Sistem keamanan dan pelaporan tamu berbasis web untuk membantu pengelolaan kunjungan di kantor, gedung, atau area terbatas.

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.4-38B2AC?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-4.4-FF6384?style=for-the-badge&logo=chart.js&logoColor=white)

---

## Tentang Proyek

PISMA Security adalah aplikasi manajemen tamu yang menampilkan alur lengkap mulai dari pendaftaran tamu, pembuatan QR Code, check-in/check-out, hingga monitoring dan pencatatan aktivitas. Sistem ini cocok untuk lingkungan kantor, sekolah, rumah sakit, atau fasilitas dengan akses terbatas.

## Fitur Utama

- Login multi-role untuk admin dan security
- Pendaftaran tamu dengan data lengkap
- Generate QR Code untuk setiap kunjungan
- Proses check-in dan check-out berbasis QR
- Manajemen blacklist
- Dashboard admin dan security
- Log aktivitas / audit trail
- Export data laporan dalam format CSV

## Teknologi yang Digunakan

- PHP 8.2+
- MySQL / MariaDB
- PDO untuk koneksi database
- Tailwind CSS untuk tampilan UI
- Chart.js untuk visualisasi dashboard
- SweetAlert2 untuk notifikasi interaktif

## Struktur Proyek

```text
pisma/
├── index.php
├── dashboard.php
├── admin_dash.php
├── security_dash.php
├── guest.php
├── logout.php
├── hash_password.php
├── api/
│   ├── auth_api.php
│   ├── guest_api.php
│   ├── security_api.php
│   ├── report_api.php
│   └── config.php
├── assets/
│   ├── css/
│   └── js/
└── sql/
    └── database_init.sql
```

## Instalasi

1. Clone repository:
   ```bash
   git clone https://github.com/Jauzaaji30/PISMA-SECURITY.git
   cd PISMA-SECURITY/pisma
   ```

2. Import database:
   ```bash
   mysql -u root -p < sql/database_init.sql
   ```

3. Konfigurasi database di file:
   ```php
   api/config.php
   ```

4. Jalankan aplikasi melalui XAMPP, Laragon, atau server PHP built-in.

## Login Default

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | rahasia |
| Security | security | rahasia |

> Sebaiknya ubah password default setelah proses instalasi selesai.

## Kontribusi

Jika Anda ingin berkontribusi, silakan fork repository ini, buat branch baru, lalu kirim pull request.

## Lisensi

Proyek ini dibuat untuk kebutuhan pengelolaan keamanan dan kunjungan secara sederhana, modern, dan aman.

---

Dibuat untuk mendukung sistem keamanan dan monitoring kunjungan secara digital.
