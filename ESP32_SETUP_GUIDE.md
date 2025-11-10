# 🔌 ESP32-C6 Setup Guide

## 📋 Hardware Requirements

### Components
- **ESP32-C6 Development Board**
- **MFRC522 RFID Reader Module**
- **RGB LED Module 3 Color 10mm** (Common Cathode)
- **Buzzer Active 5V**
- **RFID Cards/Tags** (13.56 MHz)
- **Breadboard** dan **Jumper Wires**
- **Resistor 220Ω** (x3 untuk LED)

### Power Requirements
⚠️ **PENTING**: MFRC522 menggunakan **3.3V**, JANGAN hubungkan ke 5V!

## 🔧 Pin Connections

### MFRC522 RFID Reader → ESP32-C6

| MFRC522 Pin | ESP32-C6 Pin | Keterangan |
|-------------|--------------|------------|
| SDA (SS)    | GPIO 5       | Chip Select |
| SCK         | GPIO 18      | Clock |
| MOSI        | GPIO 23      | Master Out Slave In |
| MISO        | GPIO 19      | Master In Slave Out |
| IRQ         | -            | Not used |
| GND         | GND          | Ground |
| RST         | GPIO 22      | Reset |
| **3.3V**    | **3.3V**     | ⚠️ **CRITICAL: 3.3V ONLY!** |

### RGB LED (Common Cathode) → ESP32-C6

| LED Pin | ESP32-C6 Pin | Resistor | Keterangan |
|---------|--------------|----------|------------|
| RED     | GPIO 2       | 220Ω     | LED Merah |
| GREEN   | GPIO 4       | 220Ω     | LED Hijau |
| BLUE    | GPIO 15      | 220Ω     | LED Biru |
| CATHODE | GND          | -        | Common Ground |

### Buzzer Active 5V → ESP32-C6

| Buzzer Pin | ESP32-C6 Pin | Keterangan |
|------------|--------------|------------|
| Signal (+) | GPIO 21      | Control signal |
| GND (-)    | GND          | Ground |

## 💾 Software Setup

### 1. Install Arduino IDE

1. Download Arduino IDE: https://www.arduino.cc/en/software
2. Install sesuai OS Anda

### 2. Install ESP32 Board Support

1. Buka Arduino IDE
2. **File** → **Preferences**
3. Tambahkan URL berikut ke **Additional Board Manager URLs**:
   ```
   https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
   ```
4. **Tools** → **Board** → **Boards Manager**
5. Cari "esp32" dan install **esp32 by Espressif Systems**

### 3. Install Library MFRC522

**Cara 1: Via Library Manager (Recommended)**
1. **Sketch** → **Include Library** → **Manage Libraries**
2. Cari "MFRC522"
3. Install **MFRC522 by GithubCommunity**

**Cara 2: Manual Download**
1. Download: https://github.com/miguelbalboa/rfid
2. Extract dan copy ke folder `Arduino/libraries/`

### 4. Configure Board Settings

1. **Tools** → **Board** → **ESP32 Arduino** → **ESP32C6 Dev Module**
2. **Tools** → **Upload Speed** → **115200**
3. **Tools** → **CPU Frequency** → **160MHz**
4. **Tools** → **Flash Size** → **4MB**
5. **Tools** → **Port** → Pilih port COM ESP32 Anda

## 📝 Upload Code ke ESP32

### 1. Edit Konfigurasi WiFi

Buka file `esp32_absensi.ino` dan edit:

```cpp
// ============================================
// KONFIGURASI WIFI
// ============================================
const char* ssid = "NAMA_WIFI_ANDA";        // <--- Ganti dengan WiFi Anda
const char* password = "PASSWORD_WIFI_ANDA"; // <--- Ganti dengan password
```

### 2. Edit URL Server

Sesuaikan dengan domain atau IP server Anda:

```cpp
// ============================================
// KONFIGURASI SERVER
// ============================================
const char* serverURL = "https://yourdomain.com/cc/absen.php";
// atau untuk local:
// const char* serverURL = "http://192.168.1.100/cc/absen.php";
```

**Cara cek IP lokal:**
- Windows: `ipconfig` di CMD
- Linux/Mac: `ifconfig` di Terminal

### 3. Upload Code

1. Hubungkan ESP32-C6 ke komputer via USB
2. Pilih **Port** yang benar di **Tools** → **Port**
3. Klik **Upload** (panah → di toolbar)
4. Tunggu hingga selesai upload

### 4. Monitor Serial

1. Klik **Serial Monitor** (icon kaca pembesar)
2. Set baud rate ke **115200**
3. Anda akan melihat output:

```
=================================
ESP32-C6 RFID Absensi System
v2.0 with LED Standby
=================================
🔵 Booting...
✅ RFID Reader initialized
🔌 Connecting to WiFi: YourWiFi
.....
✅ WiFi Connected!
IP Address: 192.168.1.50

📡 Server Configuration:
URL: https://yourdomain.com/cc/absen.php

✅ System Ready!
🟢 LED hijau = Siap scan (akan mati setelah 30 detik tidak aktif)
Tap kartu RFID untuk absensi...
```

## 🎯 Testing & Troubleshooting

### Test Connection

1. **LED Indikator:**
   - 🔵 **Biru**: Booting
   - 🟢 **Hijau**: Ready (mati otomatis setelah 30 detik)
   - 🟡 **Kuning**: Scanning kartu
   - 🔴 **Merah**: Error

2. **Tap kartu RFID** ke reader
3. Lihat serial monitor untuk output

### Common Issues & Solutions

#### ❌ MFRC522 not detected

**Penyebab:**
- Wiring salah
- Power 5V (harus 3.3V!)
- MFRC522 rusak

**Solusi:**
1. Cek semua koneksi pin
2. Pastikan **VCC ke 3.3V bukan 5V**
3. Test MFRC522 dengan example code

#### ❌ WiFi Connection Failed

**Penyebab:**
- SSID/password salah
- ESP32 terlalu jauh dari router
- WiFi 5GHz (ESP32 hanya support 2.4GHz)

**Solusi:**
1. Cek SSID dan password
2. Dekatkan ESP32 ke router
3. Gunakan WiFi 2.4GHz

#### ❌ HTTP Error -1 atau -5

**Penyebab:**
- Server tidak bisa diakses
- URL salah
- Firewall memblokir

**Solusi:**
1. Test URL di browser
2. Cek IP address server (jika local)
3. Matikan firewall sementara untuk test

#### ❌ Error: Kartu tidak terdaftar

**Penyebab:**
- UID kartu belum di-input ke database

**Solusi:**
1. Scan kartu dan catat UID dari serial monitor
2. Tambahkan santri baru dengan UID tersebut via web

#### ⚠️ Sudah absen hari ini

**Ini bukan error!** Sistem mencegah absen ganda di hari yang sama.

## 🎨 LED Status Indicators

| Warna | Status | Keterangan |
|-------|--------|------------|
| 🔵 Biru | Booting | System sedang startup |
| 🟢 Hijau | Ready | Siap scan kartu |
| 🟡 Kuning | Scanning | Kartu sedang dibaca |
| 🔴 Merah (blink 3x) | Error | Kartu tidak terdaftar |
| 🟢 Hijau (blink 3x) | Success | Absensi berhasil |
| 🟡 Kuning (blink 2x) | Warning | Sudah absen hari ini |
| ⚫ Mati | Standby | 30 detik tidak aktif |

## 🔊 Buzzer Sounds

| Sound | Keterangan |
|-------|------------|
| Beep 1x (100ms) | Success - Absensi berhasil |
| Beep 2x (100ms) | Error - Kartu tidak terdaftar / Sudah absen |

## ⚙️ Features

### 1. Auto Standby LED
- LED hijau menyala saat ready
- Otomatis mati setelah **30 detik** tidak ada aktivitas
- Hemat daya dan umur LED
- LED menyala kembali saat ada kartu yang di-scan

### 2. Auto WiFi Reconnect
- Cek koneksi WiFi setiap loop
- Auto reconnect jika terputus
- Retry logic untuk stabilitas

### 3. Duplicate Prevention
- Sistem mencegah absen ganda di hari yang sama
- Response "sudah_absen" jika sudah scan hari ini

### 4. Visual & Audio Feedback
- RGB LED untuk status visual
- Buzzer untuk feedback audio
- Clear indication untuk user

### 5. HTTPS Support
- Support HTTP dan HTTPS
- SSL certificate validation bisa di-skip untuk development
- Production-ready security

### 6. Detailed Logging
- Serial monitor output lengkap
- Error messages yang jelas
- Debugging information

## 📊 API Response Format

### Success Response
```json
{
  "status": "sukses",
  "message": "Absensi berhasil dicatat",
  "timestamp": "2025-11-10 08:00:00",
  "data": {
    "id_absensi": 123,
    "nama": "Ahmad Fauzi",
    "kelas": "Kelas Iqro 1",
    "uid_kartu": "A1B2C3D4",
    "waktu_absen": "2025-11-10 08:00:00",
    "status": "Hadir"
  }
}
```

### Already Absent Response
```json
{
  "status": "sudah_absen",
  "message": "Anda sudah melakukan absensi hari ini",
  "timestamp": "2025-11-10 08:00:00",
  "data": {
    "nama": "Ahmad Fauzi",
    "kelas": "Kelas Iqro 1",
    "uid_kartu": "A1B2C3D4",
    "tanggal": "2025-11-10"
  }
}
```

### Card Not Found Response
```json
{
  "status": "gagal",
  "message": "Kartu RFID tidak terdaftar di sistem",
  "timestamp": "2025-11-10 08:00:00",
  "data": {
    "uid_kartu": "UNKNOWN123"
  }
}
```

## 🔒 Security Notes

1. **HTTPS Recommended**: Gunakan HTTPS untuk production
2. **WiFi Security**: Gunakan WPA2/WPA3
3. **Change Default Credentials**: Ganti SSID dan password default
4. **Physical Security**: Amankan hardware dari akses tidak sah

## 📞 Support

Jika ada masalah:
1. Cek **Serial Monitor** untuk error messages
2. Verifikasi semua **pin connections**
3. Test **WiFi connectivity**
4. Cek **server accessibility**

---

**Last Updated:** November 2025  
**Version:** 2.0  
**Compatible:** ESP32-C6, ESP32 DevKit, ESP32-WROOM
