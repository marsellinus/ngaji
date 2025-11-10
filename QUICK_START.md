# 🚀 Quick Start Guide - Admin System

## 📌 Default Accounts

### Superadmin
```
URL: http://localhost/cc/public/login.php
Username: admin
Password: admin123
```

### Operator
```
URL: http://localhost/cc/public/login.php
Username: operator
Password: admin123
```

⚠️ **GANTI PASSWORD SETELAH LOGIN PERTAMA!**

---

## 🎯 Quick Access Menu

### 📊 Dashboard
- **URL:** `/public/index.php`
- **Access:** All users
- **Features:** Statistik, absensi hari ini, chart

### 👥 Data Santri
- **URL:** `/public/tambah_santri.php`
- **Access:** All users
- **Features:** Add, edit, delete, search santri

### 📈 Laporan
- **URL:** `/public/laporan.php`
- **Access:** All users
- **Features:** Filter laporan, export data

### 🔐 Admin Users (Admin Only)
- **URL:** `/public/admin_users.php`
- **Access:** Admin, Superadmin
- **Features:** CRUD admin users, reset password

### 📜 Activity Log (Admin Only)
- **URL:** `/public/activity_logs.php`
- **Access:** Admin, Superadmin
- **Features:** View logs, filter by user/type/date

### ⚙️ Pengaturan (Superadmin Only)
- **URL:** `/public/settings.php`
- **Access:** Superadmin only
- **Features:** System config, view info sistem

### 👤 Profil
- **URL:** `/public/profile.php`
- **Access:** All users
- **Features:** Edit profile, change password

---

## 🔑 Common Tasks

### 1️⃣ Login
1. Buka `http://localhost/cc/public/login.php`
2. Masukkan username & password
3. Klik **Login**
4. Redirect ke dashboard

### 2️⃣ Ganti Password (First Login)
1. Klik nama user (pojok kanan atas)
2. Pilih **Profil**
3. Scroll ke form **Ganti Password**
4. Isi:
   - Password Lama: `admin123`
   - Password Baru: (min. 6 karakter)
   - Konfirmasi Password
5. Klik **Ubah Password**

### 3️⃣ Tambah Admin Baru (Admin/Superadmin)
1. Menu **Admin** → **Admin Users**
2. Isi form sebelah kiri:
   - Nama Lengkap
   - Username (unique)
   - Email
   - Password
   - Level (operator/admin/superadmin)
   - Status (active)
3. Klik **Simpan Admin**

### 4️⃣ Reset Password Admin (Admin/Superadmin)
1. Menu **Admin** → **Admin Users**
2. Cari user yang ingin direset
3. Klik tombol **Reset** (🔄)
4. Password baru akan muncul di alert
5. Catat dan berikan ke user

### 5️⃣ Lihat Activity Log (Admin/Superadmin)
1. Menu **Activity Log**
2. Optional: Gunakan filter
   - User: Pilih user tertentu
   - Tipe: Pilih activity type
   - Tanggal: Pilih tanggal
3. Klik **Filter** atau **Reset**

### 6️⃣ Ubah Pengaturan Sistem (Superadmin)
1. Menu **Pengaturan**
2. Tab **Pengaturan Umum**
3. Edit nilai yang ingin diubah
4. Klik **Simpan Pengaturan**

### 7️⃣ Logout
1. Klik nama user (pojok kanan atas)
2. Pilih **Logout**
3. Redirect ke login page

---

## 🎨 Access Level Guide

### 👤 Operator
**Can Access:**
- ✅ Dashboard
- ✅ Data Santri (add, edit, delete)
- ✅ Laporan
- ✅ Profil (edit sendiri)

**Cannot Access:**
- ❌ Admin Users
- ❌ Activity Log
- ❌ Pengaturan

### 👨‍💼 Admin
**Can Access:**
- ✅ Semua akses Operator
- ✅ Admin Users (CRUD)
- ✅ Activity Log (view & filter)
- ✅ Profil

**Cannot Access:**
- ❌ Pengaturan Sistem

### 👑 Superadmin
**Can Access:**
- ✅ **Full Access** ke semua fitur
- ✅ Admin Users
- ✅ Activity Log
- ✅ Pengaturan Sistem
- ✅ Semua menu

---

## 🛠️ Troubleshooting

### ❌ Tidak Bisa Login
**Solusi:**
1. Cek username & password
2. Pastikan status = 'active'
3. Clear browser cache/cookies
4. Hubungi admin untuk reset password

### ⏱️ Auto Logout Terlalu Cepat
**Solusi (Superadmin):**
1. Menu **Pengaturan**
2. Setting `auto_logout` → ubah durasi (dalam menit)
3. Default: 30 menit

### 🚫 Menu Tidak Muncul
**Penyebab:** Access level tidak cukup

**Solusi:**
- Operator: Hubungi admin untuk upgrade ke Admin
- Admin: Hubungi superadmin untuk upgrade ke Superadmin

### 🔒 Lupa Password
**Solusi:**
1. Hubungi Admin/Superadmin
2. Minta reset password
3. Login dengan password baru
4. Ganti password via Profil

---

## 📊 Dashboard Overview

### Statistics Cards
1. **Total Santri**: Jumlah total santri terdaftar
2. **Hadir Hari Ini**: Santri yang sudah absen hari ini
3. **Persentase Kehadiran**: % kehadiran hari ini
4. **Total Absensi Bulan Ini**: Total absensi bulan berjalan

### Absensi Hari Ini Table
- Menampilkan 10 absensi terbaru hari ini
- Kolom: Nama, Kelas, Waktu Absen, RFID ID

### Chart (Coming Soon)
- Grafik kehadiran per hari
- Grafik per kelas
- Trend kehadiran

---

## 🎯 Best Practices

### 🔐 Security
1. ✅ **Ganti password default** ASAP
2. ✅ **Gunakan password kuat** (min. 12 karakter)
3. ✅ **Logout** setelah selesai
4. ✅ **Jangan share** password
5. ✅ **Update** PHP & MySQL berkala

### 👥 User Management
1. ✅ **Berikan access level** sesuai kebutuhan
2. ✅ **Non-aktifkan** user yang tidak digunakan
3. ✅ **Review** user list berkala
4. ✅ **Monitor** activity log

### ⚙️ System Maintenance
1. ✅ **Backup database** rutin (mingguan)
2. ✅ **Monitor** activity log
3. ✅ **Check** server resources
4. ✅ **Update** documentation

---

## 📞 Need Help?

### Documentation
- **Full Guide:** `ADMIN_SYSTEM_GUIDE.md`
- **Setup Guide:** `SETUP_GUIDE.md`
- **General Info:** `README.md`
- **Changelog:** `CHANGELOG.md`

### Contact
- **Email:** admin@example.com
- **GitHub:** (repository URL)

---

## 🔗 Quick Links

| Page | URL | Access |
|------|-----|--------|
| Login | `/public/login.php` | Public |
| Dashboard | `/public/index.php` | All |
| Data Santri | `/public/tambah_santri.php` | All |
| Laporan | `/public/laporan.php` | All |
| Admin Users | `/public/admin_users.php` | Admin+ |
| Activity Log | `/public/activity_logs.php` | Admin+ |
| Settings | `/public/settings.php` | Superadmin |
| Profile | `/public/profile.php` | All |
| Logout | `/public/logout.php` | All |

---

**Version:** 1.1.0  
**Last Updated:** June 2025  
**Status:** ✅ Stable
