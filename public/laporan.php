<?php
/**
 * Halaman Laporan Absensi
 * Filter laporan berdasarkan tanggal dan santri
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';

// Set page title
$pageTitle = 'Laporan Absensi';

// Handle filter
$filterTanggalMulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-01');
$filterTanggalAkhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');
$filterSantri = isset($_GET['santri']) ? $_GET['santri'] : '';
$filterKelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

// Build query dengan filter
$whereConditions = [];
$whereConditions[] = "DATE(a.waktu_absen) BETWEEN '$filterTanggalMulai' AND '$filterTanggalAkhir'";

if (!empty($filterSantri)) {
    $whereConditions[] = "s.id = " . (int)$filterSantri;
}

if (!empty($filterKelas)) {
    $whereConditions[] = "s.kelas = '" . escape_string($conn, $filterKelas) . "'";
}

$whereClause = implode(' AND ', $whereConditions);

// Query laporan absensi
$queryLaporan = "SELECT a.*, s.nama_santri, s.kelas 
                 FROM absensi a 
                 JOIN santri s ON a.rfid_id = s.rfid_id 
                 WHERE $whereClause
                 ORDER BY a.waktu_absen DESC";
$resultLaporan = $conn->query($queryLaporan);

// Query daftar santri untuk dropdown
$querySantriList = "SELECT id, nama_santri, kelas FROM santri ORDER BY nama_santri ASC";
$resultSantriList = $conn->query($querySantriList);

// Query daftar kelas unik
$queryKelasList = "SELECT DISTINCT kelas FROM santri ORDER BY kelas ASC";
$resultKelasList = $conn->query($queryKelasList);

// Include header
include '../includes/header.php';
?>

<!-- Header Section -->
<div class="mb-8 fade-in">
    <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-3">
        <i class="fas fa-file-alt text-blue-600"></i>
        <span>Laporan Absensi</span>
    </h2>
    <p class="text-gray-600 mt-2">Filter dan export laporan absensi santri</p>
</div>

<!-- Filter Form -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-6 fade-in">
    <form method="GET" action="laporan.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- Tanggal Mulai -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="far fa-calendar"></i> Tanggal Mulai
            </label>
            <input type="date" name="tanggal_mulai" 
                   value="<?php echo $filterTanggalMulai; ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        
        <!-- Tanggal Akhir -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="far fa-calendar"></i> Tanggal Akhir
            </label>
            <input type="date" name="tanggal_akhir" 
                   value="<?php echo $filterTanggalAkhir; ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        
        <!-- Filter Santri -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user"></i> Santri
            </label>
            <select name="santri" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Santri</option>
                <?php while ($santri = $resultSantriList->fetch_assoc()): ?>
                    <option value="<?php echo $santri['id']; ?>" <?php echo ($filterSantri == $santri['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($santri['nama_santri']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <!-- Filter Kelas -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-book"></i> Kelas
            </label>
            <select name="kelas" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Kelas</option>
                <?php while ($kelas = $resultKelasList->fetch_assoc()): ?>
                    <option value="<?php echo $kelas['kelas']; ?>" <?php echo ($filterKelas == $kelas['kelas']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($kelas['kelas']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <!-- Button Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 flex items-center justify-center space-x-2">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
        </div>
    </form>
</div>

<!-- Action Buttons -->
<div class="flex space-x-4 mb-6">
    <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 flex items-center space-x-2">
        <i class="fas fa-print"></i>
        <span>Print</span>
    </button>
    
    <button onclick="exportToCSV('table-laporan', 'laporan_absensi_<?php echo date('Y-m-d'); ?>.csv')" 
            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 flex items-center space-x-2">
        <i class="fas fa-file-excel"></i>
        <span>Export CSV</span>
    </button>
    
    <a href="laporan.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 flex items-center space-x-2">
        <i class="fas fa-redo"></i>
        <span>Reset Filter</span>
    </a>
</div>

<!-- Tabel Laporan -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden fade-in">
    <!-- Table Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
        <h3 class="text-white text-xl font-semibold flex items-center space-x-2">
            <i class="fas fa-table"></i>
            <span>Data Laporan Absensi</span>
        </h3>
        <span class="bg-white text-blue-600 px-3 py-1 rounded-full text-sm font-semibold">
            <?php echo $resultLaporan->num_rows; ?> Data
        </span>
    </div>
    
    <!-- Table Content -->
    <div class="overflow-x-auto">
        <table class="w-full" id="table-laporan">
            <thead class="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Santri</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RFID ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($resultLaporan->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $resultLaporan->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo formatTanggalIndo($row['waktu_absen']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <i class="far fa-clock"></i> <?php echo formatWaktu($row['waktu_absen']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_santri']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($row['kelas']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs"><?php echo htmlspecialchars($row['rfid_id']); ?></code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getBadgeStatus($row['status']); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                            Tidak ada data absensi sesuai filter
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Ringkasan Statistik -->
<?php if ($resultLaporan->num_rows > 0): ?>
<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <h4 class="text-lg font-semibold mb-2">Total Absensi</h4>
        <p class="text-4xl font-bold"><?php echo $resultLaporan->num_rows; ?></p>
        <p class="text-sm opacity-80 mt-1">Data dalam periode yang dipilih</p>
    </div>
    
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <h4 class="text-lg font-semibold mb-2">Periode</h4>
        <p class="text-lg font-bold"><?php echo formatTanggalIndo($filterTanggalMulai); ?></p>
        <p class="text-sm opacity-80">sampai</p>
        <p class="text-lg font-bold"><?php echo formatTanggalIndo($filterTanggalAkhir); ?></p>
    </div>
    
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <h4 class="text-lg font-semibold mb-2">Filter Aktif</h4>
        <p class="text-sm">
            <?php 
            $filterInfo = [];
            if (!empty($filterSantri)) $filterInfo[] = "Santri tertentu";
            if (!empty($filterKelas)) $filterInfo[] = "Kelas: $filterKelas";
            echo !empty($filterInfo) ? implode(', ', $filterInfo) : 'Semua Data';
            ?>
        </p>
    </div>
</div>
<?php endif; ?>

<!-- Print Styles -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    nav, footer, button, .bg-gradient-to-r {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .container {
        max-width: 100% !important;
        padding: 0 !important;
    }
}
</style>

<?php
// Include footer
include '../includes/footer.php';

// Tutup koneksi database
$conn->close();
?>
