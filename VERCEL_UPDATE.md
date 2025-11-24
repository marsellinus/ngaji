# ✅ Vercel Structure Update - DONE!

## 🎯 Perubahan yang Sudah Dilakukan

Struktur project sudah disesuaikan mengikuti template [putuwaw/php-app-vercel](https://github.com/putuwaw/php-app-vercel):

### 1️⃣ Struktur Folder

**BEFORE** (Local XAMPP):
```
cc/
├── index.php          # Root entry point
├── public/            # Semua PHP & static files
│   ├── index.php      # Dashboard admin
│   ├── login.php
│   ├── css/
│   └── img/
└── api/
    └── absen.php      # ESP32 endpoint
```

**AFTER** (Vercel Compatible):
```
cc/
├── api/               # ✅ Semua PHP files (serverless functions)
│   ├── index.php      # Dashboard (root → /)
│   ├── login.php      # Login page (/login)
│   ├── absen.php      # ESP32 API (/api/absen.php)
│   └── ...
├── assets/            # ✅ Static files only (CSS, JS, images)
│   ├── css/
│   ├── js/
│   └── img/
├── config/
├── includes/
├── vercel.json        # ✅ Routing configuration
├── composer.json      # ✅ PHP dependencies
└── .vercelignore      # ✅ Exclude files
```

### 2️⃣ File Updates

| File | Changes |
|------|---------|
| `vercel.json` | ✅ Updated to template structure with version 2, clean routes |
| `api/index.php` | ✅ Created - Main dashboard entry point |
| `api/*.php` | ✅ All PHP files moved from `public/` to `api/` |
| `assets/` | ✅ Renamed from `public/`, contains only static files |
| `api/santri_log.php` | ✅ Updated CSS path: `/assets/css/custom.css` |
| `.vercelignore` | ✅ Created - Exclude logs, .md files, .ino files |
| `composer.json` | ✅ Already configured (no autoload needed for Vercel) |
| `DEPLOY_VERCEL.md` | ✅ Updated with new structure documentation |

### 3️⃣ Routing Configuration

`vercel.json` routes:
```json
{
  "version": 2,
  "functions": {
    "api/*.php": { "runtime": "vercel-php@0.6.0" }
  },
  "routes": [
    { "src": "/", "dest": "/api/index.php" },          // Dashboard
    { "src": "/login", "dest": "/api/login.php" },     // Login
    { "src": "/api/(.*)", "dest": "/api/$1" },         // API endpoints
    { "src": "/assets/(.*)", "dest": "/assets/$1" }    // Static files
  ]
}
```

**Clean URLs:**
- `https://domain.vercel.app/` → Dashboard (api/index.php)
- `https://domain.vercel.app/login` → Login page
- `https://domain.vercel.app/api/absen.php` → ESP32 endpoint
- `https://domain.vercel.app/assets/css/custom.css` → Static CSS

---

## 🚀 Next Steps

Ikuti **DEPLOY_VERCEL.md** untuk deploy:

### Step 1: Setup Database
```bash
# PlanetScale (Recommended)
1. Sign up → https://planetscale.com
2. Create database: ngaji-absensi
3. Import database.sql
4. Copy connection string
```

### Step 2: Push to GitHub
```bash
git add .
git commit -m "Update Vercel structure - template compatible"
git push origin main
```

### Step 3: Deploy to Vercel
```bash
# Via Dashboard (Recommended)
1. Login Vercel → https://vercel.com
2. Import GitHub repo: marsellinus/ngaji
3. Set Environment Variables (lihat DEPLOY_VERCEL.md)
4. Deploy!

# Or via CLI
vercel login
vercel
vercel env add DB_HOST
vercel env add DB_USER
vercel env add DB_PASS
vercel env add DB_NAME
vercel env add DB_PORT
vercel env add VERCEL
vercel --prod
```

### Step 4: Update ESP32
```cpp
// Change URL in esp32_absensi.ino
const char* serverURL = "https://your-project.vercel.app/api/absen.php";
```

### Step 5: Test
```bash
# Test API
curl https://your-project.vercel.app/api/absen.php

# Test Login
https://your-project.vercel.app/login
# admin / admin123
```

---

## ✨ Benefits

✅ **Clean URLs** - Tanpa `.php` extension di browser  
✅ **Serverless** - Auto-scaling, no server management  
✅ **Fast** - Global CDN, minimal cold start  
✅ **Secure** - Environment variables, HTTPS default  
✅ **Template-based** - Mengikuti best practice Vercel PHP  

---

## 📚 Resources

- **Template**: [putuwaw/php-app-vercel](https://github.com/putuwaw/php-app-vercel)
- **Vercel Docs**: https://vercel.com/docs
- **Vercel PHP**: https://github.com/vercel-community/php
- **Deploy Guide**: [DEPLOY_VERCEL.md](DEPLOY_VERCEL.md)

---

**Status**: ✅ **READY FOR DEPLOYMENT!**
