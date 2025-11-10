<?php
/**
 * Konfigurasi Database MySQL
 * File ini berisi koneksi ke database untuk sistem absensi ngaji
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_ngaji');

// Membuat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8");

// Fungsi untuk menutup koneksi (opsional, otomatis ditutup saat script selesai)
function closeConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}
?>
