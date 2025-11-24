# 🚀 Deploy ke Vercel - Panduan Lengkap

## 📂 Struktur Project (Vercel Compatible)

Project ini sudah disesuaikan dengan struktur Vercel serverless:

```
cc/
├── api/                    # 🔹 Semua PHP files (serverless functions)
│   ├── index.php          # Dashboard → https://domain.vercel.app/
│   ├── login.php          # Login → https://domain.vercel.app/login
│   ├── absen.php          # API Endpoint → /api/absen.php (ESP32)
│   ├── santri_log.php     # Log Santri → /santri_log
│   ├── laporan.php        # Laporan → /laporan
│   └── ...                # Other pages (settings, profile, etc)
├── assets/                 # 🎨 Static files (CSS, JS, images)
│   ├── css/custom.css     # Custom styles
│   ├── js/               
│   └── img/              
├── config/                 # ⚙️ Configuration
│   └── database.php       # Auto-detect local/Vercel environment
├── includes/               # 📦 Helper functions
│   ├── auth.php           # Authentication functions
│   └── functions.php      # Utility functions
├── vercel.json            # 🔧 Vercel routing configuration
├── composer.json          # 📦 PHP dependencies
└── .vercelignore          # 🚫 Exclude files from deployment
```

**✨ Perubahan dari struktur lama:**
- ❌ `public/` folder → ✅ `api/` (PHP files) + `assets/` (static)
- ❌ Root `index.php` → ✅ `api/index.php` + clean URL routing
- ✅ Template based on: [putuwaw/php-app-vercel](https://github.com/putuwaw/php-app-vercel)

## 📋 Prerequisites

- ✅ Akun GitHub (untuk import repository)
- ✅ Akun Vercel (gratis di https://vercel.com)
- ✅ Database MySQL/Postgres (pilih salah satu opsi di bawah)

---

## 🗄️ Step 1: Setup Database (Pilih Salah Satu)

### Option A: PlanetScale (Recommended - MySQL Compatible)

**Keunggulan**:
- ✅ **Gratis** hingga 5GB storage
- ✅ **MySQL compatible** (bisa import database.sql langsung)
- ✅ **Auto-scaling** & serverless
- ✅ **Global CDN**

**Setup**:
1. Daftar di https://planetscale.com/
2. Create New Database: `ngaji-absensi`
3. Click **Connect** → Copy credentials:
   ```
   Host: aws.connect.psdb.cloud
   Username: xxxxxxxxxxxx
   Password: pscale_pw_xxxxxxxxxxxx
   Database: ngaji-absensi
   Port: 3306
   ```
4. Install PlanetScale CLI (optional):
   ```bash
   # Windows (Scoop)
   scoop install pscale
   
   # Login
   pscale auth login
   ```
5. Import Database:
   ```bash
   # Connect ke database
   pscale shell ngaji-absensi main
   
   # Atau via MySQL client
   mysql -h aws.connect.psdb.cloud -u [username] -p[password] --ssl-mode=VERIFY_IDENTITY --ssl-ca=/path/to/ca.pem [database_name] < database.sql
   ```

### Option B: Railway (Easy Setup)

**Keunggulan**:
- ✅ **Gratis** $5 credit/bulan (cukup untuk project kecil)
- ✅ **One-click MySQL** provision
- ✅ **Auto-backups**

**Setup**:
1. Daftar di https://railway.app/
2. New Project → Provision MySQL
3. Copy credentials dari MySQL service
4. Connect via MySQL Workbench atau CLI untuk import `database.sql`

### Option C: Aiven (Free Tier)

**Keunggulan**:
- ✅ **Gratis** untuk testing (limited resources)
- ✅ **Multi-cloud** (AWS/GCP/Azure)

**Setup**:
1. Daftar di https://aiven.io/
2. Create Service → MySQL
3. Pilih Free Plan
4. Copy connection string
5. Import database

### Option D: Vercel Postgres (Beta)

**Note**: Vercel Postgres adalah **PostgreSQL**, bukan MySQL. Anda perlu:
- Convert schema dari MySQL ke PostgreSQL
- Update query yang spesifik MySQL

**Setup**:
1. Vercel Dashboard → Storage → Create Database → Postgres
2. Copy credentials
3. Convert & import schema

---

## 📦 Step 2: Install Composer Dependencies (Optional)

```bash
# Install Composer (jika belum ada)
# Download: https://getcomposer.org/download/

# Di folder project
cd D:\xampp-port\htdocs\cc

# Install dependencies (saat ini hanya autoload)
composer install

# Atau skip step ini, Vercel akan run composer install otomatis
```

## 📤 Step 3: Push ke GitHub

```bash
# Di folder project
cd D:\xampp-port\htdocs\cc

# Init git (jika belum)
git init
git add .
git commit -m "Initial commit for Vercel deployment"

# Create repository di GitHub: github.com/marsellinus/ngaji
# Link remote
git remote add origin https://github.com/marsellinus/ngaji.git
git branch -M main
git push -u origin main
```

---

## 🌐 Step 4: Deploy ke Vercel

### Via Vercel Dashboard (Recommended)

1. **Login Vercel**: https://vercel.com/login

2. **Import Project**: 
   - Click **Add New** → **Project**
   - Import Git Repository → Pilih `marsellinus/ngaji`

3. **Configure Project**:
   - Framework Preset: **Other**
   - Root Directory: `./` (default)
   - Build Command: `composer install` (Vercel akan auto-detect)
   - Output Directory: (kosongkan)
   - Install Command: `composer install` (auto)

4. **Environment Variables**:
   Click **Environment Variables** → Add untuk **Production**, **Preview**, dan **Development**:
   
   | Name | Value | Example |
   |------|-------|---------|
   | `DB_HOST` | Database host | `aws.connect.psdb.cloud` |
   | `DB_USER` | Database user | `your-username` |
   | `DB_PASS` | Database password | `pscale_pw_xxxxx` |
   | `DB_NAME` | Database name | `ngaji-absensi` |
   | `DB_PORT` | Database port | `3306` |
   | `VERCEL` | Flag | `1` |

   **Tips**: 
   - Gunakan **Encrypted** untuk DB_PASS
   - Apply ke semua environments (Production, Preview, Development)

5. **Deploy**: Click **Deploy** dan tunggu ~2-3 menit

6. **Setelah Deploy Sukses**:
   - Vercel akan memberikan URL: `https://your-project.vercel.app`
   - Test endpoint API: `https://your-project.vercel.app/api/absen.php`
   - Test login: `https://your-project.vercel.app/`

### Via Vercel CLI (Alternative)

```bash
# Install Node.js dan npm jika belum ada
# Download: https://nodejs.org/

# Install Vercel CLI
npm install -g vercel

# Login
vercel login
# Browser akan terbuka, login dengan GitHub

# Deploy dari project folder
cd D:\xampp-port\htdocs\cc
vercel

# Follow prompts:
# ? Set up and deploy "D:\xampp-port\htdocs\cc"? [Y/n] Y
# ? Which scope? [Pilih username Anda]
# ? Link to existing project? [Y/n] n
# ? What's your project's name? ngaji-absensi
# ? In which directory is your code located? ./
# ? Want to modify these settings? [y/N] N

# Vercel akan deploy dan memberikan Preview URL
# Contoh: https://ngaji-absensi-xxx.vercel.app

# Set environment variables untuk production
vercel env add DB_HOST production
# Paste: aws.connect.psdb.cloud

vercel env add DB_USER production
# Paste: your-username

vercel env add DB_PASS production
# Paste: your-password

vercel env add DB_NAME production
# Paste: ngaji-absensi

vercel env add DB_PORT production
# Paste: 3306

vercel env add VERCEL production
# Paste: 1

# Deploy ke production dengan env vars
vercel --prod

# Setelah deploy sukses, akan muncul:
# ✅ Production: https://ngaji-absensi.vercel.app [copied to clipboard]
```

---

## 🔧 Step 5: Update ESP32 Code

Edit `esp32_absensi.ino`:

```cpp
// PRODUCTION - Vercel URL
const char* serverURL = "https://ngaji-absensi.vercel.app/api/absen.php";
//                       ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//                       Ganti dengan URL Vercel Anda

// DEVELOPMENT - Local testing
// const char* serverURL = "http://10.225.159.41/cc/api/absen.php";

// CUSTOM DOMAIN (optional)
// const char* serverURL = "https://absensi.yourdomain.com/api/absen.php";
```

**Upload ke ESP32**:
1. Buka Arduino IDE
2. File → Open → `esp32_absensi.ino`
3. Tools → Board → ESP32C6 Dev Module
4. Tools → Port → Pilih COM port
5. Click Upload (→)
6. Tunggu hingga "Done uploading"
7. Open Serial Monitor (115200 baud)
8. Test scan kartu RFID

---

## ✅ Step 6: Test Deployment

### 1️⃣ Test API Endpoint (Health Check)

```bash
# Via Browser
https://ngaji-absensi.vercel.app/api/absen.php

# Via curl (Windows PowerShell)
curl https://ngaji-absensi.vercel.app/api/absen.php

# Expected Response:
{
  "status": "online",
  "message": "API Absensi ESP32 - Health Check OK",
  "server_time": "2025-11-24 15:30:00",
  "database": "connected",
  "stats": {
    "total_santri": 5,
    "absensi_hari_ini": 3
  }
}
```

### 2️⃣ Test POST Absensi

```bash
# Via curl (Windows PowerShell)
curl -X POST https://ngaji-absensi.vercel.app/api/absen.php `
  -H "Content-Type: application/x-www-form-urlencoded" `
  -d "uid_kartu=A1B2C3D4"

# Expected Response (Sukses):
{
  "status": "sukses",
  "message": "Absensi berhasil dicatat",
  "data": {
    "nama": "Ahmad Fauzi",
    "kelas": "Iqro 1",
    "uid_kartu": "A1B2C3D4",
    "waktu": "2025-11-24 15:35:00"
  }
}

# Expected Response (Sudah Absen):
{
  "status": "sudah_absen",
  "message": "Anda sudah melakukan absensi hari ini",
  "data": { ... }
}

# Expected Response (Tidak Terdaftar):
{
  "status": "gagal",
  "message": "Kartu RFID tidak terdaftar"
}
```

### 3️⃣ Test Website Login

1. Buka URL: `https://ngaji-absensi.vercel.app/`
2. Login Admin: `admin` / `admin123`
3. Check:
   - ✅ Dashboard load dengan benar
   - ✅ Statistik muncul (Total Santri, Kehadiran, dll)
   - ✅ Menu navigation berfungsi
   - ✅ Data Santri load dari database

### 4️⃣ Test ESP32 Integration

1. **Upload ESP32 Code** dengan URL Vercel
2. **Open Serial Monitor** (115200 baud)
3. **Expected Output**:
   ```
   ESP32-C6 RFID Absensi System
   ✅ WiFi Connected!
   IP Address: 192.168.1.100
   📡 Server: https://ngaji-absensi.vercel.app/api/absen.php
   ✅ System Ready!
   ```
4. **Scan Kartu RFID**
5. **Expected Output**:
   ```
   📇 Kartu terdeteksi!
   UID: A1B2C3D4
   📤 Sending to server...
   ✅ Response Code: 200
   📥 Response: {"status":"sukses","message":"Absensi berhasil dicatat",...}
   ✅ Absensi berhasil dicatat!
   ```
6. **Check Dashboard**: Refresh halaman, data absensi baru muncul

### 5️⃣ Test dari Multiple Devices

- ✅ **Desktop**: Buka di browser desktop
- ✅ **Mobile**: Buka di HP (responsive design)
- ✅ **ESP32**: Scan kartu dari hardware
- ✅ **Blynk**: Monitor dari Blynk app (jika aktif)

---

## 🐛 Troubleshooting

### Error: "Database connection failed"

**Solusi**:
```bash
# Cek environment variables di Vercel
vercel env ls

# Pastikan semua DB_* variables sudah diset
# Test koneksi database dari local
mysql -h [DB_HOST] -u [DB_USER] -p[DB_PASS] [DB_NAME]
```

### Error: "500 Internal Server Error"

**Solusi**:
```bash
# Check Vercel logs
vercel logs

# Atau di dashboard: Project → Deployments → [Latest] → Function Logs
```

### Error: "vercel-php not found"

**Solusi**:
- Vercel mungkin tidak auto-detect PHP
- Pastikan `vercel.json` sudah di-commit
- Re-deploy: `vercel --prod --force`

### Session tidak berfungsi

**Note**: Vercel serverless **tidak support PHP session tradisional** karena stateless.

**Solusi**:
1. **JWT Authentication** (Recommended):
   - Ganti session dengan JWT token
   - Store token di localStorage/cookie client
   
2. **Database Session**:
   - Store session di database
   - Session ID di cookie

3. **Vercel KV Storage** (Paid):
   - Store session di Redis-like KV

---

## 💰 Cost Estimation

### Free Tier (Cukup untuk project ini)

| Service | Free Quota | Limit |
|---------|------------|-------|
| **Vercel** | 100GB bandwidth/bulan | Unlimited projects |
| **PlanetScale** | 5GB storage, 1B row reads | 1 database |
| **Railway** | $5 credit/bulan | ~500 hours runtime |
| **Blynk** | 2 devices | Basic widgets |

**Total**: **$0/bulan** (dalam free tier)

### Paid (Jika butuh scale)

| Service | Price | Features |
|---------|-------|----------|
| **Vercel Pro** | $20/bulan | 1TB bandwidth, analytics |
| **PlanetScale Scaler** | $29/bulan | 10GB storage, backups |
| **Blynk Plus** | $6.99/bulan | 10 devices, analytics |

---

## 🔐 Security Checklist

- ✅ Ganti password default admin di database
- ✅ Set environment variables di Vercel (jangan hardcode)
- ✅ Enable SSL (Vercel otomatis HTTPS)
- ✅ Gunakan prepared statements (sudah implemented)
- ✅ Backup database regular (PlanetScale auto-backup)
- ✅ Monitor Vercel logs untuk suspicious activity

---

## 📊 Monitoring

### Vercel Dashboard
- Real-time function invocations
- Error tracking
- Bandwidth usage
- Response times

### PlanetScale Dashboard
- Database size
- Query insights
- Connection count
- Slow queries

### Blynk Dashboard (Optional)
- ESP32 online status
- Absensi real-time
- Daily statistics

---

## 🔄 Update & Rollback

### Update Website

```bash
git add .
git commit -m "Update feature X"
git push origin main

# Vercel auto-deploy dari GitHub
# Check: https://vercel.com/your-username/ngaji-absensi
```

### Rollback

```bash
# Via Vercel dashboard: Deployments → Previous → Promote to Production

# Atau via CLI
vercel rollback
```

---

## 🎯 Custom Domain (Optional)

1. **Buy Domain**: Niagahoster, Namecheap, dll
2. **Vercel Dashboard**: Project → Settings → Domains
3. **Add Domain**: `absensi.yourdomain.com`
4. **Set DNS**:
   ```
   Type: CNAME
   Name: absensi
   Value: cname.vercel-dns.com
   ```
5. **Verify**: Tunggu DNS propagation (~10 menit)

---

## 📞 Support

- **Vercel Docs**: https://vercel.com/docs
- **PlanetScale Docs**: https://planetscale.com/docs
- **GitHub Issues**: https://github.com/marsellinus/ngaji/issues

---

**Happy Deploying! 🚀**
