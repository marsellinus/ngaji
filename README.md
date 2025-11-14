# 🕌 Sistem Absensi Ngaji IoT

**Sistem absensi otomatis berbasis ESP32-C6 dan RFID MFRC522 untuk Lembaga Pendidikan Islam (TPQ/TPA/Pondok Pesantren)**

[![Tech Stack](https://img.shields.io/badge/ESP32--C6-IoT-blue)](https://www.espressif.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 📖 Tentang Project

Sistem ini dirancang untuk **memudahkan pencatatan kehadiran santri** di lembaga pendidikan Islam secara otomatis menggunakan teknologi IoT. Santri cukup **tap kartu RFID**, data kehadiran langsung tercatat ke database, dan admin bisa monitoring real-time via web dashboard.

### 🎯 Latar Belakang
- **Masalah**: Pencatatan absensi manual memakan waktu dan rawan kesalahan
- **Solusi**: Sistem otomatis dengan RFID yang cepat, akurat, dan real-time
- **Target User**: TPQ, TPA, Pondok Pesantren, Madrasah

### ✨ Keunggulan
- ⚡ **Cepat** - Absensi hanya 1-2 detik per santri
- 🎯 **Akurat** - Data tersimpan otomatis di database
- 📊 **Real-time Monitoring** - Admin bisa lihat dashboard langsung
- 📱 **Mobile Friendly** - Responsive design dengan Tailwind CSS
- 🔐 **Multi-User Login** - Admin (kelola sistem) & Santri (lihat log sendiri)
- 🌐 **IoT Cloud Ready** - Support HTTPS dan Blynk IoT
- 💡 **Visual Feedback** - RGB LED & Buzzer untuk indikasi status
- 🔄 **Auto Reconnect** - WiFi & server auto-reconnect jika disconnect

## 🚀 Instalasi & Setup

### A. Setup Web Server (Local)

#### 1️⃣ Install XAMPP
- Download: https://www.apachefriends.org/
- Install dan start **Apache** + **MySQL**

#### 2️⃣ Clone/Download Project
```bash
cd C:\xampp\htdocs\
git clone https://github.com/marsellinus/ngaji.git cc
# Atau download ZIP dan extract ke C:\xampp\htdocs\cc
```

#### 3️⃣ Import Database
**Via phpMyAdmin**:
- Buka http://localhost/phpmyadmin
- Create database baru: `db_ngaji`
- Import file: `database.sql`

**Via Command Line**:
```bash
mysql -u root -p
# Ketik password MySQL (default: kosong)

CREATE DATABASE db_ngaji;
USE db_ngaji;
source C:/xampp/htdocs/cc/database.sql;
exit;
```

#### 4️⃣ Konfigurasi Database (jika perlu)
Edit file `config/database.php`:
```php
$host = 'localhost';
$user = 'root';
$pass = '';  // Ganti jika punya password MySQL
$db   = 'db_ngaji';
```

#### 5️⃣ Akses Sistem
```
URL Utama: http://localhost/cc/

Pages:
├─ Login:          http://localhost/cc/
├─ Dashboard:      http://localhost/cc/public/index.php
├─ Log Santri:     http://localhost/cc/public/santri_log.php
└─ API Endpoint:   http://localhost/cc/api/absen.php
```

### B. Setup ESP32 Hardware

#### 1️⃣ Install Arduino IDE
- Download: https://www.arduino.cc/en/software
- Install ESP32 Board:
  - File → Preferences → Additional Board Manager URLs
  - Tambahkan: `https://espressif.github.io/arduino-esp32/package_esp32_index.json`
  - Tools → Board → Board Manager → Search "esp32" → Install

#### 2️⃣ Install Library
**Via Library Manager** (Sketch → Include Library → Manage Libraries):
- `MFRC522` by GithubCommunity
- `Blynk` by Volodymyr Shymanskyy (optional, untuk Blynk IoT)

#### 3️⃣ Wiring Hardware
Sambungkan sesuai **Pin Configuration** di atas

#### 4️⃣ Konfigurasi ESP32 Code
Edit file `esp32_absensi.ino`:
```cpp
// WiFi Configuration
const char* ssid = "NamaWiFiAnda";      // Ganti
const char* password = "PasswordWiFi";   // Ganti

// Server Configuration
const char* serverURL = "http://192.168.1.100/cc/api/absen.php";
// Ganti dengan IP komputer/laptop Anda
// Cara cek IP: buka CMD → ketik "ipconfig"

// Blynk (Optional)
#define BLYNK_TEMPLATE_ID "TMPLxxxxxx"   // Dari Blynk App
#define BLYNK_AUTH_TOKEN "your_token"    // Dari Blynk App
```

#### 5️⃣ Upload ke ESP32
- Tools → Board → **ESP32C6 Dev Module**
- Tools → Port → Pilih COM port ESP32
- Klik **Upload** (→)
- Buka **Serial Monitor** (115200 baud) untuk debug

#### 6️⃣ Test System
- ESP32 akan connect ke WiFi
- LED hijau nyala = Ready
- Tap kartu RFID → Data masuk ke database
- Cek di dashboard web

## 🖼️ Screenshots

### 🔐 Login Page
Universal login untuk admin dan santri dengan validation & security.

### 📊 Admin Dashboard
Statistik real-time: total santri, kehadiran hari ini/minggu/bulan dengan chart interaktif.

### 👥 Data Santri Management
CRUD lengkap dengan search, pagination, dan set username/password untuk santri.

### 📋 Laporan Absensi
Filter advanced, export Excel, print, dengan preview data yang rapi.

### 📱 Santri Log View
Mobile-friendly interface untuk santri lihat rekap absensi pribadi mereka.

### 🤖 ESP32 Hardware
ESP32-C6 + MFRC522 RFID + RGB LED + Buzzer dalam satu box enclosure.

---

## 🔐 Default Login Credentials

### 👨‍💼 Admin (Full Access)
```
Username: admin
Password: admin123
```
**Akses**:
- ✅ Dashboard statistik
- ✅ Kelola semua data santri
- ✅ Lihat semua laporan absensi
- ✅ Kelola admin lain
- ✅ Activity log
- ✅ Pengaturan sistem

### 👨‍🎓 Santri (View Only - Absensi Pribadi)
```
Username: ahmad | fatimah | rizki | aisyah | umar
Password: admin123
```
**Akses**:
- ✅ Lihat log absensi sendiri saja
- ✅ Filter periode absensi
- ✅ Statistik kehadiran pribadi
- ❌ Tidak bisa lihat data santri lain
- ❌ Tidak bisa edit data

> ⚠️ **PENTING**: Ganti password default setelah instalasi!

## 📋 Fitur Lengkap

### 👨‍💼 Fitur Admin (Web Dashboard)
- 📊 **Dashboard Statistik** - Total santri, kehadiran hari ini, minggu ini, bulan ini dengan chart
- 👥 **Kelola Data Santri** - CRUD lengkap (Create, Read, Update, Delete)
  - Tambah santri baru dengan RFID ID
  - Edit nama, kelas, RFID ID (bisa diubah admin)
  - Set username/password untuk santri (opsional)
  - Hapus santri (cascade delete absensi)
- 📋 **Laporan Absensi** - Filter berdasarkan tanggal, santri, status
  - Export ke Excel/CSV
  - Print laporan
  - Statistik kehadiran per santri
- 👤 **Kelola Admin Lain** - Tambah/edit/hapus user admin
- 📜 **Activity Log** - Track semua aktivitas admin di sistem
- ⚙️ **Pengaturan Sistem** - Customize nama aplikasi, timezone, jam absensi

### 👨‍🎓 Fitur Santri (Mobile Friendly)
- 🔐 **Login Mandiri** - Santri bisa login dengan username/password
- 📱 **Lihat Log Pribadi** - Hanya melihat absensi mereka sendiri
- 📅 **Filter Periode** - Hari ini, minggu ini, bulan ini, custom range
- 📊 **Statistik Kehadiran** - Total hadir, persentase, streak kehadiran
- 🔔 **Status Real-time** - Cek apakah sudah absen hari ini atau belum

### 🤖 Fitur ESP32 (Hardware IoT)
- 🆔 **Scan RFID** - Deteksi kartu MFRC522 otomatis
- 💡 **LED Indicator**:
  - 🔵 Biru = Booting
  - 🟢 Hijau = Ready (siap scan)
  - 🟡 Kuning = Scanning
  - ✅ Hijau Blink 3x = Absensi berhasil
  - ⚠️ Kuning = Sudah absen hari ini
  - ❌ Merah Blink 3x = Kartu tidak terdaftar
  - 💤 Mati otomatis setelah 30 detik tidak aktif (power saving)
- 🔊 **Buzzer Feedback**:
  - Beep 1x = Sukses
  - Beep 2x = Error/Sudah absen
- 🌐 **WiFi Auto Reconnect** - Otomatis reconnect jika WiFi putus
- 📡 **HTTP/HTTPS Support** - Bisa ke local server atau cloud hosting
- 📱 **Blynk IoT Integration** - Remote monitoring & control via smartphone (optional)

### 🔐 Sistem Keamanan
- 🔒 **Password Hashing** - bcrypt dengan salt untuk semua password
- 🚫 **Session Management** - Auto logout setelah inactivity
- 🛡️ **SQL Injection Protection** - Prepared statements & input sanitization
- 👮 **Role-Based Access Control** - Admin vs Santri permission
- 📝 **Activity Logging** - Track semua perubahan data

## 🎯 Cara Pakai

### Untuk Admin:

1. **Login sebagai admin** (admin/admin123)
2. **Tambah santri baru**:
   - Menu: Data Santri → Tambah Santri Baru
   - Isi: Nama, RFID ID, Kelas
   - **Username & Password**: Isi jika ingin santri bisa login
   - Klik Simpan
3. **Santri tap kartu RFID** → Data absensi masuk otomatis
4. **Lihat laporan** di menu Laporan Absensi

### Untuk Santri:

1. **Login** dengan username/password yang dibuat admin
2. **Otomatis masuk** ke halaman log absensi
3. **Lihat rekap** kehadiran mereka sendiri
4. **Filter** berdasarkan periode yang diinginkan

## 🛠️ Hardware Requirements

### ESP32-C6 + RFID Module
| Komponen | Spesifikasi |
|----------|-------------|
| **ESP32-C6 DevKit** | WiFi 6, Bluetooth 5.3 |
| **MFRC522 RFID Reader** | 13.56MHz, SPI |
| **RGB LED Common Cathode** | 10mm, 3 warna | 
| **Buzzer Active 5V** | Piezo speaker | 
| **Kartu RFID (10 pcs)** | Mifare 1K | 
| **Resistor 220Ω (3 pcs)** | Untuk LED | 
| **Kabel Jumper** | Male-Female | 

### 🔌 Pin Configuration (ESP32-C6)

#### MFRC522 RFID Reader
```
RFID Pin    → ESP32-C6 GPIO
─────────────────────────────
SDA (SS)    → GPIO 5
SCK         → GPIO 18
MOSI        → GPIO 23
MISO        → GPIO 19
RST         → GPIO 22
VCC         → 3.3V (⚠️ PENTING! Jangan 5V)
GND         → GND
```

#### RGB LED (Common Cathode)
```
LED Pin     → ESP32-C6 GPIO
─────────────────────────────
RED Anode   → GPIO 2  + resistor 220Ω
GREEN Anode → GPIO 4  + resistor 220Ω
BLUE Anode  → GPIO 15 + resistor 220Ω
Cathode (−) → GND
```

#### Buzzer Active 5V
```
Buzzer Pin  → ESP32-C6 GPIO
─────────────────────────────
Signal (+)  → GPIO 21
GND (−)     → GND
```

### 📡 Server Requirements
- **PHP** 7.4 atau lebih tinggi
- **MySQL** 5.7 atau lebih tinggi
- **Apache/Nginx** Web Server
- **Internet Connection** (untuk Blynk IoT - optional)

### 🖥️ Hosting Options
- **Local Development**: XAMPP, WAMP, Laragon
- **Free Hosting**: 000webhost, AwardSpace (IoT friendly)
- **Paid Hosting**: Niagahoster, DomaiNesia, Hostinger
- **VPS**: Contabo, DigitalOcean, Vultr (paling fleksibel)

## 📂 Struktur Project

```
cc/
├── 📁 api/
│   └── absen.php                  # REST API endpoint untuk ESP32
│                                  # GET: health check, POST: catat absensi
│
├── 📁 config/
│   └── database.php               # Konfigurasi koneksi MySQL
│
├── 📁 includes/
│   ├── auth.php                   # Sistem autentikasi (admin & santri)
│   ├── functions.php              # Helper functions (sanitize, validate, dll)
│   ├── header.php                 # Header template dengan menu navigation
│   └── footer.php                 # Footer template
│
├── 📁 public/
│   ├── login.php                  # 🔐 Halaman login universal (admin + santri)
│   ├── index.php                  # 📊 Dashboard admin (statistik & chart)
│   ├── santri_log.php             # 📱 Log absensi santri (view only)
│   ├── laporan.php                # 📋 Laporan lengkap dengan filter & export
│   ├── tambah_santri.php          # 👥 CRUD data santri + set username/password
│   ├── admin_users.php            # 👤 Kelola user admin
│   ├── activity_logs.php          # 📜 Activity log sistem
│   ├── settings.php               # ⚙️ Pengaturan sistem
│   └── profile.php                # 👨‍💼 Profil admin
│
├── 📁 assets/
│   ├── css/                       # Tailwind CSS via CDN
│   └── js/                        # Chart.js, custom scripts
│
├── 📄 esp32_absensi.ino           # 🤖 Source code ESP32 (Arduino)
├── 📄 database.sql                # 💾 Schema + data dummy MySQL
├── 📄 README.md                   # 📖 Dokumentasi project (file ini)
├── 📄 BLYNK_SETUP.md              # 📱 Tutorial setup Blynk IoT
└── 📄 HOSTING_INFINITYFREE.md     # 🌐 Tutorial deploy ke InfinityFree
```

### 🗄️ Database Structure (db_ngaji)

```sql
📊 santri (Data santri + login credentials)
├── id (PK, AUTO_INCREMENT)
├── nama_santri (VARCHAR 100)
├── rfid_id (VARCHAR 50, UNIQUE) ← UID dari kartu RFID
├── username (VARCHAR 50, UNIQUE, NULL) ← Untuk login santri
├── password (VARCHAR 255, NULL) ← bcrypt hash
├── kelas (VARCHAR 50)
├── keterangan (TEXT)
├── status (ENUM: aktif/nonaktif)
└── timestamps

📋 absensi (Data kehadiran santri)
├── id (PK, AUTO_INCREMENT)
├── rfid_id (FK → santri.rfid_id)
├── waktu_absen (DATETIME)
├── status (ENUM: Hadir/Tidak Hadir/Izin/Sakit)
├── keterangan (TEXT)
└── created_at

👤 admin (User admin yang kelola sistem)
├── id (PK, AUTO_INCREMENT)
├── username (VARCHAR 50, UNIQUE)
├── password (VARCHAR 255) ← bcrypt hash
├── nama_lengkap (VARCHAR 100)
├── email (VARCHAR 100)
├── foto (VARCHAR 255)
├── status (ENUM: aktif/nonaktif)
├── last_login (DATETIME)
└── timestamps

📜 activity_log (Track aktivitas admin)
├── id (PK, AUTO_INCREMENT)
├── admin_id (FK → admin.id)
├── activity_type (VARCHAR 50)
├── description (TEXT)
├── ip_address (VARCHAR 50)
├── user_agent (TEXT)
└── created_at

⚙️ settings (Konfigurasi sistem)
├── id (PK, AUTO_INCREMENT)
├── setting_key (VARCHAR 100, UNIQUE)
├── setting_value (TEXT)
├── setting_type (VARCHAR 50)
├── description (TEXT)
└── updated_at
```

## 📖 Cara Penggunaan

### Skenario 1: Admin Mengelola Sistem

1. **Login Admin**
   - Buka http://localhost/cc/
   - Username: `admin` | Password: `admin123`
   - Masuk ke dashboard

2. **Tambah Santri Baru**
   - Menu: **Data Santri** → **Tambah Santri Baru**
   - Isi form:
     - Nama Santri: `Usman bin Affan`
     - RFID ID: `AB12CD34` (scan dulu dari kartu RFID)
     - Kelas: Pilih dari dropdown (Iqro 1-6, Al-Quran, Umum)
     - Keterangan: Optional
   - **Username & Password** (Optional):
     - Isi jika santri ingin bisa login lihat log pribadi
     - Biarkan kosong jika tidak perlu login
   - Klik **Simpan**

3. **Santri Absen dengan Tap Kartu**
   - Santri tap kartu RFID ke reader
   - ESP32 kirim UID ke server
   - Server cek database → jika terdaftar → simpan absensi
   - LED hijau blink 3x + buzzer beep 1x = Sukses ✅
   - Data langsung muncul di dashboard admin

4. **Lihat Laporan**
   - Menu: **Laporan Absensi**
   - Filter berdasarkan:
     - Tanggal (range)
     - Santri (pilih spesifik atau semua)
     - Status (Hadir/Tidak Hadir/Izin/Sakit)
   - Export ke Excel atau Print

### Skenario 2: Santri Melihat Log Pribadi

1. **Login Santri**
   - Buka http://localhost/cc/
   - Username: `ahmad` | Password: `admin123`
   - Otomatis redirect ke halaman log pribadi

2. **Lihat Statistik**
   - Total kehadiran
   - Persentase kehadiran
   - Streak (berapa hari berturut-turut hadir)

3. **Filter Periode**
   - Hari ini
   - Minggu ini
   - Bulan ini
   - Custom range

### Skenario 3: ESP32 Indikator LED

| LED Status | Arti | Aksi |
|------------|------|------|
| 🔵 Biru | Booting | Tunggu 5 detik |
| 🟢 Hijau | Ready - Siap scan | Tap kartu RFID |
| 🟡 Kuning | Sedang scanning | Tunggu... |
| ✅ Hijau Blink 3x | Absensi berhasil! | ✓ Data tersimpan |
| ⚠️ Kuning + Beep 2x | Sudah absen hari ini | Skip, sudah tercatat |
| ❌ Merah Blink 3x | Kartu tidak terdaftar | Hubungi admin |
| 💤 LED Mati | Standby (30 detik idle) | Tap kartu untuk nyalakan |

### Skenario 4: Troubleshooting

#### LED Merah Terus?
- Cek wiring RFID → ESP32
- Pastikan RFID pakai 3.3V (bukan 5V!)
- Cek Serial Monitor untuk error

#### Kartu Scan Tapi Tidak Masuk Database?
- Cek Serial Monitor: apakah HTTP 200 OK?
- Cek koneksi WiFi
- Cek server URL di ESP32 code
- Cek RFID ID sudah terdaftar di database

#### Santri Tidak Bisa Login?
- Pastikan admin sudah set username/password saat tambah santri
- Username harus unique (tidak boleh sama)
- Password minimal 6 karakter

#### Buzzer Tidak Bunyi?
- Cek wiring buzzer
- Coba uncomment kode Passive Buzzer (jika pakai passive)
- Cek buzzer test saat booting

---

## 💡 Tips & Best Practices

### Untuk Admin:
- ✅ **Backup Database** rutin setiap minggu
- ✅ **Ganti password default** admin setelah install
- ✅ Set **username/password santri** hanya jika diperlukan
- ✅ Gunakan **RFID ID** yang mudah diingat (misal: nomor induk santri)
- ✅ **Export laporan** setiap akhir bulan untuk arsip

### Untuk ESP32:
- ✅ **Tes dulu di local** sebelum deploy ke cloud
- ✅ Gunakan **power supply 5V 2A** yang stabil
- ✅ Taruh ESP32 di **tempat kering** (jangan kena air!)
- ✅ **LED standby mati** setelah 30 detik = normal (power saving)
- ✅ Jika WiFi sering putus, coba **ganti channel WiFi router**

### Untuk Santri:
- ✅ Tap kartu **1-2 detik** (jangan terlalu cepat)
- ✅ Tunggu hingga **LED hijau blink** sebelum lepas kartu
- ✅ Jika **LED merah**, hubungi ustadz/admin
- ✅ **Jaga kartu RFID** jangan sampai hilang

## 🔧 Troubleshooting Common Issues

### Error 1: "Connection refused" di ESP32
**Gejala**: Serial Monitor menunjukkan HTTP error -1 atau connection refused

**Solusi**:
```bash
# Cek firewall Windows
# Control Panel → Windows Defender Firewall → Allow an app
# Pastikan Apache HTTP Server diizinkan

# Atau matikan firewall sementara untuk test
# (jangan lupa nyalakan lagi setelah test)
```

### Error 2: "Table 'db_ngaji.santri' doesn't exist"
**Gejala**: Error saat buka dashboard web

**Solusi**:
```sql
-- Import ulang database
DROP DATABASE IF EXISTS db_ngaji;
CREATE DATABASE db_ngaji;
USE db_ngaji;
source C:/xampp/htdocs/cc/database.sql;
```

### Error 3: "Username atau password salah"
**Gejala**: Login admin/santri gagal terus

**Solusi**:
```sql
-- Reset password admin ke default (admin123)
UPDATE admin SET password = '$2y$10$t8rQGkmSBAqCvQTCMMxU4ewUF5Lb3LPEwlLbURMEL/4LhiuuTkrHu' 
WHERE username = 'admin';
```

### Error 4: LED ESP32 Merah Terus
**Gejala**: MFRC522 tidak terdeteksi

**Solusi**:
- Cek wiring: MFRC522 VCC **harus 3.3V** (bukan 5V!)
- Cek SPI pins (SS, SCK, MOSI, MISO, RST)
- Ganti modul RFID jika rusak

### Error 5: WiFi ESP32 Tidak Connect
**Gejala**: WiFi connection failed

**Solusi**:
```cpp
// Cek SSID & password benar
const char* ssid = "NamaWiFiYangBenar";
const char* password = "PasswordYangBenar";

// Cek WiFi 2.4GHz (bukan 5GHz)
// ESP32-C6 tidak support WiFi 5GHz di mode ini
```

### Error 6: Blynk "Offline" Terus
**Gejala**: Blynk App menunjukkan device offline

**Solusi**:
- Cek Auth Token benar
- Cek Template ID benar
- Pastikan internet connection aktif
- Restart ESP32

---

## 📚 Dokumentasi Tambahan

- **[BLYNK_SETUP.md](BLYNK_SETUP.md)** - Tutorial lengkap setup Blynk IoT untuk remote monitoring
- **[HOSTING_INFINITYFREE.md](HOSTING_INFINITYFREE.md)** - Panduan deploy ke hosting gratis/berbayar
- **[ESP32 Pinout](https://docs.espressif.com/projects/esp-idf/en/latest/esp32c6/)** - Datasheet ESP32-C6
- **[MFRC522 Library](https://github.com/miguelbalboa/rfid)** - Library RFID yang digunakan

---

## 🤝 Contributing

Kontribusi sangat diterima! Silakan:
1. Fork repository ini
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

---

## 📝 License

Project ini menggunakan [MIT License](LICENSE).

Anda bebas untuk:
- ✅ Menggunakan untuk project pribadi
- ✅ Menggunakan untuk project komersial
- ✅ Memodifikasi source code
- ✅ Mendistribusikan ulang

Dengan syarat:
- ⚠️ Sertakan copyright notice asli
- ⚠️ License harus tetap MIT

---

## 📞 Support & Contact

**Developer**: Marsellinus  
**Email**: -  
**GitHub**: [@marsellinus](https://github.com/marsellinus)  
**Repository**: [github.com/marsellinus/ngaji](https://github.com/marsellinus/ngaji)

**Issues & Bug Report**: [GitHub Issues](https://github.com/marsellinus/ngaji/issues)

---

## 🙏 Acknowledgments

Terima kasih kepada:
- **ESP32 Community** - Support & library
- **Miguelbalboa** - MFRC522 RFID Library
- **Blynk** - IoT Platform
- **Tailwind CSS** - UI Framework
- **Chart.js** - Data visualization
- **Semua kontributor** yang telah membantu project ini

---

## 📊 Tech Stack Summary

| Layer | Technology | Version |
|-------|-----------|---------|
| **Hardware** | ESP32-C6 | DevKit v1.0 |
| **RFID** | MFRC522 | 13.56MHz |
| **Backend** | PHP | 7.4+ |
| **Database** | MySQL | 5.7+ |
| **Frontend** | Tailwind CSS | 3.x via CDN |
| **Charts** | Chart.js | 4.x |
| **IoT Cloud** | Blynk IoT | Latest |
| **IDE** | Arduino IDE | 2.x |
| **Web Server** | Apache | 2.4+ |

---

**⭐ Jika project ini membantu, berikan star di GitHub! ⭐**

---

**Dibuat dengan ❤️ untuk kemudahan pencatatan absensi di lembaga pendidikan Islam**
