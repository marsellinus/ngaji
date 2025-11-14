/*
 * ESP32-C6 RFID Absensi System - FINAL VERSION with BLYNK IoT
 * 
 * Fitur:
 * - LED status standby (mati otomatis 30 detik)
 * - Support HTTP dan HTTPS
 * - Auto reconnect WiFi
 * - Blynk IoT monitoring real-time
 * - Remote LED control via Blynk
 * 
 * Hardware:
 * - ESP32-C6 Development Board
 * - MFRC522 RFID Reader
 * - RGB LED Module 3 Color 10mm (Common Cathode)
 * - Buzzer Speaker Active 5V
 * 
 * Library yang dibutuhkan:
 * - MFRC522 by GithubCommunity
 * - Blynk by Volodymyr Shymanskyy (install via Library Manager)
 * 
 * Your Specific Pin Connection:
 * MFRC522 RFID Reader   -> ESP32-C6
 * SDA (SS)              -> GPIO 5
 * SCK                   -> GPIO 18
 * MOSI                  -> GPIO 23
 * MISO                  -> GPIO 19
 * RST                   -> GPIO 22
 * VCC                   -> 3.3V (CRITICAL! Jangan hubungkan ke 5V)
 * GND                   -> GND
 * 
 * RGB LED (Common Cathode) -> ESP32-C6
 * RED (Anode)           -> GPIO 2  (+ resistor 220Ω)
 * GREEN (Anode)         -> GPIO 4  (+ resistor 220Ω)
 * BLUE (Anode)          -> GPIO 15 (+ resistor 220Ω)
 * Common Cathode        -> GND
 * 
 * Buzzer Active 5V      -> ESP32-C6
 * Signal (+)            -> GPIO 21
 * GND (-)               -> GND
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>
#include <BlynkSimpleEsp32.h>

// ============================================
// KONFIGURASI BLYNK
// ============================================
#define BLYNK_TEMPLATE_ID "TMPL6DH4DNzrE"
#define BLYNK_TEMPLATE_NAME "Absensi RFID"
#define BLYNK_AUTH_TOKEN "05hD447U-GLK_GfPCoOj1ZQR3IRyGJPC"

// Uncomment untuk debug Blynk
// #define BLYNK_PRINT Serial

// ============================================
// KONFIGURASI WIFI
// ============================================
const char* ssid = "wokwi";           // <--- Ganti dengan nama WiFi kamu
const char* password = "82292112";       // <--- Ganti dengan password WiFi kamu

// ============================================
// KONFIGURASI SERVER
// ============================================
// PRODUCTION (butuh disable anti-bot protection di hosting):
// const char* serverURL = "https://cel.my.id/cc/api/absen.php";

// DEVELOPMENT (test local dulu):
const char* serverURL = "http://10.225.159.41/cc/api/absen.php"; // IP laptop di WiFi karyameu

// ============================================
// PIN CONFIGURATION
// ============================================
// RFID MFRC522
#define SS_PIN 5
#define RST_PIN 22
#define SCK_PIN 18
#define MISO_PIN 19
#define MOSI_PIN 23

// RGB LED (Common Cathode)
#define LED_RED_PIN 2
#define LED_GREEN_PIN 4
#define LED_BLUE_PIN 15

// Buzzer Active 5V
#define BUZZER_PIN 21

MFRC522 rfid(SS_PIN, RST_PIN);
BlynkTimer timer;

// ============================================
// LOGIKA LED STANDBY & BLYNK
// ============================================
unsigned long lastActivityTime = 0;       // Waktu aktivitas terakhir (scan kartu)
bool isReadyLedOn = true;                 // Status apakah LED 'Ready' (hijau) sedang menyala
const unsigned long LED_STANDBY_MS = 30000; // Waktu standby dalam milidetik (30 detik)
int todayCount = 0;                       // Counter absensi hari ini
bool blynkLedControl = false;             // Remote LED control dari Blynk

// ============================================
// FUNGSI LED DAN BUZZER
// ============================================

void setColor(bool red, bool green, bool blue) {
  digitalWrite(LED_RED_PIN, red ? HIGH : LOW);
  digitalWrite(LED_GREEN_PIN, green ? HIGH : LOW);
  digitalWrite(LED_BLUE_PIN, blue ? HIGH : LOW);
}

void ledOff() { setColor(false, false, false); }
void ledRed() { setColor(true, false, false); }
void ledGreen() { setColor(false, true, false); }
void ledBlue() { setColor(false, false, true); }
void ledYellow() { setColor(true, true, false); }

void blinkGreen(int times) {
  for(int i = 0; i < times; i++) {
    ledGreen(); delay(200);
    ledOff(); delay(200);
  }
}

void blinkRed(int times) {
  for(int i = 0; i < times; i++) {
    ledRed(); delay(200);
    ledOff(); delay(200);
  }
}

// PILIH SALAH SATU:
// Uncomment yang sesuai dengan tipe buzzer Anda

// === UNTUK ACTIVE BUZZER (5V) ===
void beep(int duration) {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(duration);
  digitalWrite(BUZZER_PIN, LOW);
}

// === UNTUK PASSIVE BUZZER (perlu PWM/tone) ===
// Uncomment kode di bawah jika Active Buzzer tidak bunyi
/*
void beep(int duration) {
  tone(BUZZER_PIN, 2000);  // 2000 Hz
  delay(duration);
  noTone(BUZZER_PIN);
}
*/

void beepSuccess() { beep(100); }
void beepError() { beep(100); delay(100); beep(100); }

// ============================================
// BLYNK HANDLERS
// ============================================

// Blynk Virtual Pin V5 - Remote LED Control
BLYNK_WRITE(V5) {
  blynkLedControl = param.asInt();
  if (blynkLedControl) {
    ledGreen();
    Serial.println("📱 LED dinyalakan dari Blynk");
  } else {
    ledOff();
    Serial.println("📱 LED dimatikan dari Blynk");
  }
}

// Update Blynk setiap 5 detik
void updateBlynk() {
  if (Blynk.connected()) {
    Blynk.virtualWrite(V0, WiFi.status() == WL_CONNECTED ? "🟢 Online" : "🔴 Offline");
    Blynk.virtualWrite(V4, todayCount);
  }
}

// ============================================
// SETUP
// ============================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n\n=================================");
  Serial.println("ESP32-C6 RFID Absensi System");
  Serial.println("v2.0 with LED Standby");
  Serial.println("=================================");
  
  pinMode(LED_RED_PIN, OUTPUT);
  pinMode(LED_GREEN_PIN, OUTPUT);
  pinMode(LED_BLUE_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  
  ledBlue();
  Serial.println("🔵 Booting...");
  
  // Test buzzer saat startup
  Serial.println("Testing buzzer...");
  for(int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(BUZZER_PIN, LOW);
    delay(100);
  }
  Serial.println("Buzzer test selesai.");
  
  SPI.begin(SCK_PIN, MISO_PIN, MOSI_PIN); 
  rfid.PCD_Init();
  
  byte v = rfid.PCD_ReadRegister(MFRC522::VersionReg);
  if (v == 0x00 || v == 0xFF) {
    Serial.println("❌ MFRC522 not detected! Check wiring and power (3.3V).");
    Serial.println("🔴 Sistem dihentikan.");
    blinkRed(10);
    while(true);
  }
  Serial.println("✅ RFID Reader initialized");
  rfid.PCD_DumpVersionToSerial();
  
  connectWiFi();
  
  // Initialize Blynk
  Serial.println("📱 Connecting to Blynk...");
  Blynk.config(BLYNK_AUTH_TOKEN);
  Blynk.connect();
  
  if (Blynk.connected()) {
    Serial.println("✅ Blynk Connected!");
    Blynk.virtualWrite(V0, "🟢 Online");
    Blynk.virtualWrite(V1, "Waiting...");
    Blynk.virtualWrite(V2, "Waiting...");
    Blynk.virtualWrite(V3, "Waiting...");
    Blynk.virtualWrite(V4, 0);
  } else {
    Serial.println("⚠️ Blynk connection failed, continuing without Blynk...");
  }
  
  // Setup timer untuk update Blynk setiap 5 detik
  timer.setInterval(5000L, updateBlynk);
  
  Serial.println("\n📡 Server Configuration:");
  Serial.print("URL: "); Serial.println(serverURL);
  
  ledGreen();
  beepSuccess();
  lastActivityTime = millis(); // Mulai timer standby
  isReadyLedOn = true;
  Serial.println("\n✅ System Ready!");
  Serial.println("🟢 LED hijau = Siap scan (akan mati setelah 30 detik tidak aktif)");
  Serial.println("Tap kartu RFID untuk absensi...\n");
}

// ============================================
// CONNECT TO WIFI
// ============================================
void connectWiFi() {
  Serial.print("🔌 Connecting to WiFi: ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 40) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Connected!");
    Serial.print("IP Address: "); Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n❌ WiFi Connection Failed!");
    blinkRed(5);
  }
}

// ============================================
// MAIN LOOP
// ============================================
void loop() {
  Blynk.run();       // Run Blynk
  timer.run();       // Run timer untuk update Blynk
  
  // --- LOGIKA LED STANDBY (FITUR BARU) ---
  // Jika LED 'Ready' (hijau) sedang menyala dan sudah melewati waktu standby, matikan LED.
  if (isReadyLedOn && (millis() - lastActivityTime > LED_STANDBY_MS)) {
    Serial.println("\n(i) Inactivity timeout. LED mati, sistem standby.");
    ledOff();
    isReadyLedOn = false; // Tandai bahwa LED sudah dimatikan
  }
  
  // Pastikan WiFi tetap terhubung
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️ WiFi disconnected. Reconnecting...");
    connectWiFi();
    if (WiFi.status() != WL_CONNECTED) {
      delay(5000);
      return;
    }
  }

  // Cek apakah ada kartu yang di-tap
  if (!rfid.PICC_IsNewCardPresent()) {
    return;
  }
  
  // --- Aktivitas terdeteksi ---
  isReadyLedOn = false; // Matikan status 'Ready' karena akan mulai scan
  ledYellow();          // Indikator scanning
  
  // Baca kartu
  if (!rfid.PICC_ReadCardSerial()) {
    Serial.println("❌ Gagal membaca serial kartu.");
    ledRed();
    beepError();
    delay(1000);
    // Kembalikan ke state Ready
    ledGreen();
    lastActivityTime = millis(); // Reset timer
    isReadyLedOn = true;
    return;
  }
  
  // Ambil UID dari kartu
  String uid = getUID();
  
  Serial.println("\n=================================");
  Serial.println("📇 Kartu terdeteksi!");
  Serial.print("UID: "); Serial.println(uid);
  Serial.println("=================================");
  
  // Kirim ke server
  sendToServer(uid);
  
  // Halt kartu untuk mencegah pembacaan ganda
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
  
  // Kembali ke LED hijau (state Ready)
  ledGreen();
  lastActivityTime = millis(); // Reset timer standby setelah aktivitas selesai
  isReadyLedOn = true;
  
  // Delay untuk menghindari pembacaan ganda terlalu cepat
  delay(2000);
  
  Serial.println("\n🟢 Siap untuk kartu berikutnya...\n");
}

// ============================================
// GET UID FROM RFID CARD
// ============================================
String getUID() {
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}

// ============================================
// SEND DATA TO SERVER (HTTP & HTTPS Support)
// ============================================
void sendToServer(String uid) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("❌ WiFi not connected for sending data!");
    ledRed(); beepError();
    connectWiFi();
    if (WiFi.status() != WL_CONNECTED) {
      Serial.println("❌ Failed to reconnect, cannot send data.");
      return;
    }
  }
  
  Serial.println("📤 Sending to server...");
  
  HTTPClient http;
  WiFiClientSecure client;
  
  String url = String(serverURL);
  
  // Konfigurasi untuk HTTPS
  if (url.startsWith("https://")) {
    client.setInsecure(); // Untuk development, skip SSL verification
    http.begin(client, serverURL);
  } else {
    http.begin(serverURL);
  }
  
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  
  // Kirim dengan parameter uid_kartu sesuai absen.php
  String postData = "uid_kartu=" + uid;
  Serial.print("Data: "); Serial.println(postData);
  
  int httpResponseCode = http.POST(postData);
  
  if (httpResponseCode > 0) {
    Serial.print("✅ Response Code: "); Serial.println(httpResponseCode);
    String response = http.getString();
    Serial.println("📥 Response:"); Serial.println(response);
    
    // Remove whitespace untuk parsing lebih akurat
    response.replace(" ", "");
    response.replace("\n", "");
    response.replace("\r", "");
    
    // Parse response dari absen.php
    if (response.indexOf("\"status\":\"sukses\"") >= 0) {
      Serial.println("✅ Absensi berhasil dicatat!");
      blinkGreen(3); beepSuccess();
      todayCount++;
      
      // Update Blynk
      if (Blynk.connected()) {
        // Extract nama dari JSON response
        int namaStart = response.indexOf("\"nama\":\"") + 8;
        int namaEnd = response.indexOf("\"", namaStart);
        String nama = response.substring(namaStart, namaEnd);
        
        Blynk.virtualWrite(V1, uid);
        Blynk.virtualWrite(V2, nama);
        Blynk.virtualWrite(V3, "✅ Berhasil");
        Blynk.virtualWrite(V4, todayCount);
        Blynk.logEvent("absensi_sukses", "Absensi: " + nama);
      }
    } else if (response.indexOf("\"status\":\"sudah_absen\"") >= 0) {
      Serial.println("⚠️ Sudah absen hari ini!");
      ledYellow(); beepError();
      delay(1000);
      
      // Update Blynk
      if (Blynk.connected()) {
        int namaStart = response.indexOf("\"nama\":\"") + 8;
        int namaEnd = response.indexOf("\"", namaStart);
        String nama = response.substring(namaStart, namaEnd);
        
        Blynk.virtualWrite(V1, uid);
        Blynk.virtualWrite(V2, nama);
        Blynk.virtualWrite(V3, "⚠️ Sudah Absen");
      }
    } else if (response.indexOf("\"status\":\"gagal\"") >= 0) {
      Serial.println("❌ Kartu tidak terdaftar!");
      blinkRed(3); beepError();
      
      // Update Blynk
      if (Blynk.connected()) {
        Blynk.virtualWrite(V1, uid);
        Blynk.virtualWrite(V2, "Tidak Dikenal");
        Blynk.virtualWrite(V3, "❌ Gagal");
        Blynk.logEvent("kartu_tidak_terdaftar", "UID: " + uid);
      }
    } else {
      Serial.println("❌ Response tidak dikenali!");
      blinkRed(2); beepError();
    }
  } else {
    Serial.print("❌ Error sending request. HTTP Code: ");
    Serial.println(httpResponseCode);
    
    // Memberikan petunjuk berdasarkan kode error HTTP
    if (httpResponseCode == HTTPC_ERROR_CONNECTION_REFUSED) {
      Serial.println("- Koneksi ditolak oleh server.");
    } else if (httpResponseCode == HTTPC_ERROR_NOT_CONNECTED) {
      Serial.println("- Tidak terhubung ke jaringan.");
    } else if (httpResponseCode == HTTPC_ERROR_NO_STREAM) {
      Serial.println("- Server tidak memberi respons.");
    } else if (httpResponseCode == HTTPC_ERROR_READ_TIMEOUT) {
      Serial.println("- Timeout saat membaca respons.");
    } else if (httpResponseCode == -5) {
      Serial.println("- Gagal resolve hostname (DNS).");
    }
    
    blinkRed(5); beepError();
  }
  
  http.end();
}
