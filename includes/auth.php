<?php
/**
 * Authentication Functions
 * Sistem login dan keamanan
 */

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk login user
 * Support 2 tipe login:
 * 1. Admin - login untuk kelola sistem
 * 2. Santri - login untuk lihat log absensi mereka sendiri
 */
function login($conn, $username, $password) {
    $username = escape_string($conn, $username);
    
    // Cek apakah login sebagai admin
    $query = "SELECT * FROM admin WHERE username = '$username' AND status = 'aktif'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $admin['password'])) {
            // Set session untuk admin
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['username'] = $admin['username'];
            $_SESSION['nama_lengkap'] = $admin['nama_lengkap'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Update last login
            $updateQuery = "UPDATE admin SET last_login = NOW() WHERE id = " . $admin['id'];
            $conn->query($updateQuery);
            
            // Log aktivitas
            logActivity($conn, $admin['id'], 'login', 'Admin berhasil login');
            
            return true;
        }
    }
    
    // Cek apakah login sebagai santri
    $query = "SELECT * FROM santri WHERE username = '$username' AND status = 'aktif'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $santri = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $santri['password'])) {
            // Set session untuk santri
            $_SESSION['user_id'] = $santri['id'];
            $_SESSION['user_type'] = 'santri';
            $_SESSION['username'] = $santri['username'];
            $_SESSION['nama_lengkap'] = $santri['nama_santri'];
            $_SESSION['rfid_id'] = $santri['rfid_id'];
            $_SESSION['kelas'] = $santri['kelas'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            return true;
        }
    }
    
    // Log failed login attempt
    logActivity($conn, null, 'failed_login', 'Percobaan login gagal: ' . $username);
    
    return false;
}

/**
 * Fungsi untuk logout
 */
function logout($conn) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] === 'admin') {
            logActivity($conn, $_SESSION['user_id'], 'logout', 'Admin logout');
        }
    }
    
    session_unset();
    session_destroy();
    redirect('login.php');
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Protect halaman (harus login)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Cek apakah user adalah admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Cek apakah user adalah santri
 */
function isSantri() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'santri';
}

/**
 * Protect halaman admin (hanya admin yang bisa akses)
 */
function requireAdmin() {
    if (!isAdmin()) {
        if (!isLoggedIn()) {
            redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        } else {
            // Logged in tapi bukan admin - redirect ke halaman santri
            redirect('santri_log.php?error=' . urlencode('Akses ditolak! Halaman ini hanya untuk admin.'));
        }
    }
}

/**
 * DEPRECATED: Fungsi lama untuk backward compatibility
 * Gunakan isAdmin() atau requireAdmin() untuk kode baru
 */
function checkAccess($required_level) {
    // Sekarang hanya cek apakah admin
    return isAdmin();
}

/**
 * Require level akses tertentu (deprecated, gunakan requireAdmin)
 */
function requireAccess($required_level) {
    requireAdmin();
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log aktivitas user
 */
function logActivity($conn, $admin_id, $activity_type, $description) {
    $admin_id_safe = $admin_id ? (int)$admin_id : 'NULL';
    $activity_type = escape_string($conn, $activity_type);
    $description = escape_string($conn, $description);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $query = "INSERT INTO activity_log (admin_id, activity_type, description, ip_address, user_agent) 
              VALUES ($admin_id_safe, '$activity_type', '$description', '$ip', '$user_agent')";
    
    $conn->query($query);
}

/**
 * Get setting value
 */
function getSetting($conn, $key, $default = null) {
    $key = escape_string($conn, $key);
    $query = "SELECT setting_value FROM settings WHERE setting_key = '$key'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['setting_value'];
    }
    
    return $default;
}

/**
 * Update setting value
 */
function updateSetting($conn, $key, $value) {
    $key = escape_string($conn, $key);
    $value = escape_string($conn, $value);
    
    $query = "UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'";
    return $conn->query($query);
}

/**
 * Get current user info (admin atau santri)
 */
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = (int)$_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];
    
    if ($user_type === 'admin') {
        $query = "SELECT * FROM admin WHERE id = $user_id";
    } else {
        $query = "SELECT * FROM santri WHERE id = $user_id";
    }
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * DEPRECATED: Alias untuk getCurrentUser
 */
function getCurrentAdmin($conn) {
    return getCurrentUser($conn);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        $timeout = 30 * 60; // 30 menit default
        
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
            session_unset();
            session_destroy();
            redirect('login.php?error=Sesi Anda telah berakhir. Silakan login kembali.');
        }
        
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

/**
 * Get user type badge HTML
 */
function getUserTypeBadge($user_type) {
    $badges = [
        'admin' => '<span class="px-2 py-1 text-xs font-semibold text-white bg-blue-600 rounded-full">Admin</span>',
        'santri' => '<span class="px-2 py-1 text-xs font-semibold text-white bg-green-600 rounded-full">Santri</span>'
    ];
    
    return $badges[$user_type] ?? '<span class="px-2 py-1 text-xs font-semibold text-white bg-gray-600 rounded-full">' . $user_type . '</span>';
}

/**
 * DEPRECATED: Alias untuk getUserTypeBadge
 */
function getLevelBadge($level) {
    return getUserTypeBadge($level);
}
?>
