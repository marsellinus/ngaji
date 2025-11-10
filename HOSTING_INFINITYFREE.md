# 🌐 Cara Hosting di InfinityFree

## 📋 Persiapan

InfinityFree adalah hosting gratis dengan fitur:
- ✅ PHP & MySQL
- ✅ Unlimited bandwidth
- ✅ Free subdomain atau custom domain
- ✅ Control panel (cPanel-like)

---

## 🚀 Langkah-langkah Hosting

### 1. Daftar InfinityFree

1. Buka https://infinityfree.net/
2. Klik **"Sign Up"**
3. Isi form pendaftaran:
   - Email
   - Password
4. Verifikasi email
5. Login ke control panel

---

### 2. Buat Akun Hosting

1. Di dashboard, klik **"Create Account"**
2. Pilih subdomain atau domain:
   ```
   Contoh subdomain gratis:
   - absensi-ngaji.rf.gd
   - tpq-absensi.epizy.com
   - ngaji-online.great-site.net
   
   Atau pakai domain sendiri jika punya
   ```
3. Tunggu account dibuat (1-5 menit)

---

### 3. Setup Database MySQL

1. Buka **Control Panel** → **MySQL Databases**
2. **Create New Database**:
   ```
   Database Name: db_ngaji
   ```
   (akan jadi: epiz_12345678_db_ngaji)
3. **Create User**:
   ```
   Username: (buat username)
   Password: (buat password kuat)
   ```
4. **Add User to Database**:
   - Pilih user yang dibuat
   - Pilih database yang dibuat
   - Klik "Add"
   - Centang **ALL PRIVILEGES**
   - Klik "Make Changes"

5. **Catat informasi ini** (penting!):
   ```
   Database Host: sql123.infinityfreeapp.com (atau sql lainnya)
   Database Name: epiz_12345678_db_ngaji
   Database User: epiz_12345678_user
   Database Password: (password yang Anda buat)
   ```

---

### 4. Upload File

**Cara 1: File Manager (Recommended)**

1. Buka **Control Panel** → **Online File Manager**
2. Masuk ke folder **htdocs**
3. **Upload semua file** kecuali:
   - ❌ `.git/` folder
   - ❌ `logs/` folder
   - ❌ `README.md`, `*.md` files (opsional)
4. Upload file:
   - ✅ `index.php`
   - ✅ `database.sql`
   - ✅ Folder `public/`
   - ✅ Folder `api/`
   - ✅ Folder `config/`
   - ✅ Folder `includes/`
   - ✅ Folder `esp32/`
   - ✅ `.htaccess`

**Cara 2: FTP (Filezilla)**

1. Download Filezilla: https://filezilla-project.org/
2. Koneksi FTP (lihat di control panel):
   ```
   Host: ftpupload.net (atau ftp lainnya)
   Username: epiz_12345678
   Password: (password akun hosting)
   Port: 21
   ```
3. Upload semua file ke folder `/htdocs/`

---

### 5. Import Database

1. Buka **Control Panel** → **phpMyAdmin**
2. Klik database yang dibuat (epiz_12345678_db_ngaji)
3. Klik tab **"Import"**
4. **Choose File** → Pilih `database.sql`
5. Klik **"Go"**
6. Tunggu sampai selesai

⚠️ **Jika file terlalu besar**, split database:
```sql
-- Buat file database_struktur.sql (hanya CREATE TABLE)
-- Buat file database_data.sql (hanya INSERT)
-- Import satu per satu
```

---

### 6. Edit Config Database

1. Di File Manager, buka **config/database.php**
2. Edit dengan info database InfinityFree:

```php
<?php
/**
 * Database Configuration
 */

// Database credentials - EDIT INI!
$db_host = 'sql123.infinityfreeapp.com';  // Ganti dengan host dari InfinityFree
$db_user = 'epiz_12345678_user';          // Ganti dengan username database
$db_pass = 'PASSWORD_ANDA';                // Ganti dengan password database
$db_name = 'epiz_12345678_db_ngaji';      // Ganti dengan nama database

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");
?>
```

3. **Save** file

---

### 7. Update URL di ESP32 (Jika Pakai ESP32)

Edit file `esp32/esp32_absen_led.ino`:

```cpp
// SEBELUM (localhost)
const char* serverName = "http://192.168.1.100/cc/api/absen.php";

// SETELAH (InfinityFree)
const char* serverName = "http://absensi-ngaji.rf.gd/api/absen.php";
```

Upload ulang ke ESP32.

---

### 8. Test Website

Buka browser:
```
http://absensi-ngaji.rf.gd/
```

Atau:
```
http://absensi-ngaji.rf.gd/public/login.php
```

**Login dengan:**
```
Admin:
Username: admin
Password: admin123

Santri:
Username: ahmad
Password: admin123
```

---

## 🔧 Troubleshooting

### ❌ Error "Database connection failed"

**Solusi:**
1. Cek config/database.php sudah benar
2. Cek database sudah di-import
3. Cek user sudah ditambahkan ke database dengan privileges
4. Pastikan host database benar (biasanya sql1XX.infinityfreeapp.com)

### ❌ Error "404 Not Found"

**Solusi:**
1. Pastikan file di-upload ke folder `htdocs` (bukan `htdocs/cc`)
2. Cek struktur folder benar
3. Cek .htaccess sudah di-upload

### ❌ CSS Tidak Muncul / Tampilan Rusak

**Solusi:**
1. Pastikan ada koneksi internet (Tailwind CDN)
2. Cek folder `public/css/` sudah di-upload
3. Clear cache browser (Ctrl+F5)

### ❌ Error "Cannot modify header information"

**Solusi:**
1. Pastikan tidak ada spasi atau BOM di awal file PHP
2. Pastikan tidak ada `echo` sebelum `header()`
3. Edit dengan text editor yang support UTF-8 without BOM

### ❌ Login Tidak Berfungsi

**Solusi:**
1. Test hash password:
   - Buat file `test.php`:
   ```php
   <?php
   $password = 'admin123';
   $hash = '$2y$10$t8rQGkmSBAqCvQTCMMxU4ewUF5Lb3LPEwlLbURMEL/4LhiuuTkrHu';
   echo password_verify($password, $hash) ? 'OK' : 'GAGAL';
   ?>
   ```
   - Akses: http://absensi-ngaji.rf.gd/test.php
   - Jika GAGAL, generate hash baru di phpMyAdmin

### ❌ ESP32 Tidak Bisa Kirim Data

**Solusi:**
1. Cek URL sudah benar (http, bukan https)
2. InfinityFree kadang blok request dari IoT device
3. Alternatif: Pakai Heroku, Railway, atau VPS

---

## 🔒 Keamanan

### Penting untuk Hosting Publik:

1. **Ganti Password Default:**
   ```sql
   -- Di phpMyAdmin, jalankan:
   UPDATE admin SET password = '$2y$10$HASH_BARU' WHERE username = 'admin';
   ```

2. **Hapus File Test:**
   - Hapus test.php
   - Hapus semua file .md (README, GUIDE, dll)

3. **Proteksi Folder:**
   - Folder `config/` jangan bisa diakses langsung
   - Folder `includes/` jangan bisa diakses langsung
   - Sudah di-handle di .htaccess

4. **HTTPS (Optional):**
   - InfinityFree support SSL gratis
   - Aktifkan di control panel
   - Update ESP32 pakai `https://`

---

## 📊 Monitoring

### Cek Traffic & Error:

1. **Control Panel** → **Error Logs**
   - Lihat PHP errors
2. **Control Panel** → **Statistics**
   - Lihat visitor stats
3. **Activity Log** di aplikasi
   - Menu admin → Activity Log

---

## 🎯 Optimasi untuk InfinityFree

### Karena Resource Terbatas:

1. **Batasi Query Database:**
   - Tambah limit di laporan
   - Gunakan pagination

2. **Cache:**
   - Buat sistem cache sederhana
   - Cache statistik dashboard

3. **Compress File:**
   - Minify CSS
   - Compress images (jika ada)

4. **Cron Jobs:**
   - InfinityFree punya cron job gratis
   - Bisa auto-backup database

---

## 💰 Upgrade (Jika Perlu)

### Jika InfinityFree Tidak Cukup:

**Hosting Berbayar Murah:**
1. **Hostinger** (~Rp 20.000/bulan)
   - Support ESP32
   - Fast PHP
   - 24/7 support

2. **Niagahoster** (~Rp 10.000/bulan)
   - Support lokal Indonesia
   - cPanel
   - Fast response

3. **DigitalOcean** (~$5/bulan)
   - VPS full control
   - Support ESP32
   - Perlu setting sendiri

---

## 📞 Support InfinityFree

- Forum: https://forum.infinityfree.net/
- Knowledge Base: https://infinityfree.net/support/
- Response time: 1-2 hari

---

## ✅ Checklist Deploy

- [ ] Akun InfinityFree dibuat
- [ ] Database MySQL dibuat
- [ ] User database dibuat dengan privileges
- [ ] File di-upload ke htdocs
- [ ] config/database.php sudah diedit
- [ ] database.sql sudah di-import
- [ ] Test login admin: admin/admin123
- [ ] Test login santri: ahmad/admin123
- [ ] ESP32 URL sudah diupdate (jika pakai)
- [ ] Password default sudah diganti
- [ ] File test sudah dihapus

---

**Website siap online! 🎉**  
**Gratis selamanya di InfinityFree! 🚀**
