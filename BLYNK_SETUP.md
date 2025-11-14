# 📱 Setup Blynk IoT untuk ESP32 Absensi RFID

## 🎯 Fitur Blynk
- ✅ Monitoring real-time status absensi
- ✅ Notifikasi push saat ada absensi
- ✅ Remote control LED via smartphone
- ✅ Dashboard monitoring total absensi hari ini
- ✅ History scan RFID terakhir

---

## 📲 Step 1: Setup Blynk App

### 1.1 Download App
- **Android**: [Google Play Store](https://play.google.com/store/apps/details?id=cloud.blynk)
- **iOS**: [App Store](https://apps.apple.com/app/blynk-iot/id1559317868)

### 1.2 Create Account
1. Buka Blynk App
2. Tap **Sign Up**
3. Masukkan email dan password
4. Verify email

### 1.3 Create New Template
1. Tap **+ Create Template**
2. Isi form:
   - **Name**: `Absensi RFID`
   - **Hardware**: `ESP32`
   - **Connection Type**: `WiFi`
3. Tap **Done**

---

## 📊 Step 2: Setup Datastreams

Masuk ke **Template Settings** → **Datastreams** → Tap **+ New Datastream**

### Datastream Virtual Pin V0 - Status Connection
- **Type**: String
- **Name**: Status Connection
- **Virtual Pin**: V0
- **Default**: Offline

### Datastream Virtual Pin V1 - Last RFID UID
- **Type**: String
- **Name**: Last UID
- **Virtual Pin**: V1
- **Default**: Waiting...

### Datastream Virtual Pin V2 - Last Name
- **Type**: String
- **Name**: Nama Santri
- **Virtual Pin**: V2
- **Default**: Waiting...

### Datastream Virtual Pin V3 - Last Status
- **Type**: String
- **Name**: Status Absensi
- **Virtual Pin**: V3
- **Default**: Waiting...

### Datastream Virtual Pin V4 - Total Hari Ini
- **Type**: Integer
- **Name**: Total Absensi
- **Virtual Pin**: V4
- **Min**: 0
- **Max**: 999
- **Default**: 0

### Datastream Virtual Pin V5 - LED Control
- **Type**: Integer
- **Name**: LED Control
- **Virtual Pin**: V5
- **Min**: 0
- **Max**: 1
- **Default**: 0

---

## 🎨 Step 3: Design Dashboard (Web & Mobile)

### 3.1 Mobile Dashboard
Tap **Web Dashboard** atau **Mobile Dashboard** → Drag widgets:

#### Widget 1: Label - Status Connection
- **Widget**: Label
- **Datastream**: V0 (Status Connection)
- **Design**: 
  - Font Size: 18
  - Color: Auto

#### Widget 2: Label - Last UID
- **Widget**: Label
- **Title**: "UID Kartu Terakhir"
- **Datastream**: V1 (Last UID)

#### Widget 3: Label - Nama Santri
- **Widget**: Label
- **Title**: "Nama Santri"
- **Datastream**: V2 (Nama Santri)
- **Font Size**: 20
- **Font Style**: Bold

#### Widget 4: Label - Status
- **Widget**: Label
- **Title**: "Status Absensi"
- **Datastream**: V3 (Status Absensi)

#### Widget 5: Value Display - Total
- **Widget**: Value Display
- **Title**: "Total Absensi Hari Ini"
- **Datastream**: V4 (Total Absensi)
- **Icon**: 📊

#### Widget 6: Switch - LED Control
- **Widget**: Switch
- **Title**: "Remote LED"
- **Datastream**: V5 (LED Control)
- **ON Label**: "LED ON"
- **OFF Label**: "LED OFF"

---

## 🔔 Step 4: Setup Events (Notifikasi)

Masuk ke **Template Settings** → **Events** → **+ New Event**

### Event 1: Absensi Sukses
- **Event Name**: `absensi_sukses`
- **Event Code**: `ABSENSI_OK`
- **Description**: Notifikasi saat absensi berhasil
- **Notification**: 
  ```
  ✅ Absensi Berhasil
  {DESCRIPTION}
  ```

### Event 2: Kartu Tidak Terdaftar
- **Event Name**: `kartu_tidak_terdaftar`
- **Event Code**: `KARTU_UNKNOWN`
- **Description**: Notifikasi kartu tidak dikenal
- **Notification**:
  ```
  ❌ Kartu Tidak Terdaftar
  {DESCRIPTION}
  ```

---

## 🔑 Step 5: Get Auth Token

1. Tap **Template Settings** → **Device Info**
2. **Copy Auth Token** (format: `abcd1234efgh5678ijkl...`)
3. Simpan token ini untuk dipakai di ESP32

---

## 💻 Step 6: Update ESP32 Code

### 6.1 Install Library Blynk
**Arduino IDE**:
1. Sketch → Include Library → Manage Libraries
2. Search: `Blynk`
3. Install: **Blynk by Volodymyr Shymanskyy**

### 6.2 Update Config di `esp32_absensi.ino`

Ganti di bagian atas file:

```cpp
// ============================================
// KONFIGURASI BLYNK
// ============================================
#define BLYNK_TEMPLATE_ID "TMPLxxxxxx"     // <--- Ganti ini
#define BLYNK_TEMPLATE_NAME "Absensi RFID"
#define BLYNK_AUTH_TOKEN "abcd1234..."     // <--- Ganti dengan token Anda
```

**Cara dapat Template ID**:
- Blynk App → Template Settings → Template Info
- Copy `TMPL` ID (contoh: `TMPL6eR7x8y9z`)

**Cara dapat Auth Token**:
- Sudah di-copy di Step 5

### 6.3 Upload ke ESP32
1. Buka `esp32_absensi.ino` di Arduino IDE
2. Verify code (✓)
3. Upload ke ESP32 (→)
4. Buka Serial Monitor (115200 baud)

---

## ✅ Step 7: Test System

### 7.1 Cek Serial Monitor
Harusnya muncul:
```
🔌 Connecting to WiFi: karyameu
✅ WiFi Connected!
IP Address: 10.225.159.41
📱 Connecting to Blynk...
✅ Blynk Connected!
✅ System Ready!
```

### 7.2 Cek Blynk App
- Status Connection: 🟢 Online
- Total Absensi: 0

### 7.3 Test Scan Kartu RFID
1. Tap kartu RFID ke reader
2. **Serial Monitor** akan show:
   ```
   📇 Kartu terdeteksi!
   UID: A1B2C3D4
   ✅ Absensi berhasil dicatat!
   ```
3. **Blynk App** akan update:
   - Last UID: `A1B2C3D4`
   - Nama Santri: `Ahmad Fauzi`
   - Status: `✅ Berhasil`
   - Total: `1`
4. **Push Notification**: "✅ Absensi Berhasil: Ahmad Fauzi"

### 7.4 Test Remote LED Control
1. Di Blynk App, toggle switch **Remote LED**
2. LED ESP32 akan nyala/mati sesuai switch
3. Serial Monitor: `📱 LED dinyalakan dari Blynk`

---

## 🎯 Fitur-Fitur Blynk

### ✅ Yang Sudah Berfungsi:
- Real-time monitoring status absensi
- Display UID kartu terakhir
- Display nama santri terakhir
- Counter total absensi hari ini
- Remote LED control dari smartphone
- Push notification absensi sukses
- Push notification kartu tidak terdaftar
- Auto-update setiap 5 detik

### 🔮 Fitur Tambahan (Optional):
- **History Chart**: Graph absensi per hari
- **Time Range**: Filter absensi per jam/hari/minggu
- **Multi-device**: Monitor beberapa ESP32 sekaligus
- **Automation**: Auto-LED control berdasarkan waktu

---

## 🔧 Troubleshooting

### Blynk tidak connect
```
⚠️ Blynk connection failed, continuing without Blynk...
```
**Solusi**:
- Cek Auth Token benar atau tidak
- Cek WiFi connected atau tidak
- Cek internet connection
- Restart ESP32

### Widget tidak update
**Solusi**:
- Cek Virtual Pin di dashboard sama dengan di code
- Cek datastream type (String/Integer) sesuai
- Force refresh Blynk App

### Notification tidak masuk
**Solusi**:
- Enable notification di phone settings
- Cek Events sudah dibuat di template
- Cek event name match dengan code (`absensi_sukses`, `kartu_tidak_terdaftar`)

---

## 📱 Blynk Free vs Paid

### Free Plan (Cukup untuk project ini):
- ✅ 1 Template
- ✅ 2 Devices
- ✅ Unlimited Datastreams
- ✅ Basic Widgets
- ✅ Push Notifications
- ❌ Advanced Analytics
- ❌ Data Export

### Plus Plan ($6.99/bulan):
- ✅ Unlimited Templates
- ✅ 10 Devices
- ✅ Advanced Widgets
- ✅ Data Export CSV
- ✅ Custom Branding

**Untuk project absensi ini, Free Plan sudah cukup!** 🎉

---

## 📚 Resource

- **Blynk Docs**: https://docs.blynk.io
- **Blynk Community**: https://community.blynk.cc
- **Blynk Examples**: https://github.com/blynkkk/blynk-library/tree/master/examples

---

**Happy Monitoring! 📊🚀**
