<?php
/**
 * Halaman Tambah/Edit/Hapus Santri
 * CRUD untuk data santri
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Require admin access
requireAccess('admin');

// Set page title
$pageTitle = 'Data Santri';

// Handle form submission untuk tambah/edit santri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // TAMBAH SANTRI BARU
    if ($action === 'tambah') {
        $nama = sanitizeInput($_POST['nama_santri']);
        $rfid_id = sanitizeInput($_POST['rfid_id']);
        $kelas = sanitizeInput($_POST['kelas']);
        $keterangan = sanitizeInput($_POST['keterangan'] ?? '');
        
        // Validasi input
        if (isEmptyInput($nama) || isEmptyInput($rfid_id)) {
            redirect('tambah_santri.php?error=Nama dan RFID ID harus diisi!');
        }
        
        // Validasi format RFID
        if (!isValidRFID($rfid_id)) {
            redirect('tambah_santri.php?error=Format RFID tidak valid! Minimal 4 karakter alfanumerik.');
        }
        
        // Cek apakah RFID sudah terdaftar
        $checkQuery = "SELECT id FROM santri WHERE rfid_id = '" . escape_string($conn, $rfid_id) . "'";
        $checkResult = $conn->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            redirect('tambah_santri.php?error=RFID ID sudah terdaftar!');
        }
        
        // Generate username dan password default untuk santri
        $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $status = 'aktif';
        
        // Hash password jika diisi
        $hashedPassword = null;
        if (!empty($password)) {
            $hashedPassword = hashPassword($password);
        }
        
        // Insert data santri baru dengan username/password (opsional)
        if (!empty($username) && !empty($hashedPassword)) {
            $insertQuery = "INSERT INTO santri (nama_santri, rfid_id, username, password, kelas, keterangan, status) 
                           VALUES ('" . escape_string($conn, $nama) . "', 
                                   '" . escape_string($conn, $rfid_id) . "', 
                                   '" . escape_string($conn, $username) . "', 
                                   '$hashedPassword', 
                                   '" . escape_string($conn, $kelas) . "', 
                                   '" . escape_string($conn, $keterangan) . "', 
                                   '$status')";
        } else {
            $insertQuery = "INSERT INTO santri (nama_santri, rfid_id, kelas, keterangan, status) 
                           VALUES ('" . escape_string($conn, $nama) . "', 
                                   '" . escape_string($conn, $rfid_id) . "', 
                                   '" . escape_string($conn, $kelas) . "', 
                                   '" . escape_string($conn, $keterangan) . "', 
                                   '$status')";
        }
        
        if ($conn->query($insertQuery)) {
            redirect('tambah_santri.php?success=Santri berhasil ditambahkan!');
        } else {
            redirect('tambah_santri.php?error=Gagal menambahkan santri: ' . $conn->error);
        }
    }
    
    // EDIT SANTRI
    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $nama = sanitizeInput($_POST['nama_santri']);
        $rfid_id = sanitizeInput($_POST['rfid_id']);
        $kelas = sanitizeInput($_POST['kelas']);
        $keterangan = sanitizeInput($_POST['keterangan'] ?? '');
        
        // Validasi input
        if (isEmptyInput($nama) || isEmptyInput($rfid_id)) {
            redirect('tambah_santri.php?error=Nama dan RFID ID harus diisi!');
        }
        
        // Validasi format RFID
        if (!isValidRFID($rfid_id)) {
            redirect('tambah_santri.php?error=Format RFID tidak valid! Minimal 4 karakter alfanumerik.');
        }
        
        // Cek apakah RFID sudah terdaftar oleh santri lain
        $checkQuery = "SELECT id FROM santri WHERE rfid_id = '" . escape_string($conn, $rfid_id) . "' AND id != $id";
        $checkResult = $conn->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            redirect('tambah_santri.php?error=RFID ID sudah terdaftar oleh santri lain!');
        }
        
        // Update data santri (RFID sekarang bisa diubah)
        $updateQuery = "UPDATE santri 
                       SET nama_santri = '" . escape_string($conn, $nama) . "',
                           rfid_id = '" . escape_string($conn, $rfid_id) . "',
                           kelas = '" . escape_string($conn, $kelas) . "',
                           keterangan = '" . escape_string($conn, $keterangan) . "'
                       WHERE id = $id";
        
        if ($conn->query($updateQuery)) {
            redirect('tambah_santri.php?success=Data santri berhasil diupdate!');
        } else {
            redirect('tambah_santri.php?error=Gagal mengupdate data: ' . $conn->error);
        }
    }
}

// Handle hapus santri
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    $deleteQuery = "DELETE FROM santri WHERE id = $id";
    
    if ($conn->query($deleteQuery)) {
        redirect('tambah_santri.php?success=Santri berhasil dihapus!');
    } else {
        redirect('tambah_santri.php?error=Gagal menghapus santri: ' . $conn->error);
    }
}

// Ambil data santri untuk edit (jika ada parameter edit)
$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = "SELECT * FROM santri WHERE id = $editId";
    $editResult = $conn->query($editQuery);
    if ($editResult->num_rows > 0) {
        $editData = $editResult->fetch_assoc();
    }
}

// Ambil semua data santri
$querySantri = "SELECT s.*, 
                COUNT(a.id) as total_absensi,
                MAX(a.waktu_absen) as absensi_terakhir
                FROM santri s
                LEFT JOIN absensi a ON s.rfid_id = a.rfid_id
                GROUP BY s.id
                ORDER BY s.created_at DESC";
$resultSantri = $conn->query($querySantri);

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
        <i class="fas fa-users text-blue-600"></i>
        <span>Data Santri</span>
    </h2>
    <p class="text-gray-600 mt-2">Kelola data santri dan RFID ID</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Tambah/Edit Santri -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-lg p-6 fade-in sticky top-4">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                <i class="fas fa-user-plus text-green-600"></i>
                <span><?php echo $editData ? 'Edit Santri' : 'Tambah Santri Baru'; ?></span>
            </h3>
            
            <form method="POST" action="tambah_santri.php" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $editData ? 'edit' : 'tambah'; ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php endif; ?>
                
                <!-- Nama Santri -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user"></i> Nama Santri *
                    </label>
                    <input type="text" name="nama_santri" required
                           value="<?php echo $editData ? htmlspecialchars($editData['nama_santri']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Masukkan nama lengkap">
                </div>
                
                <!-- RFID ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card"></i> RFID ID *
                    </label>
                    <input type="text" name="rfid_id" required
                           value="<?php echo $editData ? htmlspecialchars($editData['rfid_id']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Contoh: A1B2C3D4">
                    <?php if ($editData): ?>
                        <p class="text-xs text-yellow-600 mt-1">
                            <i class="fas fa-exclamation-triangle"></i> Hati-hati saat mengubah RFID ID - pastikan tidak duplikat!
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Kelas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-book"></i> Kelas
                    </label>
                    <select name="kelas" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="Iqro 1" <?php echo ($editData && $editData['kelas'] == 'Iqro 1') ? 'selected' : ''; ?>>Iqro 1</option>
                        <option value="Iqro 2" <?php echo ($editData && $editData['kelas'] == 'Iqro 2') ? 'selected' : ''; ?>>Iqro 2</option>
                        <option value="Iqro 3" <?php echo ($editData && $editData['kelas'] == 'Iqro 3') ? 'selected' : ''; ?>>Iqro 3</option>
                        <option value="Iqro 4" <?php echo ($editData && $editData['kelas'] == 'Iqro 4') ? 'selected' : ''; ?>>Iqro 4</option>
                        <option value="Iqro 5" <?php echo ($editData && $editData['kelas'] == 'Iqro 5') ? 'selected' : ''; ?>>Iqro 5</option>
                        <option value="Iqro 6" <?php echo ($editData && $editData['kelas'] == 'Iqro 6') ? 'selected' : ''; ?>>Iqro 6</option>
                        <option value="Al-Quran" <?php echo ($editData && $editData['kelas'] == 'Al-Quran') ? 'selected' : ''; ?>>Al-Quran</option>
                        <option value="Umum" <?php echo ($editData && $editData['kelas'] == 'Umum') ? 'selected' : ''; ?>>Umum</option>
                    </select>
                </div>
                
                <?php if (!$editData): ?>
                <!-- Username untuk Login (hanya saat tambah baru) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-circle"></i> Username (Opsional)
                    </label>
                    <input type="text" name="username"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Username untuk login santri">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i> Biarkan kosong jika santri tidak perlu login
                    </p>
                </div>
                
                <!-- Password untuk Login (hanya saat tambah baru) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock"></i> Password (Opsional)
                    </label>
                    <input type="password" name="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Password untuk login santri">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i> Santri bisa login untuk lihat log absensi mereka
                    </p>
                </div>
                <?php endif; ?>
                
                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-comment"></i> Keterangan
                    </label>
                    <textarea name="keterangan" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Keterangan tambahan (opsional)"><?php echo $editData ? htmlspecialchars($editData['keterangan']) : ''; ?></textarea>
                </div>
                
                <!-- Buttons -->
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 flex items-center justify-center space-x-2">
                        <i class="fas fa-save"></i>
                        <span><?php echo $editData ? 'Update' : 'Simpan'; ?></span>
                    </button>
                    
                    <?php if ($editData): ?>
                        <a href="tambah_santri.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 flex items-center justify-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabel Data Santri -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden fade-in">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white text-xl font-semibold flex items-center space-x-2">
                    <i class="fas fa-list"></i>
                    <span>Daftar Santri</span>
                </h3>
                <span class="bg-white text-blue-600 px-3 py-1 rounded-full text-sm font-semibold">
                    <?php echo $resultSantri->num_rows; ?> Santri
                </span>
            </div>
            
            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RFID ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Absensi</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($resultSantri->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = $resultSantri->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo $no++; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_santri']); ?></div>
                                                <?php if ($row['absensi_terakhir']): ?>
                                                    <div class="text-xs text-gray-500">
                                                        <i class="far fa-clock"></i> <?php echo formatWaktu($row['absensi_terakhir']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <code class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs"><?php echo htmlspecialchars($row['rfid_id']); ?></code>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($row['kelas']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                            <?php echo $row['total_absensi']; ?>x
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="tambah_santri.php?edit=<?php echo $row['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-800 transition duration-200" 
                                               title="Edit">
                                                <i class="fas fa-edit text-lg"></i>
                                            </a>
                                            <a href="tambah_santri.php?hapus=<?php echo $row['id']; ?>" 
                                               onclick="return confirm('Yakin ingin menghapus santri ini? Data absensi juga akan terhapus!')"
                                               class="text-red-600 hover:text-red-800 transition duration-200" 
                                               title="Hapus">
                                                <i class="fas fa-trash text-lg"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-user-slash text-4xl mb-2 block text-gray-300"></i>
                                    Belum ada data santri
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';

// Tutup koneksi database
$conn->close();
?>
