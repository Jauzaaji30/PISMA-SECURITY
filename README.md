# PISMA Security System

Sistem Cerdas Pelaporan Tamu Berbasis Web untuk Manajemen Keamanan & Kunjungan Kantor/Gedung.

![PISMA Security](https://img.shields.io/badge/PISMA-Security-blue?style=for-the-badge&logo=shield)
![PHP](https://img.shields.io/badge/PHP-8.2+-8892BF?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.4-38B2AC?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-4.4-FF6384?style=for-the-badge&logo=chart.js&logoColor=white)

---

## 📋 Daftar Isi
- [Tentang Proyek](#-tentang-proyek)
- [Fitur Utama](#-fitur-utama)
- [Tech Stack](#-tech-stack--modul-yang-digunakan)
- [Struktur Proyek](#-struktur-proyek)
- [Database Schema](#-database-schema)
- [Instalasi & Setup](#-instalasi--setup)
- [Konfigurasi](#-konfigurasi)
- [Default Login](#-default-login)
- [API Endpoints](#-api-endpoints)
- [Screenshot & Demo](#-screenshot--demo)
- [Lisensi](#-lisensi)

---

## 🎯 Tentang Proyek

**PISMA Security System** adalah aplikasi web-based untuk manajemen keamanan kunjungan tamu di lingkungan kantor, gedung, atau area terbatas. Sistem ini menyediakan alur kerja lengkap mulai dari registrasi tamu, generate QR Code, check-in/check-out via scan QR, hingga monitoring real-time dan audit trail untuk admin.

### ✨ Highlights
- **Multi-role**: Admin, Security, Guest Portal
- **QR Code Check-in/out**: Scan via kamera atau input manual
- **Blacklist System**: Deteksi otomatis tamu terblokir
- **Real-time Dashboard**: Live stats, charts (Chart.js), audit trail
- **Export CSV**: Laporan data kunjungan
- **Modern UI**: TailwindCSS + Glassmorphism, Dark/Light mode
- **Security**: PDO Prepared Statements, Password Hashing, Session Auth, XSS Protection

---

## 🚀 Fitur Utama

### 🔐 **Authentication & Authorization**
| Fitur | Deskripsi |
|-------|-----------|
| **Multi-role Login** | Admin & Security (role-based access) |
| **Session Management** | PHP Session + Secure Cookie |
| **Password Hashing** | `password_hash()` / `password_verify()` (bcrypt) |
| **User Registration** | Security register akun baru (role default: security) |
| **Activity Logging** | Semua aksi tercatat ke `security_logs` |

### 👥 **Guest Management (Portal Tamu - `guest.php`)**
- Form registrasi tamu lengkap: Nama, No. Identitas (KTP/SIM), HP, Instansi, Kendaraan, Tujuan, Keperluan
- **Auto-generate QR Code** format: `DIG-XXXXXX-XXX`
- Validasi blacklist saat registrasi
- Tampilkan QR Code hasil registrasi (copy/print)

### 🛡️ **Security Dashboard (`security_dash.php`)**
| Panel | Fitur |
|-------|-------|
| **Scan Check-in** | Input/scan QR → Validasi → Check-in tamu (deteksi blacklist otomatis) |
| **Scan Check-out** | Input/scan QR → Proses check-out tamu |
| **Daftar Tamu Hari Ini** | Filter search nama/QR, filter status (waiting/checked-in/completed) |
| **Blacklist Management** | Tambah/hapus blacklist (admin only delete), deteksi otomatis saat check-in |
| **Log Aktivitas** | Real-time log: LOGIN, CHECK_IN, CHECK_OUT, BLACKLIST_ATTEMPT, GUEST_REGISTER |

### 👑 **Admin Dashboard (`admin_dash.php`)**
| Section | Fitur |
|---------|-------|
| **Overview** | 6 Stat Cards (Total Tamu, Check-in/out Hari Ini, Waiting, Blacklist, Total User) |
| **Charts** | • Bar Chart: Tren Kunjungan 7 Hari (Chart.js)<br>• Doughnut Chart: Status Kunjungan Hari Ini |
| **Data Kunjungan** | Tabel lengkap + Pagination + Search (Nama/ID/QR) |
| **Manajemen User** | CRUD User (Tambah, Aktifkan/Nonaktifkan), Role: Admin/Security |
| **Audit Trail** | Log lengkap aktivitas sistem + Export CSV |
| **Export CSV** | Download laporan kunjungan (semua data) |

### 📊 **Database & Logging**
- **Tables**: `users`, `guests`, `visits`, `blacklist`, `security_logs`
- **Foreign Keys**: Relasi antar tabel dengan `ON DELETE CASCADE/SET NULL`
- **Audit Log**: Setiap aksi penting (login, check-in, check-out, register, blacklist) tercatat dengan user_id, ip_address, timestamp

---

## 🛠 Tech Stack & Modul yang Digunakan

### **Backend (PHP 8.2+)**
| Komponen | Versi/Detail | Kegunaan |
|----------|--------------|----------|
| **PHP** | 8.2+ | Runtime utama |
| **PDO MySQL** | Native | Database abstraction, Prepared Statements |
| **Session** | Native PHP | Authentication state management |
| **password_hash/verify** | bcrypt (cost 10) | Secure password storage |

### **Database**
| Komponen | Versi | Kegunaan |
|----------|-------|----------|
| **MySQL/MariaDB** | 8.0+ / 10.5+ | Primary Database |
| **SQL File** | `sql/database_init.sql` | Schema + Seed Data |

### **Frontend (CDN - Zero Build Step)**
| Library | Versi | CDN Source | Kegunaan |
|---------|-------|------------|----------|
| **TailwindCSS** | 3.4+ | `cdn.tailwindcss.com` | Utility-first CSS Framework |
| **Font Awesome** | 6.0+ | `cdnjs.cloudflare.com` | Icon Library |
| **Google Fonts (Outfit)** | - | `fonts.googleapis.com` | Typography |
| **Chart.js** | 4.4.0 | `cdn.jsdelivr.net` | Data Visualization (Bar, Doughnut) |
| **SweetAlert2** | 11.x | `cdn.jsdelivr.net` | Beautiful Alert/Modal/Confirm Dialog |
| **Vanilla JS (ES6+)** | Native | `assets/js/script.js` | Client-side Logic, Fetch API, DOM |

### **Arsitektur & Pattern**
| Pola | Implementasi |
|------|--------------|
| **MVC-like** | `api/*.php` (Controller), `sql/*.sql` (Model), `*.php` (View) |
| **RESTful API** | `api/auth_api.php`, `api/guest_api.php`, `api/security_api.php`, `api/report_api.php` |
| **Single Entry Point** | `index.php` → Router ke dashboard berdasarkan role |
| **Modular API** | Action-based routing via `?action=` query param |
| **Security First** | Prepared Statements, XSS Escape (`htmlspecialchars`), CSRF-ready (SameSite Cookie) |

---

## 📁 Struktur Proyek

```
pisma/
├── index.php                 # Entry point: Login + Register (Guest Portal)
├── dashboard.php             # Unified Dashboard (Security + Admin tabs)
├── admin_dash.php            # Admin Panel (Dark Theme, Full Features)
├── security_dash.php         # Security Panel (Dark Theme, Scan Focused)
├── guest.php                 # Public Guest Registration Portal
├── logout.php                # Session Destroy + Redirect
├── hash_password.php         # Utility: Generate bcrypt hash untuk seeding
├── AGENT.py                  # (Opsional) Python Agent/Script
│
├── api/
│   ├── config.php            # DB Connection (PDO) + Helper Functions
│   ├── auth_api.php          # Register User API
│   ├── guest_api.php         # Guest Register & Check-in API
│   ├── security_api.php      # Security Ops: Guests, Logs, Blacklist, Stats
│   └── report_api.php        # Admin Reports: Stats, Users, Audit, Export CSV
│
├── assets/
│   ├── css/
│   │   └── style.css         # Custom CSS (Legacy/Guest Portal styling)
│   └── js/
│       └── script.js         # Shared JS Utilities (Guest Portal logic)
│
└── sql/
    └── database_init.sql     # Full Schema + Indexes + Seed Data
```

---

## 🗄 Database Schema

```sql
-- users: Admin & Security accounts
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,          -- bcrypt hash
    fullname VARCHAR(150) NOT NULL,
    role ENUM('admin','security') DEFAULT 'security',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- guests: Data tamu (bisa kunjungan berulang)
CREATE TABLE guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(150) NOT NULL,
    identity_number VARCHAR(100) NOT NULL,   -- KTP/SIM
    phone VARCHAR(50) NULL,
    institution VARCHAR(150) NULL,
    vehicle_number VARCHAR(50) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- visits: Riwayat kunjungan per tamu
CREATE TABLE visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT NOT NULL,
    purpose TEXT NOT NULL,
    destination VARCHAR(150) NOT NULL,
    check_in_time DATETIME NULL,
    checkout_time DATETIME NULL,
    status ENUM('waiting','checked_in','completed') DEFAULT 'waiting',
    qr_code VARCHAR(100) UNIQUE NOT NULL,    -- Format: DIG-XXXXXX-XXX
    security_in_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE,
    FOREIGN KEY (security_in_id) REFERENCES users(id) ON DELETE SET NULL
);

-- blacklist: Identitas terblokir
CREATE TABLE blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identity_number VARCHAR(100) NOT NULL,
    reason TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- security_logs: Audit Trail
CREATE TABLE security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,            -- LOGIN, CHECK_IN, CHECK_OUT, BLACKLIST_*, GUEST_REGISTER
    description TEXT,
    ip_address VARCHAR(50),
    guest_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE SET NULL
);
```

### **Seed Data (Default)**
```sql
-- Password: 'rahasia' (bcrypt hash)
INSERT INTO users (username, password, fullname, role, is_active) VALUES 
('admin', '$2y$10$w09ZtoT.FvVStyT0mN/UgeC1D5S5m3R./y.o90w0L3G/z6Kx6H5e.', 'Administrator Utama', 'admin', 1),
('security', '$2y$10$w09ZtoT.FvVStyT0mN/UgeC1D5S5m3R./y.o90w0L3G/z6Kx6H5e.', 'Petugas Security 1', 'security', 1);
```

---

## ⚙️ Instalasi & Setup

### **Prasyarat**
- **PHP 8.2+** (dengan ekstensi `pdo_mysql`, `session`, `mbstring`)
- **MySQL 8.0+** atau **MariaDB 10.5+**
- **Web Server**: Apache (XAMPP/Laragon) / Nginx / PHP Built-in Server
- **Browser Modern** (ES6+ support untuk Chart.js, Fetch API)

### **Langkah Instalasi**

#### **1. Clone / Copy Project**
```bash
# Jika menggunakan Git
git clone https://github.com/username/pisma-security.git
cd pisma-security/pisma

# Atau copy folder pisma ke htdocs/www
```

#### **2. Database Setup**
```bash
# Masuk ke MySQL
mysql -u root -p

# Jalankan script inisialisasi
SOURCE sql/database_init.sql;

# Atau via command line
mysql -u root -p < sql/database_init.sql
```

#### **3. Konfigurasi Database**
Edit `api/config.php`:
```php
$host = 'localhost';
$dbname = 'dig_security';
$username = 'root';      // Sesuaikan dengan MySQL user DB user
$password = '';          // Sesuaikan password MySQL
```

#### **4. Jalankan Web Server**

**Opsi A: XAMPP / Laragon (Recommended)**
- Copy folder `pisma` ke `htdocs` (XAMPP) atau `www` (Laragon)
- Start Apache & MySQL
- Akses: `http://localhost/pisma/`

**Opsi B: PHP Built-in Server (Development)**
```bash
cd pisma
php -S localhost:8000
# Akses: http://localhost:8000
```

**Opsi C: Nginx + PHP-FPM (Production)**
```nginx
server {
    listen 80;
    server_name pisma.local;
    root /var/www/pisma/pisma;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 🔧 Konfigurasi

### **Environment Variables (Optional)**
Buat file `.env` di root (jika menggunakan `vlucas/phpdotenv`):
```env
DB_HOST=localhost
DB_NAME=dig_security
DB_USER=root
DB_PASS=
APP_ENV=production
SESSION_LIFETIME=7200
```

### **TailwindCSS Config (Inline di HTML)**
Semua file PHP menggunakan Tailwind via CDN dengan config inline:
```html
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: { extend: { colors: { primary: {...} } } }
  }
</script>
```

### **Chart.js Default Config**
```javascript
Chart.defaults.font.family = "'Outfit', sans-serif";
Chart.defaults.color = '#94a3b8';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
```

---

## 🔑 Default Login

| Role | Username | Password | Akses |
|------|----------|----------|-------|
| **Admin** | `admin` | `rahasia` | Full Access: Admin Dashboard, Security Mode, Guest Portal, User Management, Audit, Export |
| **Security** | `security` | `rahasia` | Security Dashboard: Scan Check-in/out, Guest List, Blacklist, Logs |

> ⚠️ **Ganti password default segera setelah instalasi production!**

---

## 🌐 API Endpoints

### **Base URL**: `http://domain/api/`

| Endpoint | Method | Action | Auth | Role | Deskripsi |
|----------|--------|--------|------|------|-----------|
| `auth_api.php` | POST | `register` | ❌ | - | Registrasi user baru (default role: security) |
| `guest_api.php` | POST | `register` | ✅ | All | Registrasi tamu baru + generate QR |
| `guest_api.php` | POST | `checkin` | ✅ | All | Proses check-in via QR Code |
| `security_api.php` | GET | `get_today_guests` | ✅ | All | Daftar tamu hari ini (search, filter status) |
| `security_api.php` | GET | `get_logs` | ✅ | All | Log aktivitas (limit) |
| `security_api.php` | POST | `checkout` | ✅ | All | Proses check-out tamu |
| `security_api.php` | GET | `get_blacklist` | ✅ | All | Daftar blacklist |
| `security_api.php` | POST | `add_blacklist` | ✅ | All | Tambah blacklist |
| `security_api.php` | POST | `remove_blacklist` | ✅ | Admin | Hapus blacklist |
| `security_api.php` | GET | `live_stats` | ✅ | All | Real-time stats (untuk polling) |
| `report_api.php` | GET | `dashboard_stats` | ✅ | Admin | Stats overview + weekly data |
| `report_api.php` | GET | `get_all_guests` | ✅ | Admin | Data kunjungan lengkap (pagination, search) |
| `report_api.php` | GET | `get_users` | ✅ | Admin | Daftar user sistem |
| `report_api.php` | POST | `toggle_user` | ✅ | Admin | Aktifkan/Nonaktifkan user |
| `report_api.php` | POST | `create_user` | ✅ | Admin | Tambah user baru |
| `report_api.php` | GET | `audit_logs` | ✅ | Admin | Audit trail lengkap |
| `report_api.php` | GET | `export_csv` | ✅ | Admin | Export CSV semua data kunjungan |

### **Response Format (JSON)**
```json
// Success
{ "success": true, "message": "...", "data": {...} }

// Error
{ "success": false, "message": "...", "error": "..." }
```

---

## 📸 Screenshot & Demo

### **Login & Guest Portal**
| Login Page | Guest Registration |
|------------|-------------------|
| ![Login](screenshots/login.png) | ![Guest](screenshots/guest.png) |

### **Security Dashboard**
| Scan Panel | Guest List | Blacklist |
|------------|------------|-----------|
| ![Scan](screenshots/security_scan.png) | ![List](screenshots/security_guests.png) | ![Blacklist](screenshots/security_blacklist.png) |

### **Admin Dashboard**
| Overview + Charts | Data Kunjungan | User Management |
|-------------------|----------------|-----------------|
| ![Admin](screenshots/admin_overview.png) | ![Data](screenshots/admin_guests.png) | ![Users](screenshots/admin_users.png) |

> 📝 *Tambahkan screenshot ke folder `screenshots/` di repo GitHub untuk tampil di atas*

---

## 🔒 Security Checklist

- [x] **SQL Injection Prevention**: PDO Prepared Statements di semua query
- [x] **XSS Protection**: `htmlspecialchars()` di semua output user-generated
- [x] **Password Security**: bcrypt (cost 10) via `password_hash()`
- [x] **Session Security**: `session_start()` dengan `httponly`, `secure` (HTTPS production)
- [x] **Role-Based Access**: Middleware `requireLogin()` + role check di Admin API
- [x] **Blacklist Detection**: Validasi di registrasi & check-in
- [x] **Audit Logging**: Semua aksi kritis tercatat dengan IP & User
- [x] **CSRF Ready**: SameSite Cookie, Token bisa ditambahkan ke form
- [ ] **Rate Limiting** (TODO: Tambah middleware rate limit untuk API)
- [ ] **HTTPS Enforcement** (Production: Setup SSL + HSTS)
- [ ] **Content Security Policy** (TODO: Header CSP)

---

## 🗺 Roadmap / TODO

- [ ] **QR Code Scanner Camera** (Web API `BarcodeDetector` / QuaggaJS)
- [ ] **Real-time WebSocket** (Socket.io / Pusher) untuk live update tanpa polling
- [ ] **Email/WA Notification** pada check-in/out tamu
- [ ] **Multi-location/Branch Support**
- [ ] **Visitor Pre-registration** (Admin buat undangan, tamu QR ready)
- [ ] **Report PDF** (dompdf / tcpdf)
- [ ] **Dockerize** (Dockerfile + docker-compose.yml)
- [ ] **Unit Test** (PHPUnit) & **E2E Test** (Cypress)
- [ ] **CI/CD Pipeline** (GitHub Actions)

---

## 🤝 Kontribusi

1. Fork repository ini
2. Buat branch fitur: `git checkout -b fitur/nama-fitur`
3. Commit changes: `git commit -m 'feat: tambah fitur X'`
4. Push branch: `git push origin fitur/nama-fitur`
5. Buat **Pull Request**

### **Conventional Commits**
```
feat:     Fitur baru
fix:      Bug fix
docs:     Dokumentasi
style:    Formatting, missing semi colons, etc
refactor: Refactoring code
test:     Adding tests
chore:    Maintenance
```

---

## 📄 Lisensi

**MIT License** - Silakan gunakan, modifikasi, dan distribusikan untuk keperluan pribadi maupun komersial.

```
Copyright (c) 2025 PISMA Security Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software...
```

---

## 👨‍💻 Author & Credits

| Role | Nama |
|------|------|
| **Developer** | [Nama Anda / Tim PISMA] |
| **UI/UX Design** | TailwindCSS + Custom Glassmorphism |
| **Icons** | Font Awesome 6 |
| **Charts** | Chart.js 4 |
| **Alerts** | SweetAlert2 |

---

## 📞 Support & Contact

- **Issues**: [GitHub Issues](https://github.com/username/pisma-security/issues)
- **Discussions**: [GitHub Discussions](https://github.com/username/pisma-security/discussions)
- **Email**: security@pisma.example.com

---

⭐ **Jika project ini bermanfaat, beri star di GitHub!** ⭐

---

*Generated with ❤️ for PISMA Security System*