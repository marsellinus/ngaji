# 📁 Struktur Folder Project

## 🎯 Akses URL Sekarang Lebih Simple!

### URL Utama:
```
http://localhost/cc/
```
→ Otomatis redirect ke login atau dashboard tergantung status login

---

## 📂 Struktur Lengkap:

```
cc/                                    ← Root project
│
├── index.php                          ← Entry point (NEW!) - Auto redirect
├── database.sql                       ← Database lengkap
├── README.md                          ← Panduan utama
├── .htaccess                          ← Apache config
├── .gitignore                         ← Git ignore rules
│
├── api/                               ← API untuk ESP32
│   └── absen.php                      → Endpoint absensi RFID
│
├── config/                            ← Konfigurasi
│   └── database.php                   → Config database
│
├── includes/                          ← Library & Functions
│   ├── auth.php                       → Sistem login (admin & santri)
│   ├── functions.php                  → Helper functions
│   ├── header.php                     → Header template
│   └── footer.php                     → Footer template
│
├── public/                            ← Halaman-halaman utama
│   ├── login.php                      → Halaman login
│   ├── logout.php                     → Logout
│   │
│   ├── index.php                      → Dashboard admin
│   ├── laporan.php                    → Laporan lengkap (admin)
│   ├── tambah_santri.php              → Kelola santri (admin)
│   ├── admin_users.php                → Kelola admin (admin)
│   ├── activity_logs.php              → Activity log (admin)
│   ├── settings.php                   → Settings sistem (admin)
│   │
│   ├── santri_log.php                 → Log absensi santri
│   │
│   ├── css/
│   │   └── custom.css                 → Custom CSS
│   │
│   └── js/
│       └── script.js                  → JavaScript
│
├── esp32/                             ← Code Arduino
│   └── esp32_absen_led.ino            → Code ESP32-C6
│
└── logs/                              ← Log files (auto-generated)
    └── absensi_log.txt

```

---

## 🌐 URL Map:

### Root:
```
http://localhost/cc/                   → Auto redirect ke login/dashboard
```

### Admin URLs:
```
http://localhost/cc/public/login.php              → Login page
http://localhost/cc/public/index.php              → Dashboard
http://localhost/cc/public/laporan.php            → Laporan absensi
http://localhost/cc/public/tambah_santri.php      → Kelola santri
http://localhost/cc/public/admin_users.php        → Kelola admin
http://localhost/cc/public/activity_logs.php      → Activity log
http://localhost/cc/public/settings.php           → Settings
```

### Santri URLs:
```
http://localhost/cc/public/santri_log.php         → Log absensi santri
```

### API:
```
http://localhost/cc/api/absen.php                 → API untuk ESP32
```

---

## 🎯 Cara Kerja index.php di Root:

**index.php** sekarang ada di root folder dan otomatis redirect:

```php
// Jika belum login
→ Redirect ke: public/login.php

// Jika login sebagai admin
→ Redirect ke: public/index.php (Dashboard)

// Jika login sebagai santri
→ Redirect ke: public/santri_log.php (Log mereka)
```

### Keuntungan:
✅ URL lebih simple: `http://localhost/cc/` saja
✅ Tidak perlu ketik `/public/login.php` lagi
✅ Auto redirect berdasarkan status login
✅ Struktur tetap rapi, file PHP di folder `public/`

---

## 📦 File Penting untuk GitHub:

### Yang DI-UPLOAD:
✅ Semua file PHP
✅ database.sql
✅ esp32/ folder
✅ README.md
✅ .htaccess
✅ .gitignore

### Yang TIDAK DI-UPLOAD:
❌ logs/ (sudah di .gitignore)
❌ config/database.php dengan password asli (edit dulu!)
❌ .git/ (otomatis di-ignore)

---

## 🚀 Quick Start:

1. **Clone dari GitHub:**
   ```bash
   git clone https://github.com/USERNAME/absensi-ngaji-iot.git
   cd absensi-ngaji-iot
   ```

2. **Setup Database:**
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Edit Config:**
   ```bash
   # Edit config/database.php
   # Sesuaikan username, password database
   ```

4. **Akses:**
   ```
   http://localhost/absensi-ngaji-iot/
   ```

---

## 💡 Tips:

### Jika ingin URL lebih pendek lagi:

Rename folder `cc` jadi `absensi`:
```
d:\xampp-port\htdocs\absensi\
```

Akses jadi:
```
http://localhost/absensi/
```

### Atau pakai Virtual Host Apache:

Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    ServerName absensi.local
    DocumentRoot "D:/xampp-port/htdocs/cc"
    <Directory "D:/xampp-port/htdocs/cc">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Edit `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1    absensi.local
```

Akses jadi:
```
http://absensi.local/
```

---

**Struktur folder sekarang lebih rapi! ✅**  
**URL akses lebih simple! ✅**  
**Siap di-upload ke GitHub! 🚀**
