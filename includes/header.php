<?php
/**
 * Header Template
 * Bagian header yang akan di-include di setiap halaman
 */

// Include auth functions
include_once dirname(__FILE__) . '/auth.php';

// Check session timeout
checkSessionTimeout();

// Protect page (require login)
requireLogin();

// Kalau santri akses halaman admin, redirect
if (!isAdmin() && !in_array(basename($_SERVER['PHP_SELF']), ['santri_log.php', 'logout.php'])) {
    redirect('santri_log.php');
}

// Get current user info
$currentUser = getCurrentUser($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Absensi Ngaji</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Custom Styles -->
    <link href="css/custom.css" rel="stylesheet">
    
    <!-- Inline Styles -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hover-scale:hover {
            transform: scale(1.02);
            transition: all 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo & Title -->
                <div class="flex items-center space-x-3">
                    <div class="bg-white p-2 rounded-lg">
                        <i class="fas fa-mosque text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-white text-2xl font-bold">Absensi Ngaji</h1>
                        <p class="text-blue-200 text-sm">Sistem Absensi IoT ESP32</p>
                    </div>
                </div>
                
                <!-- Navigation Menu -->
                <div class="flex items-center space-x-2">
                    <a href="laporan.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-600'; ?> px-4 py-2 rounded-lg transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-file-alt"></i>
                        <span class="hidden md:inline">Laporan Absensi</span>
                    </a>
                    
                    <?php if (checkAccess('admin')): ?>
                    <a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-600'; ?> px-4 py-2 rounded-lg transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-home"></i>
                        <span class="hidden md:inline">Dashboard</span>
                    </a>
                    <a href="tambah_santri.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'tambah_santri.php') ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-600'; ?> px-4 py-2 rounded-lg transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-user-plus"></i>
                        <span class="hidden md:inline">Data Santri</span>
                    </a>
                    <a href="admin_users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_users.php') ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-600'; ?> px-4 py-2 rounded-lg transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-users-cog"></i>
                        <span class="hidden md:inline">Kelola User</span>
                    </a>
                    <a href="activity_logs.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'activity_logs.php') ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-600'; ?> px-4 py-2 rounded-lg transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-history"></i>
                        <span class="hidden md:inline">Activity Log</span>
                    </a>
                    <a href="settings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-600'; ?> px-4 py-2 rounded-lg transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-cog"></i>
                        <span class="hidden md:inline">Pengaturan</span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- User Dropdown -->
                    <div class="relative group">
                        <button class="bg-blue-700 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-user-circle"></i>
                            <span class="hidden md:inline"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User'); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block z-50">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? ''); ?></p>
                                <p class="text-xs text-gray-600"><?php echo getUserTypeBadge($_SESSION['user_type'] ?? 'admin'); ?></p>
                            </div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <div class="container mx-auto px-4 py-8">
