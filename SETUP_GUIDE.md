# 📘 Panduan Setup Lengkap - Sistem Absensi Ngaji IoT

## 🎯 Langkah-Langkah Setup

### STEP 1: Install XAMPP/Laragon

**Untuk Windows:**
1. Download XAMPP dari: https://www.apachefriends.org/
2. Install XAMPP di `C:\xampp` atau lokasi lain
3. Jalankan XAMPP Control Panel
4. Start service **Apache** dan **MySQL**

**Untuk Mac/Linux:**
1. Download XAMPP untuk platform Anda
2. Follow installer instructions
3. Start Apache dan MySQL

---

### STEP 2: Import Database

**Via phpMyAdmin:**
1. Buka browser, akses: `http://localhost/phpmyadmin`
2. Login (default: username `root`, password kosong)
3. Klik tab **"Import"**
4. Pilih file `database.sql` dari folder proyek
5. Klik **"Go"** untuk import

**Via Command Line:**
```bash
# Windows (Command Prompt)
cd C:\xampp\mysql\bin
mysql -u root -p < D:\xampp-port\htdocs\cc\database.sql

# Mac/Linux (Terminal)
mysql -u root -p < /path/to/database.sql
```

**Verifikasi Database:**
- Database `db_ngaji` harus terbuat
- Tabel `santri` dan `absensi` harus ada
- Data dummy santri sudah terisi (5 santri)

---

### STEP 3: Konfigurasi Database Connection

1. Buka file `config/database.php`
2. Edit sesuai konfigurasi MySQL Anda:

```php
define('DB_HOST', 'localhost');    // Host database
define('DB_USER', 'root');         // Username MySQL
define('DB_PASS', '');             // Password MySQL (kosong default)
define('DB_NAME', 'db_ngaji');     // Nama database
```

3. Save file

---

### STEP 5: Testing Website

1. Buka browser
2. Akses: `http://localhost/cc/public/index.php`
3. Anda akan melihat:
   - Dashboard dengan statistik
   - Data absensi dummy
   - Menu navigasi

**Test Fitur:**
- ✅ Dashboard → Lihat data absensi
- ✅ Data Santri → Tambah/Edit/Hapus santri
- ✅ Laporan → Filter dan export data

---

### STEP 6: Setup ESP32

**Install Arduino IDE:**
1. Download dari: https://www.arduino.cc/en/software
2. Install Arduino IDE

**Install ESP32 Board:**
1. Buka Arduino IDE
2. File → Preferences
3. Di "Additional Board Manager URLs", tambahkan:
   ```
   https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
   ```
4. Tools → Board → Boards Manager
5. Cari "ESP32" dan install

**Install Library MFRC522:**
1. Sketch → Include Library → Manage Libraries
2. Cari "MFRC522"
3. Install library by GithubCommunity

**Upload Code ke ESP32:**
1. Buka file `esp32_example.ino`
2. Edit konfigurasi:
   ```cpp
   const char* ssid = "NAMA_WIFI_ANDA";
   const char* password = "PASSWORD_WIFI";
   const char* serverName = "http://192.168.1.XXX/cc/public/absensi.php";
   ```
3. Cek IP address komputer Anda:
   - Windows: `ipconfig` di CMD
   - Mac/Linux: `ifconfig` di Terminal
4. Tools → Board → ESP32 Dev Module
5. Tools → Port → Pilih COM Port ESP32
6. Klik Upload (→)

---

### STEP 7: Wiring RFID ke ESP32

**Koneksi Pin:**

| MFRC522 | ESP32    |
|---------|----------|
| SDA     | GPIO 5   |
| SCK     | GPIO 18  |
| MOSI    | GPIO 23  |
| MISO    | GPIO 19  |
| IRQ     | (kosong) |
| GND     | GND      |
| RST     | GPIO 22  |
| 3.3V    | 3.3V     |

**LED dan Buzzer (Opsional):**
- LED Hijau → GPIO 26 → GND (via resistor 220Ω)
- LED Merah → GPIO 27 → GND (via resistor 220Ω)
- Buzzer → GPIO 25 → GND

---

### STEP 8: Testing End-to-End

**Test 1: Registrasi RFID**
1. Buka Serial Monitor (115200 baud)
2. Dekatkan kartu RFID ke reader
3. Catat RFID ID yang muncul (contoh: `A1B2C3D4`)
4. Masuk ke website → Data Santri
5. Tambah santri baru dengan RFID ID tersebut

**Test 2: Absensi**
1. Dekatkan kartu RFID yang sudah terdaftar
2. ESP32 mengirim data ke server
3. Serial Monitor menampilkan: ✅ ABSENSI BERHASIL!
4. Refresh dashboard website
5. Data absensi baru muncul

**Test 3: RFID Tidak Terdaftar**
1. Dekatkan kartu RFID yang belum terdaftar
2. ESP32 menampilkan: ❌ RFID TIDAK TERDAFTAR
3. LED merah menyala

---

## 🔧 Troubleshooting

### ❌ Error: Database connection failed

**Solusi:**
1. Pastikan MySQL running di XAMPP
2. Cek `config/database.php` - pastikan username/password benar
3. Cek database `db_ngaji` sudah di-import

### ❌ Tailwind CSS tidak muncul

**Solusi:**
1. Compile Tailwind: `npm run build:css`
2. Atau gunakan CDN yang sudah ada di `public/css/tailwind.css`
3. Clear browser cache (Ctrl+F5)

### ❌ ESP32 tidak bisa upload

**Solusi:**
1. Install driver CH340/CP2102 untuk ESP32
2. Cek kabel USB (gunakan kabel data, bukan charging only)
3. Tekan dan tahan tombol BOOT saat upload

### ❌ RFID tidak terbaca

**Solusi:**
1. Cek wiring - pastikan semua pin terhubung dengan benar
2. Cek supply 3.3V (JANGAN gunakan 5V!)
3. Test dengan contoh sketch MFRC522
4. Cek jarak kartu (optimal 1-3 cm)

### ❌ ESP32 tidak konek WiFi

**Solusi:**
1. Pastikan SSID dan password benar
2. ESP32 hanya support WiFi 2.4GHz (tidak support 5GHz)
3. Cek Serial Monitor untuk error message
4. Pastikan WiFi tidak menggunakan captive portal

### ❌ ESP32 tidak bisa kirim data ke server

**Solusi:**
1. Pastikan ESP32 dan komputer di jaringan yang sama
2. Cek IP address server (gunakan `ipconfig`/`ifconfig`)
3. Test akses endpoint via browser: `http://192.168.1.XXX/cc/public/absensi.php`
4. Disable firewall sementara untuk testing
5. Cek Serial Monitor untuk HTTP response code

---

## 📊 Test Data

Database sudah include 5 santri dummy:

| Nama           | RFID ID  | Kelas         |
|----------------|----------|---------------|
| Ahmad Fauzi    | A1B2C3D4 | Kelas Iqro 1  |
| Fatimah Zahra  | E5F6G7H8 | Kelas Iqro 2  |
| Muhammad Rizki | I9J0K1L2 | Kelas Iqro 3  |
| Aisyah Nur     | M3N4O5P6 | Kelas Al-Quran|
| Umar Abdullah  | Q7R8S9T0 | Kelas Al-Quran|

**Gunakan RFID ID ini untuk testing ESP32!**

---

## 🚀 Production Deployment

**Untuk deployment ke server production:**

1. **Security:**
   - Ganti password database
   - Tambahkan authentication/login system
   - Enable HTTPS (SSL certificate)
   - Implement CSRF protection
   - Rate limiting untuk API endpoint

2. **Performance:**
   - Compile Tailwind untuk production (minified)
   - Enable OPcache untuk PHP
   - Optimize MySQL queries
   - Add database indexing

3. **Monitoring:**
   - Enable error logging
   - Setup backup database otomatis
   - Monitor server resources
   - Log absensi activity

---

## 📞 Butuh Bantuan?

Jika masih ada masalah:
1. Cek file `logs/absensi_log.txt` untuk debug
2. Cek Serial Monitor ESP32 untuk error
3. Cek console browser (F12) untuk error JavaScript
4. Buka issue di GitHub repository

---

**Good luck! 🎉**
