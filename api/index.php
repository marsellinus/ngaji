<?php
/**
 * Dashboard Utama
 * Menampilkan data absensi terbaru dan statistik
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Require admin access
requireAccess('admin');

// Set page title
$pageTitle = 'Dashboard';

// Ambil data statistik
$totalSantri = getTotalSantri($conn);
$totalAbsensiHariIni = getTotalAbsensiHariIni($conn);

// Ambil data absensi terbaru (30 data terakhir)
$queryAbsensi = "SELECT a.*, s.nama_santri, s.kelas 
                 FROM absensi a 
                 JOIN santri s ON a.rfid_id = s.rfid_id 
                 ORDER BY a.waktu_absen DESC 
                 LIMIT 30";
$resultAbsensi = $conn->query($queryAbsensi);

// Hitung persentase kehadiran hari ini
$persentaseKehadiran = $totalSantri > 0 ? round(($totalAbsensiHariIni / $totalSantri) * 100) : 0;

// Include header
include '../includes/header.php';
?>

<!-- Alert notifikasi jika ada -->
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
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-3">
                <i class="fas fa-chart-line text-blue-600"></i>
                <span>Dashboard Absensi</span>
            </h2>
            <p class="text-gray-600 mt-2">
                <i class="far fa-calendar-alt"></i> 
                <?php echo formatTanggalIndo(date('Y-m-d')); ?>
            </p>
        </div>
        
        <!-- Refresh Button -->
        <button onclick="location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition duration-300 flex items-center space-x-2">
            <i class="fas fa-sync-alt"></i>
            <span>Refresh</span>
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Santri -->
    <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Santri</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $totalSantri; ?></h3>
                <p class="text-xs text-gray-400 mt-1">Santri terdaftar</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-full">
                <i class="fas fa-users text-blue-600 text-3xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Absensi Hari Ini -->
    <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Hadir Hari Ini</p>
                <h3 class="text-3xl font-bold text-green-600 mt-2"><?php echo $totalAbsensiHariIni; ?></h3>
                <p class="text-xs text-gray-400 mt-1">Dari <?php echo $totalSantri; ?> santri</p>
            </div>
            <div class="bg-green-100 p-4 rounded-full">
                <i class="fas fa-check-circle text-green-600 text-3xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Persentase Kehadiran -->
    <div class="bg-white rounded-lg shadow-lg p-6 hover-scale">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Persentase Kehadiran</p>
                <h3 class="text-3xl font-bold text-purple-600 mt-2"><?php echo $persentaseKehadiran; ?>%</h3>
                <p class="text-xs text-gray-400 mt-1">Hari ini</p>
            </div>
            <div class="bg-purple-100 p-4 rounded-full">
                <i class="fas fa-chart-pie text-purple-600 text-3xl"></i>
            </div>
        </div>
        <!-- Progress Bar -->
        <div class="mt-4 bg-gray-200 rounded-full h-2">
            <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" style="width: <?php echo $persentaseKehadiran; ?>%"></div>
        </div>
    </div>
</div>

<!-- Tabel Absensi Terbaru -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden fade-in">
    <!-- Table Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-white text-xl font-semibold flex items-center space-x-2">
            <i class="fas fa-clipboard-list"></i>
            <span>Data Absensi Terbaru</span>
        </h3>
        <span class="bg-white text-blue-600 px-3 py-1 rounded-full text-sm font-semibold">
            <?php echo $resultAbsensi->num_rows; ?> Data
        </span>
    </div>
    
    <!-- Table Content -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Santri</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RFID ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Absen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($resultAbsensi->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $resultAbsensi->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_santri']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($row['kelas']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs"><?php echo htmlspecialchars($row['rfid_id']); ?></code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <i class="far fa-clock text-gray-400"></i>
                                <?php echo formatDateTimeLengkap($row['waktu_absen']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getBadgeStatus($row['status']); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                            Belum ada data absensi
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Info Card -->
<div class="mt-8 bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-600 rounded-lg p-6">
    <div class="flex items-start space-x-4">
        <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-600 text-2xl"></i>
        </div>
        <div>
            <h4 class="text-blue-800 font-semibold mb-2">Informasi Sistem</h4>
            <p class="text-blue-700 text-sm leading-relaxed">
                Sistem akan otomatis memperbarui data absensi ketika ESP32 mengirimkan data RFID. 
                Pastikan perangkat ESP32 terhubung ke jaringan yang sama dengan server.
                <br><strong>Endpoint API:</strong> <code class="bg-white px-2 py-1 rounded text-xs">http://your-server/public/absensi.php</code>
            </p>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';

// Tutup koneksi database
$conn->close();
?>
