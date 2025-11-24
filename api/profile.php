<?php
/**
 * Halaman Profil User
 * User bisa edit profil dan ganti password sendiri
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Set page title
$pageTitle = 'Profil Saya';

// Require login
requireLogin();

// Get admin ID from session
$adminId = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitizeInput($_POST['action']);
    
    if ($action === 'update_profile') {
        $namaLengkap = sanitizeInput($_POST['nama_lengkap']);
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        
        // Validasi
        if (empty($namaLengkap) || empty($username) || empty($email)) {
            $error = 'Semua field harus diisi!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid!';
        } else {
            // Cek username duplikat (selain user sendiri)
            $stmtCheck = $conn->prepare("SELECT id FROM admin WHERE username = ? AND id != ?");
            $stmtCheck->bind_param("si", $username, $adminId);
            $stmtCheck->execute();
            if ($stmtCheck->get_result()->num_rows > 0) {
                $error = 'Username sudah digunakan!';
            } else {
                // Update profil
                $stmt = $conn->prepare("UPDATE admin SET nama_lengkap = ?, username = ?, email = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("sssi", $namaLengkap, $username, $email, $adminId);
                
                if ($stmt->execute()) {
                    $_SESSION['admin_name'] = $namaLengkap; // Update session
                    logActivity($conn, $adminId, 'update_profile', 'Mengupdate profil pribadi');
                    $success = 'Profil berhasil diupdate!';
                } else {
                    $error = 'Gagal update profil!';
                }
                $stmt->close();
            }
            $stmtCheck->close();
        }
    } 
    elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validasi
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Semua field password harus diisi!';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            // Cek password lama
            $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            
            if (!password_verify($currentPassword, $admin['password'])) {
                $error = 'Password lama salah!';
            } else {
                // Update password
                $hashedPassword = hashPassword($newPassword);
                $stmtUpdate = $conn->prepare("UPDATE admin SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmtUpdate->bind_param("si", $hashedPassword, $adminId);
                
                if ($stmtUpdate->execute()) {
                    logActivity($conn, $adminId, 'change_password', 'Mengganti password');
                    $success = 'Password berhasil diubah!';
                } else {
                    $error = 'Gagal mengubah password!';
                }
                $stmtUpdate->close();
            }
            $stmt->close();
        }
    }
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user activity count
$stmtActivity = $conn->prepare("SELECT COUNT(*) as total FROM activity_log WHERE admin_id = ?");
$stmtActivity->bind_param("i", $adminId);
$stmtActivity->execute();
$activityCount = $stmtActivity->get_result()->fetch_assoc()['total'];
$stmtActivity->close();

// Get latest user activity
$stmtLatest = $conn->prepare("SELECT * FROM activity_log WHERE admin_id = ? ORDER BY created_at DESC LIMIT 10");
$stmtLatest->bind_param("i", $adminId);
$stmtLatest->execute();
$latestActivity = $stmtLatest->get_result();
$stmtLatest->close();

// Include header
include '../includes/header.php';
?>

<!-- Alert notifikasi -->
<?php if ($success): ?>
    <div class="alert-auto-hide">
        <?php echo showAlert($success, 'success'); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert-auto-hide">
        <?php echo showAlert($error, 'danger'); ?>
    </div>
<?php endif; ?>

<!-- Header Section -->
<div class="mb-8 fade-in">
    <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-3">
        <i class="fas fa-user-circle text-blue-600"></i>
        <span>Profil Saya</span>
    </h2>
    <p class="text-gray-600 mt-2">Kelola informasi profil dan keamanan akun Anda</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full text-white text-3xl font-bold mb-4">
                    <?php echo strtoupper(substr($admin['nama_lengkap'], 0, 2)); ?>
                </div>
                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($admin['nama_lengkap']); ?></h3>
                <p class="text-gray-500 text-sm mb-2">@<?php echo htmlspecialchars($admin['username']); ?></p>
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                    <?php echo strtoupper($admin['level']); ?>
                </span>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Email:</span>
                    <span class="font-medium"><?php echo htmlspecialchars($admin['email']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium <?php echo $admin['status'] == 'active' ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $admin['status'] == 'active' ? 'Aktif' : 'Nonaktif'; ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Login Terakhir:</span>
                    <span class="font-medium"><?php echo $admin['last_login'] ? date('d/m/Y H:i', strtotime($admin['last_login'])) : '-'; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Aktivitas:</span>
                    <span class="font-medium"><?php echo $activityCount; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Terdaftar Sejak:</span>
                    <span class="font-medium"><?php echo date('d M Y', strtotime($admin['created_at'])); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Latest Activity Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-history text-blue-600"></i> Aktivitas Terakhir
            </h4>
            <div class="space-y-3">
                <?php while ($activity = $latestActivity->fetch_assoc()): ?>
                    <div class="text-sm border-l-2 border-blue-600 pl-3">
                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($activity['description']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <!-- Edit Forms -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Edit Profile Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-edit text-blue-600 mr-2"></i> Edit Profil
            </h3>
            
            <form method="POST" action="profile.php">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" 
                               value="<?php echo htmlspecialchars($admin['nama_lengkap']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                        <input type="text" name="username" 
                               value="<?php echo htmlspecialchars($admin['username']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" 
                               value="<?php echo htmlspecialchars($admin['email']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-300">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Change Password Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-key text-blue-600 mr-2"></i> Ganti Password
            </h3>
            
            <form method="POST" action="profile.php">
                <input type="hidden" name="action" value="change_password">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Lama *</label>
                        <div class="relative">
                            <input type="password" name="current_password" id="current_password"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            <button type="button" onclick="togglePassword('current_password')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="current_password-icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru * (min. 6 karakter)</label>
                        <div class="relative">
                            <input type="password" name="new_password" id="new_password"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            <button type="button" onclick="togglePassword('new_password')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="new_password-icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru *</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="confirm_password"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            <button type="button" onclick="togglePassword('confirm_password')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="confirm_password-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-300">
                        <i class="fas fa-lock"></i> Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php
include '../includes/footer.php';
$conn->close();
?>
