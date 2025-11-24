<?php
/**
 * Konfigurasi Database MySQL
 * File ini berisi koneksi ke database untuk sistem absensi ngaji
 * Auto-detect: Local vs Vercel deployment
 */

// Detect if running on Vercel
$isVercel = getenv('VERCEL') !== false || isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']);

if ($isVercel) {
    // Vercel Environment - Use environment variables
    define('DB_HOST', getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost'));
    define('DB_USER', getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'));
    define('DB_PASS', getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ''));
    define('DB_NAME', getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'db_ngaji'));
    $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? 3306);
} else {
    // Local Development
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'db_ngaji');
    $port = 3306;
}

// Membuat koneksi dengan port
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);

// Cek koneksi
if ($conn->connect_error) {
    $errorMsg = "Koneksi database gagal: " . $conn->connect_error;
    error_log($errorMsg);
    
    // Return JSON untuk API endpoints
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        die(json_encode([
            'status' => 'error',
            'message' => 'Database connection failed',
            'details' => $isVercel ? 'Check Vercel environment variables' : $errorMsg
        ]));
    }
    
    die($errorMsg);
}

// Set charset UTF-8
$conn->set_charset("utf8mb4");

// Set timezone Asia/Jakarta
$conn->query("SET time_zone = '+07:00'");

// Fungsi untuk menutup koneksi (opsional, otomatis ditutup saat script selesai)
function closeConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}
?>
