# 🚀 PANDUAN CEPAT - Sistem Absensi Ngaji

## 📌 Quick Links

**Login Website:**
```
http://localhost/cc/public/login.php
Username: admin
Password: admin123
```

**Admin Panel:**
- Dashboard: `http://localhost/cc/public/index.php`
- Data Santri: `http://localhost/cc/public/tambah_santri.php`
- Admin Users: `http://localhost/cc/public/admin_users.php`
- Settings: `http://localhost/cc/public/settings.php`

## 🗄️ Database

**Import:**
```bash
mysql -u root -p db_ngaji < database.sql
```

**Tables:**
- `santri` - Data santri
- `absensi` - Data kehadiran
- `admin` - User admin
- `settings` - Konfigurasi sistem
- `activity_log` - Log aktivitas

## 🔌 ESP32-C6 Pin Configuration

### MFRC522 RFID (⚠️ 3.3V ONLY!)
- SDA: GPIO 5
- SCK: GPIO 18
- MOSI: GPIO 23
- MISO: GPIO 19
- RST: GPIO 22
- VCC: **3.3V** (JANGAN 5V!)
- GND: GND

### RGB LED (Common Cathode)
- RED: GPIO 2 (+ resistor 220Ω)
- GREEN: GPIO 4 (+ resistor 220Ω)
- BLUE: GPIO 15 (+ resistor 220Ω)
- Cathode: GND

### Buzzer Active 5V
- Signal: GPIO 21
- GND: GND

## ⚙️ ESP32 Configuration

**File:** `esp32_absensi.ino`

**Edit WiFi:**
```cpp
const char* ssid = "karyameu";
const char* password = "82292112";
```

**Edit Server:**
```cpp
const char* serverURL = "https://cel.my.id/cc/absen.php";
```

**Upload:**
1. Arduino IDE
2. Board: ESP32C6 Dev Module
3. Port: Pilih COM Port
4. Upload

## 🎨 LED Status

- 🔵 Biru = Booting
- 🟢 Hijau = Ready (mati otomatis 30 detik)
- 🟡 Kuning = Scanning
- 🔴 Merah = Error
- 🟢 Blink 3x = Sukses!

## 📋 Workflow Absensi

1. **Daftar Santri:**
   - Login website
   - Data Santri → Tambah
   - Input nama + RFID ID
   - Simpan

2. **Tap Kartu:**
   - Dekatkan kartu ke RFID reader
   - LED kuning (scanning)
   - Buzzer beep
   - LED hijau blink 3x (sukses!)

3. **Lihat Data:**
   - Dashboard → Absensi hari ini
   - Laporan → Filter by date

## 🔐 Access Levels

| Level | Access |
|-------|--------|
| Operator | Dashboard, Santri, Laporan |
| Admin | + Admin Users, Activity Log |
| Superadmin | + Settings |

## 📖 Dokumentasi

- `README.md` - Overview lengkap
- `SETUP_GUIDE.md` - Instalasi step-by-step
- `ESP32_SETUP_GUIDE.md` - Panduan ESP32 detail
- `ADMIN_SYSTEM_GUIDE.md` - Panduan admin
- `QUICK_START.md` - Reference cepat

## 🐛 Troubleshooting

**CSS tidak muncul?**
- Clear browser cache (Ctrl + F5)
- Cek internet (Tailwind CDN)

**RFID tidak terdeteksi?**
- Cek wiring
- Pastikan VCC ke 3.3V bukan 5V!
- Serial monitor untuk debug

**WiFi tidak connect?**
- Cek SSID/password
- ESP32 hanya support 2.4GHz
- Dekatkan ke router

**Kartu tidak terdaftar?**
- Scan kartu via serial monitor
- Catat UID
- Tambah santri dengan UID tersebut

## 📞 API Endpoint

**URL:** `https://cel.my.id/cc/absen.php`

**Method:** POST

**Parameter:** `uid_kartu=A1B2C3D4`

**Response:**
```json
{
  "status": "sukses",
  "message": "Absensi berhasil dicatat",
  "data": {
    "nama": "Ahmad Fauzi",
    "kelas": "Kelas Iqro 1"
  }
}
```

## ✅ Checklist Setup

- [ ] Import database
- [ ] Edit config/database.php
- [ ] Login website (admin/admin123)
- [ ] Ganti password default
- [ ] Upload code ke ESP32
- [ ] Wiring hardware
- [ ] Test scan kartu
- [ ] Tambah santri
- [ ] Test absensi end-to-end

---

**Ready to Use!** 🎉

Jika ada masalah, cek dokumentasi lengkap atau serial monitor ESP32 untuk error details.
