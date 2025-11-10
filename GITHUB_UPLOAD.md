# 📤 Cara Upload ke GitHub

## 🎯 Langkah-langkah:

### 1. Buat Repository Baru di GitHub

1. Buka https://github.com
2. Klik tombol **"+"** di kanan atas → **"New repository"**
3. Isi:
   - **Repository name**: `absensi-ngaji-iot`
   - **Description**: `Sistem Absensi Ngaji berbasis IoT ESP32 dan RFID untuk TPQ/TPA`
   - **Public** atau **Private** (pilih sesuai kebutuhan)
   - **JANGAN centang** "Initialize this repository with a README"
4. Klik **"Create repository"**

### 2. Upload dari Terminal

Setelah repository dibuat, GitHub akan tampilkan instruksi. Jalankan di terminal:

```powershell
cd d:\xampp-port\htdocs\cc

# Set remote repository (ganti USERNAME dengan username GitHub Anda)
git remote add origin https://github.com/USERNAME/absensi-ngaji-iot.git

# Rename branch ke main (opsional, sesuai standar baru GitHub)
git branch -M main

# Push ke GitHub
git push -u origin main
```

**Ganti USERNAME** dengan username GitHub Anda!

### 3. Jika Diminta Login

Jika diminta username/password:

**Untuk Windows:**
- GitHub sekarang pakai **Personal Access Token** bukan password
- Buat token di: https://github.com/settings/tokens
- Klik **"Generate new token (classic)"**
- Centang `repo` (full control)
- Copy token yang dihasilkan
- Paste sebagai password saat git push

### 4. Selesai!

Repository Anda sekarang sudah online di:
```
https://github.com/USERNAME/absensi-ngaji-iot
```

---

## 🔄 Update Repository Setelah Ada Perubahan

Setiap kali ada perubahan file:

```powershell
cd d:\xampp-port\htdocs\cc

# Add semua perubahan
git add .

# Commit dengan pesan
git commit -m "Deskripsi perubahan"

# Push ke GitHub
git push
```

---

## 📝 Contoh Commit Messages yang Baik

```bash
# Fitur baru
git commit -m "feat: tambah fitur login santri"

# Perbaikan bug
git commit -m "fix: perbaiki error hash password"

# Update dokumentasi
git commit -m "docs: update README dengan panduan lengkap"

# Perubahan kecil
git commit -m "chore: hapus file tidak terpakai"
```

---

## ⚠️ PENTING - Sebelum Push

### File yang TIDAK boleh di-push:

1. **config/database.php** dengan password asli
   - Edit dulu, ganti password jadi placeholder
   - Atau tambahkan ke .gitignore

2. **logs/** folder dengan data sensitif
   - Sudah di .gitignore

### Edit config/database.php sebelum push:

```php
// JANGAN upload password asli!
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // <-- Kosongkan atau isi 'your_password_here'
$db_name = 'db_ngaji';
```

Setelah edit:
```powershell
git add config/database.php
git commit -m "chore: remove sensitive database credentials"
git push
```

---

## 🎉 Hasil Akhir

Repository GitHub Anda akan berisi:
- ✅ Semua source code PHP
- ✅ Database SQL file
- ✅ ESP32 Arduino code
- ✅ README lengkap
- ✅ Dokumentasi setup

Bisa langsung di-clone oleh orang lain:
```bash
git clone https://github.com/USERNAME/absensi-ngaji-iot.git
```

---

**Git sudah initialized! ✅**  
**Files sudah di-commit! ✅**  
**Tinggal push ke GitHub! 🚀**
