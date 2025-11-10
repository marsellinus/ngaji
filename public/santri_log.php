<?php
/**
 * Halaman Log Absensi Santri
 * Santri hanya bisa lihat log absensi mereka sendiri
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Cek login
requireLogin();

// Kalau admin yang akses, redirect ke laporan umum
if (isAdmin()) {
    redirect('laporan.php');
}

// Kalau bukan santri, logout
if (!isSantri()) {
    logout($conn);
}

// Ambil data santri yang login
$santri_id = $_SESSION['user_id'];
$nama_santri = $_SESSION['nama_lengkap'];
$rfid_id = $_SESSION['rfid_id'];
$kelas = $_SESSION['kelas'];

// Handle filter tanggal
$tanggal_dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-01'); // Awal bulan
$tanggal_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d'); // Hari ini

// Query untuk ambil absensi santri ini saja
$query = "SELECT a.*, s.nama_santri, s.kelas 
          FROM absensi a 
          INNER JOIN santri s ON a.rfid_id = s.rfid_id 
          WHERE s.id = $santri_id
          AND DATE(a.waktu_absen) BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
          ORDER BY a.waktu_absen DESC";

$result = $conn->query($query);

// Hitung statistik
$total_hadir = 0;
$total_absensi = 0;

if ($result) {
    $total_absensi = $result->num_rows;
    $result->data_seek(0); // Reset pointer
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'Hadir') {
            $total_hadir++;
        }
    }
    $result->data_seek(0); // Reset pointer lagi
}

// Hitung persentase kehadiran
$persentase = $total_absensi > 0 ? round(($total_hadir / $total_absensi) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Absensi Saya - <?php echo htmlspecialchars($nama_santri); ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <nav class="bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-mosque text-white text-2xl"></i>
                    <span class="text-white text-xl font-bold">Log Absensi Saya</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-white text-sm">
                        <i class="fas fa-user-circle"></i>
                        <span class="font-semibold"><?php echo htmlspecialchars($nama_santri); ?></span>
                        <span class="ml-2 px-2 py-1 text-xs bg-green-500 rounded-full"><?php echo htmlspecialchars($kelas); ?></span>
                    </div>
                    <a href="logout.php" class="text-white hover:bg-white hover:bg-opacity-20 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 mb-6 text-white">
            <h2 class="text-2xl font-bold mb-2">
                <i class="fas fa-hand-peace"></i> Assalamualaikum, <?php echo explode(' ', $nama_santri)[0]; ?>!
            </h2>
            <p class="text-blue-100">Berikut adalah rekap kehadiran Anda</p>
        </div>

        <!-- Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Total Absensi -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-calendar-check text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Total Absensi</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_absensi; ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Hadir -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Hadir</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_hadir; ?></p>
                    </div>
                </div>
            </div>

            <!-- Persentase -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-chart-pie text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Persentase Hadir</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $persentase; ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-filter"></i> Filter Periode
            </h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                    <input type="date" name="dari" value="<?php echo $tanggal_dari; ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="<?php echo $tanggal_sampai; ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-search"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Log Absensi -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list"></i> Riwayat Absensi
                </h3>
                <p class="text-sm text-gray-600">Periode: <?php echo date('d M Y', strtotime($tanggal_dari)); ?> - <?php echo date('d M Y', strtotime($tanggal_sampai)); ?></p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $no++; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <i class="fas fa-calendar text-blue-500"></i>
                                        <?php echo date('d M Y', strtotime($row['waktu_absen'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <i class="fas fa-clock text-purple-500"></i>
                                        <?php echo date('H:i:s', strtotime($row['waktu_absen'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($row['status'] == 'Hadir'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-white bg-green-500 rounded-full">
                                                <i class="fas fa-check"></i> Hadir
                                            </span>
                                        <?php elseif ($row['status'] == 'Izin'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-white bg-yellow-500 rounded-full">
                                                <i class="fas fa-info"></i> Izin
                                            </span>
                                        <?php elseif ($row['status'] == 'Sakit'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-white bg-orange-500 rounded-full">
                                                <i class="fas fa-thermometer"></i> Sakit
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded-full">
                                                <i class="fas fa-times"></i> Tidak Hadir
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($row['keterangan'] ?? '-'); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Belum ada data absensi pada periode ini</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info Footer -->
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                <div class="ml-3">
                    <h4 class="text-sm font-semibold text-blue-800 mb-1">Informasi</h4>
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-id-card"></i> RFID ID Anda: <strong><?php echo htmlspecialchars($rfid_id); ?></strong>
                    </p>
                    <p class="text-sm text-blue-700 mt-1">
                        <i class="fas fa-book-quran"></i> Jangan lupa untuk selalu tap kartu saat datang ke TPQ
                    </p>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Auto refresh setiap 5 menit untuk update data terbaru
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 menit
    </script>
</body>
</html>
<?php $conn->close(); ?>
