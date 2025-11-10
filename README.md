# 🕌 Sistem Absensi Ngaji IoT

Sistem absensi berbasis ESP32 dan RFID untuk Lembaga Pendidikan Islam (TPQ/TPA)

## 🚀 Cara Install

### 1. Import Database

```sql
-- Di phpMyAdmin atau MySQL CLI
mysql -u root -p < database.sql
```

### 2. Akses Sistem

```
URL: http://localhost/cc/public/login.php
```

## 🔐 Login

### Admin (Kelola Sistem)
```
Username: admin
Password: admin123
```

### Santri (Lihat Log Absensi)
```
Username: ahmad / fatimah / rizki / aisyah / umar
Password: admin123

Santri hanya bisa login untuk lihat rekap absensi mereka sendiri
```

## 📋 Fitur

### Admin:
- ✅ Dashboard statistik
- ✅ Kelola data santri (tambah, edit, hapus)
- ✅ Set username/password untuk santri (santri bisa login lihat log)
- ✅ Lihat dan export laporan absensi
- ✅ Kelola admin lain
- ✅ Activity log
- ✅ Pengaturan sistem

### Santri:
- ✅ Login dengan username/password
- ✅ Lihat log absensi mereka sendiri
- ✅ Filter berdasarkan periode
- ✅ Lihat statistik kehadiran

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

## 🔧 ESP32 Setup

Lihat file: `esp32/esp32_absen_led.ino`

**Pin Configuration:**
```cpp
RFID: SS=7, RST=6
LED RGB: R=1, G=2, B=10
Buzzer: 3
```

**API Endpoint:**
```
POST http://[IP_SERVER]/cc/api/absen.php
Parameter: uid_kartu=[RFID_ID]
```

## 📂 Struktur

```
cc/
├── api/absen.php          → Endpoint untuk ESP32
├── config/database.php    → Config database
├── includes/
│   ├── auth.php           → Sistem login (admin & santri)
│   ├── functions.php      → Helper functions
│   └── header.php         → Header template
├── public/
│   ├── login.php          → Login page
│   ├── index.php          → Dashboard admin
│   ├── santri_log.php     → Log absensi santri
│   ├── laporan.php        → Laporan lengkap (admin)
│   ├── tambah_santri.php  → Kelola santri
│   └── admin_users.php    → Kelola admin
├── esp32/                 → Code ESP32
└── database.sql           → Database lengkap
```

## 💡 Penting!

- **Admin** = Kelola sistem (tambah santri, lihat semua data, settings)
- **Santri** = Login hanya untuk lihat log absensi mereka sendiri
- Username/password santri dibuat oleh admin saat tambah santri
- Santri yang tidak perlu login, biarkan username/password kosong

## 🔄 Update dari Versi Lama

Jika punya data lama, jalankan:

```sql
-- Tambah kolom username/password di tabel santri
ALTER TABLE santri ADD COLUMN username VARCHAR(50) UNIQUE AFTER rfid_id;
ALTER TABLE santri ADD COLUMN password VARCHAR(255) AFTER username;
ALTER TABLE santri ADD COLUMN status ENUM('aktif', 'nonaktif') DEFAULT 'aktif' AFTER keterangan;

-- Hapus kolom level di admin (tidak dipakai lagi)
ALTER TABLE admin DROP COLUMN level;
```

## 📞 Support

Jika ada error:
1. Cek `logs/error.log`
2. Pastikan database sudah di-import
3. Cek koneksi di `config/database.php`

---

**Tech Stack**: ESP32-C6, PHP, MySQL, Tailwind CSS  
**Database**: db_ngaji (4 tables)  
**Auth**: 2 tipe user (Admin & Santri)
