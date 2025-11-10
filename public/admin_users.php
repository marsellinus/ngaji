<?php
/**
 * Management Admin
 * Hanya bisa diakses oleh Admin
 * Admin bisa kelola admin lain (tidak ada role lain)
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Set page title
$pageTitle = 'Management Admin';

// Require admin access
requireAdmin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // TAMBAH ADMIN BARU
    if ($action === 'tambah') {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        $nama_lengkap = sanitizeInput($_POST['nama_lengkap']);
        $email = sanitizeInput($_POST['email']);
        
        // Validasi
        if (empty($username) || empty($password) || empty($nama_lengkap)) {
            redirect('admin_users.php?error=Semua field harus diisi!');
        }
        
        // Cek username sudah ada atau belum
        $checkQuery = "SELECT id FROM admin WHERE username = '" . escape_string($conn, $username) . "'";
        if ($conn->query($checkQuery)->num_rows > 0) {
            redirect('admin_users.php?error=Username sudah digunakan!');
        }
        
        // Hash password
        $hashedPassword = hashPassword($password);
        
        // Insert admin baru (tidak ada level lagi, semuanya admin)
        $insertQuery = "INSERT INTO admin (username, password, nama_lengkap, email, status) 
                       VALUES ('" . escape_string($conn, $username) . "', 
                               '$hashedPassword', 
                               '" . escape_string($conn, $nama_lengkap) . "', 
                               '" . escape_string($conn, $email) . "', 
                               'aktif')";
        
        if ($conn->query($insertQuery)) {
            logActivity($conn, $_SESSION['user_id'], 'create_admin', "Menambah admin baru: $username");
            redirect('admin_users.php?success=Admin berhasil ditambahkan!');
        } else {
            redirect('admin_users.php?error=Gagal menambahkan admin!');
        }
    }
    
    // EDIT ADMIN
    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $nama_lengkap = sanitizeInput($_POST['nama_lengkap']);
        $email = sanitizeInput($_POST['email']);
        $status = sanitizeInput($_POST['status']);
        
        $updateQuery = "UPDATE admin 
                       SET nama_lengkap = '" . escape_string($conn, $nama_lengkap) . "',
                           email = '" . escape_string($conn, $email) . "',
                           status = '" . escape_string($conn, $status) . "'
                       WHERE id = $id";
        
        if ($conn->query($updateQuery)) {
            logActivity($conn, $_SESSION['user_id'], 'edit_admin', "Mengedit admin ID: $id");
            redirect('admin_users.php?success=Admin berhasil diupdate!');
        } else {
            redirect('admin_users.php?error=Gagal mengupdate admin!');
        }
    }
    
    // RESET PASSWORD
    elseif ($action === 'reset_password') {
        $id = (int)$_POST['id'];
        $new_password = $_POST['new_password'];
        
        if (empty($new_password)) {
            redirect('admin_users.php?error=Password baru harus diisi!');
        }
        
        $hashedPassword = hashPassword($new_password);
        $updateQuery = "UPDATE admin SET password = '$hashedPassword' WHERE id = $id";
        
        if ($conn->query($updateQuery)) {
            logActivity($conn, $_SESSION['user_id'], 'reset_password', "Reset password admin ID: $id");
            redirect('admin_users.php?success=Password berhasil direset!');
        } else {
            redirect('admin_users.php?error=Gagal reset password!');
        }
    }
}

// Handle hapus admin
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    // Tidak bisa hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        redirect('admin_users.php?error=Tidak dapat menghapus akun sendiri!');
    }
    
    $deleteQuery = "DELETE FROM admin WHERE id = $id";
    
    if ($conn->query($deleteQuery)) {
        logActivity($conn, $_SESSION['user_id'], 'delete_admin', "Menghapus admin ID: $id");
        redirect('admin_users.php?success=Admin berhasil dihapus!');
    } else {
        redirect('admin_users.php?error=Gagal menghapus admin!');
    }
}

// Get admin for edit
$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = "SELECT * FROM admin WHERE id = $editId";
    $editResult = $conn->query($editQuery);
    if ($editResult->num_rows > 0) {
        $editData = $editResult->fetch_assoc();
    }
}

// Get all admins
$queryAdmins = "SELECT a.*, 
                (SELECT COUNT(*) FROM activity_log WHERE admin_id = a.id) as total_activity
                FROM admin a
                ORDER BY a.created_at DESC";
$resultAdmins = $conn->query($queryAdmins);

// Include header
include '../includes/header.php';
?>

<!-- Alert notifikasi -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert-auto-hide">
        <?php echo showAlert($_GET['success'], 'success'); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert-auto-hide">
        <?php echo showAlert($_GET['error'], 'error'); ?>
    </div>
<?php endif; ?>

<!-- Header Section -->
<div class="mb-8 fade-in">
    <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-3">
        <i class="fas fa-users-cog text-blue-600"></i>
        <span>Management Admin</span>
    </h2>
    <p class="text-gray-600 mt-2">Kelola akun admin sistem</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Tambah/Edit Admin -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-lg p-6 fade-in sticky top-4">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                <i class="fas fa-user-plus text-green-600"></i>
                <span><?php echo $editData ? 'Edit Admin' : 'Tambah Admin Baru'; ?></span>
            </h3>
            
            <form method="POST" action="admin_users.php" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $editData ? 'edit' : 'tambah'; ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php endif; ?>
                
                <!-- Username (hanya untuk tambah baru) -->
                <?php if (!$editData): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user"></i> Username *
                    </label>
                    <input type="text" name="username" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Username untuk login">
                </div>
                
                <!-- Password (hanya untuk tambah baru) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock"></i> Password *
                    </label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Password">
                </div>
                <?php endif; ?>
                
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card"></i> Nama Lengkap *
                    </label>
                    <input type="text" name="nama_lengkap" required
                           value="<?php echo $editData ? htmlspecialchars($editData['nama_lengkap']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" name="email"
                           value="<?php echo $editData ? htmlspecialchars($editData['email']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Status (hanya untuk edit) -->
                <?php if ($editData): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on"></i> Status
                    </label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="aktif" <?php echo ($editData['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo ($editData['status'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
                <?php endif; ?>
                
                <!-- Buttons -->
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-300">
                        <i class="fas fa-save"></i> <?php echo $editData ? 'Update' : 'Simpan'; ?>
                    </button>
                    
                    <?php if ($editData): ?>
                        <a href="admin_users.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 text-center">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabel Admin -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden fade-in">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h3 class="text-white text-xl font-semibold flex items-center justify-between">
                    <span><i class="fas fa-list"></i> Daftar Admin</span>
                    <span class="bg-white text-blue-600 px-3 py-1 rounded-full text-sm"><?php echo $resultAdmins->num_rows; ?> User</span>
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($resultAdmins->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = $resultAdmins->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-4 text-sm"><?php echo $no++; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($row['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm"><code class="bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($row['username']); ?></code></td>
                                    <td class="px-6 py-4">
                                        <?php if ($row['status'] == 'aktif'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-white bg-green-500 rounded-full">Aktif</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded-full">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="admin_users.php?edit=<?php echo $row['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-800" title="Edit">
                                                <i class="fas fa-edit text-lg"></i>
                                            </a>
                                            
                                            <?php if ($row['id'] != $_SESSION['admin_id']): ?>
                                                <a href="admin_users.php?hapus=<?php echo $row['id']; ?>" 
                                                   onclick="return confirm('Yakin ingin menghapus admin ini?')"
                                                   class="text-red-600 hover:text-red-800" title="Hapus">
                                                    <i class="fas fa-trash text-lg"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada data admin</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
$conn->close();
?>
