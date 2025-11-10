# 🔐 Admin System Documentation

## Overview

Sistem Admin & Authentication untuk Absensi Ngaji IoT menggunakan **role-based access control** dengan 3 level user:
- **Operator**: Akses dasar (dashboard, santri, absensi, laporan)
- **Admin**: Akses operator + manajemen user + activity log
- **Superadmin**: Full access + settings sistem

## 📋 Default Accounts

### Superadmin Account
```
Username: admin
Password: admin123
Level: superadmin
```

### Operator Account
```
Username: operator
Password: admin123
Level: operator
```

⚠️ **PENTING:** Segera ganti password default setelah login pertama!

## 🔑 Authentication System

### Features
- **Password Hashing**: Menggunakan `password_hash()` dengan bcrypt
- **Session Management**: Session-based authentication
- **Auto Logout**: Otomatis logout setelah 30 menit tidak aktif
- **CSRF Protection**: Token validation untuk form submission
- **Activity Logging**: Tracking semua aktivitas user
- **IP Address Logging**: Mencatat IP address untuk audit

### Login Process
1. User mengakses halaman login (`/public/login.php`)
2. Input username & password
3. Sistem validasi credentials dari database
4. Generate session dan redirect ke dashboard
5. Log aktivitas login

### Logout Process
1. User klik tombol logout
2. Destroy session
3. Log aktivitas logout
4. Redirect ke halaman login

### Session Timeout
- Default: **30 menit** tidak aktif
- Dapat dikonfigurasi via settings (key: `auto_logout`)
- Otomatis cek setiap request ke halaman protected

## 👥 User Management

### Access Levels Comparison

| Feature | Operator | Admin | Superadmin |
|---------|----------|-------|------------|
| Dashboard | ✅ | ✅ | ✅ |
| Data Santri | ✅ | ✅ | ✅ |
| Laporan | ✅ | ✅ | ✅ |
| Kelola Admin | ❌ | ✅ | ✅ |
| Activity Log | ❌ | ✅ | ✅ |
| Settings | ❌ | ❌ | ✅ |

### Add New Admin User

**Access Required:** Admin atau Superadmin

**Steps:**
1. Login sebagai Admin/Superadmin
2. Buka menu **Admin** → **Admin Users**
3. Isi form di sebelah kiri:
   - Nama Lengkap
   - Username (unique)
   - Email
   - Password (min. 6 karakter)
   - Level (operator/admin/superadmin)
   - Status (active/inactive)
4. Klik **Simpan Admin**

**Validations:**
- Username harus unique
- Email harus valid format
- Password minimal 6 karakter
- Semua field wajib diisi

### Edit Admin User

**Steps:**
1. Klik tombol **Edit** pada user yang ingin diedit
2. Form otomatis terisi dengan data existing
3. Ubah data yang diperlukan
4. Klik **Update Admin**

**Notes:**
- Password optional saat edit (kosongkan jika tidak ingin ganti)
- Username harus tetap unique
- Tidak bisa edit status dan level user sendiri

### Delete Admin User

**Steps:**
1. Klik tombol **Hapus** pada user yang ingin dihapus
2. Konfirmasi dialog akan muncul
3. Klik **OK** untuk konfirmasi

**Restrictions:**
- ❌ Tidak bisa hapus user sendiri
- ❌ Operator tidak bisa hapus siapapun
- ✅ Admin bisa hapus operator
- ✅ Superadmin bisa hapus semua

### Reset Password

**Steps:**
1. Klik tombol **Reset** pada user yang passwordnya ingin direset
2. Password baru akan di-generate otomatis
3. Password baru ditampilkan di alert
4. Catat password baru dan berikan ke user

**Auto-generated Password:**
- Format: `user_[random 6 digit]`
- Contoh: `user_583921`

## 📊 Activity Log

### Access Required
Admin atau Superadmin

### Features
- **View All Activities**: Semua aktivitas sistem
- **Filter by User**: Filter berdasarkan user tertentu
- **Filter by Type**: Filter berdasarkan tipe aktivitas
- **Filter by Date**: Filter berdasarkan tanggal
- **Pagination**: 50 record per halaman
- **Statistics**: Total aktivitas hari ini, minggu ini, bulan ini

### Activity Types

| Activity Type | Description |
|--------------|-------------|
| `login` | User login ke sistem |
| `logout` | User logout dari sistem |
| `create_santri` | Menambah data santri baru |
| `update_santri` | Mengupdate data santri |
| `delete_santri` | Menghapus data santri |
| `create_admin` | Menambah user admin baru |
| `update_admin` | Mengupdate data admin |
| `delete_admin` | Menghapus user admin |
| `reset_password` | Reset password admin |
| `update_profile` | Update profil pribadi |
| `change_password` | Ganti password |
| `update_settings` | Update pengaturan sistem |

### View Activity Log

**Steps:**
1. Login sebagai Admin/Superadmin
2. Buka menu **Activity Log**
3. Gunakan filter untuk mempersempit pencarian:
   - Pilih user (opsional)
   - Pilih tipe aktivitas (opsional)
   - Pilih tanggal (opsional)
4. Klik **Filter**

**Export to CSV/Excel:**
*(Coming soon)*

## ⚙️ System Settings

### Access Required
**Superadmin only**

### Available Settings

| Setting Key | Type | Description | Default |
|------------|------|-------------|---------|
| `site_name` | text | Nama aplikasi | Absensi Ngaji |
| `timezone` | text | Timezone server | Asia/Jakarta |
| `auto_logout` | number | Auto logout (minutes) | 30 |
| `absensi_start` | time | Jam mulai absensi | 07:00:00 |
| `absensi_end` | time | Jam akhir absensi | 20:00:00 |
| `maintenance_mode` | boolean | Mode maintenance | 0 (Off) |
| `allow_duplicate_absensi` | boolean | Izinkan absensi ganda | 0 (No) |
| `max_absensi_per_day` | number | Max absensi per hari | 3 |
| `email_notifications` | boolean | Notifikasi email | 0 (Off) |
| `admin_email` | text | Email admin | admin@example.com |

### Edit Settings

**Steps:**
1. Login sebagai Superadmin
2. Buka menu **Pengaturan**
3. Tab **Pengaturan Umum** sudah terbuka
4. Edit value yang ingin diubah
5. Klik **Simpan Pengaturan**

**Notes:**
- Changes apply immediately setelah save
- Activity log akan mencatat perubahan
- Beberapa settings memerlukan restart aplikasi

### View System Info

Tab **Info Sistem** menampilkan:
- PHP Version
- Server Software
- MySQL Version
- Max Upload Size
- Database Info (name, host, charset)
- Statistics (total santri, absensi, admin, activity)

## 👤 User Profile

### Access
Semua level user (operator, admin, superadmin)

### Features

#### 1. Edit Profile
**Fields:**
- Nama Lengkap
- Username (must be unique)
- Email

**Steps:**
1. Klik nama user di pojok kanan atas
2. Pilih **Profil**
3. Edit data di form **Edit Profil**
4. Klik **Simpan Perubahan**

#### 2. Change Password
**Fields:**
- Password Lama (required)
- Password Baru (min. 6 karakter)
- Konfirmasi Password Baru

**Steps:**
1. Scroll ke form **Ganti Password**
2. Masukkan password lama
3. Masukkan password baru
4. Konfirmasi password baru
5. Klik **Ubah Password**

**Password Requirements:**
- Minimal 6 karakter
- Password lama harus benar
- Password baru dan konfirmasi harus sama

#### 3. View Activity History
Sidebar profil menampilkan:
- Total aktivitas user
- 10 aktivitas terakhir dengan timestamp

## 🔒 Security Best Practices

### 1. Password Management
- ✅ Gunakan password minimal 12 karakter
- ✅ Kombinasi huruf besar, kecil, angka, simbol
- ✅ Ganti password secara berkala (3-6 bulan)
- ❌ Jangan share password
- ❌ Jangan gunakan password yang sama di multiple account

### 2. Account Security
- ✅ Logout setelah selesai menggunakan sistem
- ✅ Jangan save password di browser
- ✅ Gunakan komputer pribadi atau trusted device
- ✅ Non-aktifkan user yang sudah tidak digunakan
- ❌ Jangan login di komputer publik

### 3. Access Control
- ✅ Berikan access level sesuai kebutuhan (principle of least privilege)
- ✅ Review user list secara berkala
- ✅ Hapus user yang sudah tidak aktif
- ✅ Monitor activity log untuk suspicious activity

### 4. Database Security
- ✅ Gunakan password MySQL yang kuat
- ✅ Backup database secara berkala
- ✅ Jangan expose phpMyAdmin ke public
- ✅ Update PHP dan MySQL ke versi terbaru

## 🐛 Troubleshooting

### 1. Tidak Bisa Login
**Possible Causes:**
- Username atau password salah
- Account inactive (status = 'inactive')
- Session error

**Solutions:**
- Cek username dan password
- Hubungi admin untuk cek status account
- Clear browser cookies
- Reset password via admin

### 2. Session Timeout Terlalu Cepat
**Cause:**
Auto logout setting terlalu pendek

**Solution:**
Superadmin bisa ubah setting `auto_logout` di menu Pengaturan

### 3. Tidak Bisa Akses Menu Tertentu
**Cause:**
Access level tidak cukup

**Solution:**
- Cek access level Anda (operator/admin/superadmin)
- Hubungi superadmin untuk upgrade access level

### 4. Activity Log Tidak Tercatat
**Possible Causes:**
- Tabel `activity_log` error
- Insert permission error

**Solutions:**
- Cek database connection
- Cek table structure
- Cek MySQL user permissions

### 5. Lupa Password
**Solution:**
1. Hubungi admin/superadmin
2. Minta reset password
3. Login dengan password baru
4. Ganti password via menu Profile

## 📖 API Reference

### Authentication Functions

Tersedia di `includes/auth.php`:

```php
// Login user
login($conn, $username, $password)

// Logout user
logout($conn)

// Check if user logged in
isLoggedIn()

// Require login (redirect if not logged in)
requireLogin()

// Check access level
checkAccess($requiredLevel)

// Require specific access level
requireAccess($requiredLevel)

// Log activity
logActivity($conn, $adminId, $activityType, $description)

// Get setting value
getSetting($conn, $key, $default = '')

// Update setting value
updateSetting($conn, $key, $value)

// Hash password
hashPassword($password)

// Check session timeout
checkSessionTimeout()
```

### Usage Examples

**Protect a page (require login):**
```php
require_once '../includes/auth.php';
requireLogin();
```

**Protect a page (require admin level):**
```php
require_once '../includes/auth.php';
requireAccess('admin');
```

**Check access in template:**
```php
<?php if (checkAccess('superadmin')): ?>
    <!-- Superadmin only content -->
<?php endif; ?>
```

**Log custom activity:**
```php
logActivity($conn, $_SESSION['admin_id'], 'custom_action', 'User melakukan aksi X');
```

## 🔄 Database Schema

### Table: `admin`

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary key |
| username | VARCHAR(50) | Unique username |
| password | VARCHAR(255) | Hashed password |
| nama_lengkap | VARCHAR(100) | Full name |
| email | VARCHAR(100) | Email address |
| level | ENUM | operator/admin/superadmin |
| status | ENUM | active/inactive |
| last_login | DATETIME | Last login timestamp |
| created_at | TIMESTAMP | Created timestamp |
| updated_at | TIMESTAMP | Updated timestamp |

### Table: `activity_log`

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary key |
| admin_id | INT(11) | Foreign key to admin |
| activity_type | VARCHAR(50) | Activity type |
| description | TEXT | Activity description |
| ip_address | VARCHAR(45) | IP address |
| created_at | TIMESTAMP | Activity timestamp |

### Table: `settings`

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary key |
| setting_key | VARCHAR(50) | Unique setting key |
| setting_value | TEXT | Setting value |
| setting_type | VARCHAR(20) | text/number/boolean/time |
| description | VARCHAR(255) | Setting description |
| updated_at | TIMESTAMP | Last update |

## 📞 Support

Untuk pertanyaan atau issue terkait admin system, silakan hubungi:
- Email: admin@example.com
- GitHub Issues: (repository URL)

---

**Last Updated:** June 2025
**Version:** 1.0.0
