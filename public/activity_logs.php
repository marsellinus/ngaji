<?php
/**
 * Halaman Activity Logs
 * Menampilkan semua aktivitas sistem dengan filter
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Set page title
$pageTitle = 'Activity Log';

// Require admin access
requireAccess('admin');

// Pagination
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter parameters
$filterUser = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$filterType = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$filterDate = isset($_GET['date']) ? sanitizeInput($_GET['date']) : '';

// Build query
$whereConditions = [];
$params = [];
$types = '';

if ($filterUser > 0) {
    $whereConditions[] = "al.admin_id = ?";
    $params[] = $filterUser;
    $types .= 'i';
}

if (!empty($filterType)) {
    $whereConditions[] = "al.activity_type = ?";
    $params[] = $filterType;
    $types .= 's';
}

if (!empty($filterDate)) {
    $whereConditions[] = "DATE(al.created_at) = ?";
    $params[] = $filterDate;
    $types .= 's';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Count total records
$countQuery = "SELECT COUNT(*) as total FROM activity_log al $whereClause";
if (!empty($params)) {
    $stmtCount = $conn->prepare($countQuery);
    if (!empty($types)) {
        $stmtCount->bind_param($types, ...$params);
    }
    $stmtCount->execute();
    $totalRecords = $stmtCount->get_result()->fetch_assoc()['total'];
    $stmtCount->close();
} else {
    $totalRecords = $conn->query($countQuery)->fetch_assoc()['total'];
}

$totalPages = ceil($totalRecords / $limit);

// Get logs with pagination
$query = "SELECT al.*, a.nama_lengkap, a.username
          FROM activity_log al
          LEFT JOIN admin a ON al.admin_id = a.id
          $whereClause
          ORDER BY al.created_at DESC
          LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultLogs = $stmt->get_result();

// Get all users for filter
$allUsers = $conn->query("SELECT id, nama_lengkap FROM admin ORDER BY nama_lengkap ASC");

// Get all activity types for filter
$allTypes = $conn->query("SELECT DISTINCT activity_type FROM activity_log ORDER BY activity_type ASC");

// Include header
include '../includes/header.php';
?>

<!-- Header Section -->
<div class="mb-8 fade-in">
    <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-3">
        <i class="fas fa-history text-blue-600"></i>
        <span>Activity Log</span>
    </h2>
    <p class="text-gray-600 mt-2">Riwayat aktivitas sistem</p>
</div>

<!-- Filter Section -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-filter"></i> Filter
    </h3>
    
    <form method="GET" action="activity_logs.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
            <select name="user" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="0">-- Semua User --</option>
                <?php while ($user = $allUsers->fetch_assoc()): ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo ($filterUser == $user['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['nama_lengkap']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Aktivitas</label>
            <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">-- Semua Tipe --</option>
                <?php while ($type = $allTypes->fetch_assoc()): ?>
                    <option value="<?php echo $type['activity_type']; ?>" <?php echo ($filterType == $type['activity_type']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['activity_type']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-300">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="activity_logs.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition duration-300">
                <i class="fas fa-redo"></i> Reset
            </a>
        </div>
    </form>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Total Aktivitas</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo $totalRecords; ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800">
                    <?php
                    $today = $conn->query("SELECT COUNT(*) as total FROM activity_log WHERE DATE(created_at) = CURDATE()")->fetch_assoc();
                    echo $today['total'];
                    ?>
                </p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-calendar-day text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Minggu Ini</p>
                <p class="text-2xl font-bold text-gray-800">
                    <?php
                    $week = $conn->query("SELECT COUNT(*) as total FROM activity_log WHERE YEARWEEK(created_at) = YEARWEEK(NOW())")->fetch_assoc();
                    echo $week['total'];
                    ?>
                </p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <i class="fas fa-calendar-week text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-800">
                    <?php
                    $month = $conn->query("SELECT COUNT(*) as total FROM activity_log WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetch_assoc();
                    echo $month['total'];
                    ?>
                </p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Activity Log Table -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
        <h3 class="text-white text-xl font-semibold">
            <i class="fas fa-list"></i> Activity Log (Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?>)
        </h3>
        <div class="text-white text-sm">
            Total: <?php echo $totalRecords; ?> record
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Aktivitas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($resultLogs->num_rows == 0): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data activity log</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($log = $resultLogs->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm whitespace-nowrap">#<?php echo $log['id']; ?></td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php echo htmlspecialchars($log['nama_lengkap'] ?? 'System'); ?>
                                <br>
                                <span class="text-xs text-gray-500">@<?php echo htmlspecialchars($log['username'] ?? '-'); ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">
                                    <?php echo htmlspecialchars($log['activity_type']); ?>
                                </code>
                            </td>
                            <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($log['description']); ?></td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <code class="text-xs"><?php echo htmlspecialchars($log['ip_address']); ?></code>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
            <div class="text-sm text-gray-700">
                Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $limit, $totalRecords); ?> dari <?php echo $totalRecords; ?> record
            </div>
            
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $filterUser ? '&user='.$filterUser : ''; ?><?php echo $filterType ? '&type='.$filterType : ''; ?><?php echo $filterDate ? '&date='.$filterDate : ''; ?>" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">
                        <i class="fas fa-chevron-left"></i> Sebelumnya
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $filterUser ? '&user='.$filterUser : ''; ?><?php echo $filterType ? '&type='.$filterType : ''; ?><?php echo $filterDate ? '&date='.$filterDate : ''; ?>" 
                       class="px-4 py-2 <?php echo ($i == $page) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> rounded-lg transition duration-300">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $filterUser ? '&user='.$filterUser : ''; ?><?php echo $filterType ? '&type='.$filterType : ''; ?><?php echo $filterDate ? '&date='.$filterDate : ''; ?>" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">
                        Selanjutnya <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
include '../includes/footer.php';
$conn->close();
?>
