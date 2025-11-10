/*
 * ESP32-C6 RFID Absensi System - FINAL & COMPLETE VERSION with LED STANDBY
 * 
 * Fitur Baru:
 * - LED status (hijau) akan mati secara otomatis setelah 30 detik tidak ada aktivitas.
 * - LED akan menyala kembali (kuning) saat kartu mulai di-scan.
 * - Support HTTP dan HTTPS
 * - Auto reconnect WiFi
 * - Endpoint disesuaikan dengan absen.php
 * 
 * Hardware:
 * - ESP32-C6 Development Board
 * - MFRC522 RFID Reader
 * - RGB LED Module 3 Color 10mm (Common Cathode)
 * - Buzzer Speaker Active 5V
 * 
 * Library yang dibutuhkan:
 * - MFRC522 by GithubCommunity (install via Library Manager)
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

// ============================================
// KONFIGURASI WIFI
// ============================================
const char* ssid = "karyameu";           // <--- Ganti dengan nama WiFi kamu
const char* password = "82292112";       // <--- Ganti dengan password WiFi kamu

// ============================================
// KONFIGURASI SERVER
// ============================================
const char* serverURL = "https://cel.my.id/cc/absen.php";

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

// ============================================
// LOGIKA LED STANDBY (FITUR BARU)
// ============================================
unsigned long lastActivityTime = 0;       // Waktu aktivitas terakhir (scan kartu)
bool isReadyLedOn = true;                 // Status apakah LED 'Ready' (hijau) sedang menyala
const unsigned long LED_STANDBY_MS = 30000; // Waktu standby dalam milidetik (30 detik)

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

void beep(int duration) {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(duration);
  digitalWrite(BUZZER_PIN, LOW);
}

void beepSuccess() { beep(100); }
void beepError() { beep(100); delay(100); beep(100); }

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
    
    // Parse response dari absen.php
    if (response.indexOf("\"status\":\"sukses\"") >= 0) {
      Serial.println("✅ Absensi berhasil dicatat!");
      blinkGreen(3); beepSuccess();
    } else if (response.indexOf("\"status\":\"sudah_absen\"") >= 0) {
      Serial.println("⚠️ Sudah absen hari ini!");
      ledYellow(); beepError();
      delay(1000);
    } else if (response.indexOf("\"status\":\"gagal\"") >= 0) {
      Serial.println("❌ Kartu tidak terdaftar!");
      blinkRed(3); beepError();
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
