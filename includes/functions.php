<?php
/**
 * File Functions Helper
 * Berisi fungsi-fungsi bantu untuk sistem absensi ngaji
 */

/**
 * Fungsi untuk escape string agar aman dari SQL Injection
 */
function escape_string($conn, $string) {
    return $conn->real_escape_string(trim($string));
}

/**
 * Fungsi untuk format tanggal Indonesia
 */
function formatTanggalIndo($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

/**
 * Fungsi untuk format waktu (jam:menit)
 */
function formatWaktu($waktu) {
    return date('H:i', strtotime($waktu));
}

/**
 * Fungsi untuk format datetime lengkap
 */
function formatDateTimeLengkap($datetime) {
    return formatTanggalIndo($datetime) . ' pukul ' . formatWaktu($datetime);
}

/**
 * Fungsi untuk validasi RFID ID
 */
function isValidRFID($rfid) {
    // RFID harus alfanumerik dan minimal 4 karakter
    return preg_match('/^[a-zA-Z0-9]{4,}$/', $rfid);
}

/**
 * Fungsi untuk cek apakah santri sudah absen hari ini
 */
function sudahAbsenHariIni($conn, $rfid_id) {
    $today = date('Y-m-d');
    $query = "SELECT id FROM absensi 
              WHERE rfid_id = '" . escape_string($conn, $rfid_id) . "' 
              AND DATE(waktu_absen) = '$today'";
    $result = $conn->query($query);
    return $result->num_rows > 0;
}

/**
 * Fungsi untuk mendapatkan nama santri berdasarkan RFID
 */
function getNamaSantri($conn, $rfid_id) {
    $query = "SELECT nama_santri FROM santri WHERE rfid_id = '" . escape_string($conn, $rfid_id) . "'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['nama_santri'];
    }
    return null;
}

/**
 * Fungsi untuk mendapatkan data santri berdasarkan RFID
 */
function getDataSantri($conn, $rfid_id) {
    $query = "SELECT * FROM santri WHERE rfid_id = '" . escape_string($conn, $rfid_id) . "'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Fungsi untuk menampilkan alert notifikasi
 */
function showAlert($message, $type = 'success') {
    $bgColor = $type === 'success' ? 'bg-green-500' : 'bg-red-500';
    $icon = $type === 'success' ? '✓' : '✗';
    
    return "<div class='$bgColor text-white px-6 py-4 rounded-lg shadow-lg mb-4 flex items-center'>
                <span class='text-2xl mr-3'>$icon</span>
                <span>$message</span>
            </div>";
}

/**
 * Fungsi untuk redirect halaman
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Fungsi untuk validasi input kosong
 */
function isEmptyInput($value) {
    return empty(trim($value));
}

/**
 * Fungsi untuk sanitasi input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Fungsi untuk mendapatkan total santri
 */
function getTotalSantri($conn) {
    $query = "SELECT COUNT(*) as total FROM santri";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Fungsi untuk mendapatkan total absensi hari ini
 */
function getTotalAbsensiHariIni($conn) {
    $today = date('Y-m-d');
    $query = "SELECT COUNT(*) as total FROM absensi WHERE DATE(waktu_absen) = '$today'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Fungsi untuk mendapatkan badge status
 */
function getBadgeStatus($status) {
    $badges = [
        'Hadir' => '<span class="px-3 py-1 text-xs font-semibold text-white bg-green-500 rounded-full">Hadir</span>',
        'Tidak Hadir' => '<span class="px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded-full">Tidak Hadir</span>',
        'Izin' => '<span class="px-3 py-1 text-xs font-semibold text-white bg-yellow-500 rounded-full">Izin</span>',
        'Sakit' => '<span class="px-3 py-1 text-xs font-semibold text-white bg-blue-500 rounded-full">Sakit</span>'
    ];
    
    return $badges[$status] ?? '<span class="px-3 py-1 text-xs font-semibold text-white bg-gray-500 rounded-full">' . $status . '</span>';
}
?>
