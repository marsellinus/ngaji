<?php
/**
 * API Endpoint untuk ESP32 RFID Absensi
 * File: absen.php
 * 
 * Menerima data dari ESP32 dengan parameter: uid_kartu
 * Response format: JSON
 */

// Disable error display untuk production
error_reporting(0);
ini_set('display_errors', 0);

// Set header JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

// Fungsi untuk log ke file
function logToFile($message) {
    $logFile = '../logs/absensi_log.txt';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Fungsi response JSON
function sendResponse($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    $response = [
        'status' => $status,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Handle GET request untuk health check
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    logToFile("INFO: Health check request dari IP=" . $_SERVER['REMOTE_ADDR']);
    
    // Test database connection
    $db_status = 'connected';
    $db_message = 'Database connection OK';
    
    try {
        $testQuery = "SELECT COUNT(*) as total FROM santri";
        $testResult = $conn->query($testQuery);
        if ($testResult) {
            $row = $testResult->fetch_assoc();
            $total_santri = $row['total'];
        } else {
            throw new Exception('Query failed');
        }
        
        $testQuery2 = "SELECT COUNT(*) as total FROM absensi WHERE DATE(waktu_absen) = CURDATE()";
        $testResult2 = $conn->query($testQuery2);
        if ($testResult2) {
            $row2 = $testResult2->fetch_assoc();
            $total_absen_hari_ini = $row2['total'];
        } else {
            $total_absen_hari_ini = 0;
        }
        
    } catch (Exception $e) {
        $db_status = 'error';
        $db_message = 'Database error: ' . $e->getMessage();
        $total_santri = 0;
        $total_absen_hari_ini = 0;
    }
    
    sendResponse('online', 'API Endpoint aktif dan siap menerima request', [
        'version' => '1.0',
        'server_time' => date('Y-m-d H:i:s'),
        'database' => $db_status,
        'database_message' => $db_message,
        'statistics' => [
            'total_santri' => $total_santri,
            'absensi_hari_ini' => $total_absen_hari_ini
        ],
        'usage' => [
            'method' => 'POST',
            'parameter' => 'uid_kartu',
            'example' => 'POST /api/absen.php dengan body: uid_kartu=A1B2C3D4'
        ]
    ]);
}

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logToFile("ERROR: Invalid method - " . $_SERVER['REQUEST_METHOD']);
    sendResponse('gagal', 'Method tidak diizinkan. Gunakan POST untuk absensi, GET untuk health check.', null, 405);
}

// Ambil data dari POST
$uid_kartu = isset($_POST['uid_kartu']) ? trim($_POST['uid_kartu']) : '';

// Log request
logToFile("REQUEST: uid_kartu={$uid_kartu} | IP=" . $_SERVER['REMOTE_ADDR']);

// Validasi input
if (empty($uid_kartu)) {
    logToFile("ERROR: UID kartu kosong");
    sendResponse('gagal', 'UID kartu tidak boleh kosong', null, 400);
}

// Sanitize input
$uid_kartu = mysqli_real_escape_string($conn, strtoupper($uid_kartu));

try {
    // Cek apakah kartu terdaftar di database
    $query = "SELECT id, nama_santri, rfid_id, kelas FROM santri WHERE rfid_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $uid_kartu);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        logToFile("ERROR: Kartu {$uid_kartu} tidak terdaftar");
        sendResponse('gagal', 'Kartu RFID tidak terdaftar di sistem', [
            'uid_kartu' => $uid_kartu
        ], 404);
    }
    
    $santri = $result->fetch_assoc();
    $stmt->close();
    
    // Cek apakah sudah absen hari ini
    $today = date('Y-m-d');
    $queryCheck = "SELECT id FROM absensi WHERE rfid_id = ? AND DATE(waktu_absen) = ? LIMIT 1";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("ss", $uid_kartu, $today);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    
    if ($resultCheck->num_rows > 0) {
        $stmtCheck->close();
        logToFile("WARNING: {$santri['nama_santri']} sudah absen hari ini");
        sendResponse('sudah_absen', 'Anda sudah melakukan absensi hari ini', [
            'nama' => $santri['nama_santri'],
            'kelas' => $santri['kelas'],
            'uid_kartu' => $uid_kartu,
            'tanggal' => $today
        ]);
    }
    $stmtCheck->close();
    
    // Insert data absensi
    $waktu_absen = date('Y-m-d H:i:s');
    $status = 'Hadir';
    $keterangan = 'Absensi via ESP32 RFID';
    
    $queryInsert = "INSERT INTO absensi (rfid_id, waktu_absen, status, keterangan) VALUES (?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($queryInsert);
    $stmtInsert->bind_param("ssss", $uid_kartu, $waktu_absen, $status, $keterangan);
    
    if ($stmtInsert->execute()) {
        $absensi_id = $stmtInsert->insert_id;
        $stmtInsert->close();
        
        logToFile("SUCCESS: {$santri['nama_santri']} berhasil absen | ID Absensi: {$absensi_id}");
        
        sendResponse('sukses', 'Absensi berhasil dicatat', [
            'id_absensi' => $absensi_id,
            'nama' => $santri['nama_santri'],
            'kelas' => $santri['kelas'],
            'uid_kartu' => $uid_kartu,
            'waktu_absen' => $waktu_absen,
            'status' => $status
        ], 201);
    } else {
        throw new Exception('Gagal menyimpan data absensi: ' . $conn->error);
    }
    
} catch (Exception $e) {
    logToFile("EXCEPTION: " . $e->getMessage());
    sendResponse('gagal', 'Terjadi kesalahan sistem', [
        'error' => $e->getMessage()
    ], 500);
}

$conn->close();
?>
