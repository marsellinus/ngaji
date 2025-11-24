<?php
/**
 * Halaman Pengaturan Sistem
 * Hanya bisa diakses oleh Superadmin
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Set page title
$pageTitle = 'Pengaturan Sistem';

// Require admin access
requireAccess('admin');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'action' && strpos($key, 'setting_') === 0) {
            $settingKey = str_replace('setting_', '', $key);
            $settingValue = sanitizeInput($value);
            updateSetting($conn, $settingKey, $settingValue);
        }
    }
    
    logActivity($conn, $_SESSION['user_id'], 'update_settings', 'Mengupdate pengaturan sistem');
    redirect('settings.php?success=Pengaturan berhasil disimpan!');
}

// Get all settings
$querySettings = "SELECT * FROM settings ORDER BY setting_key ASC";
$resultSettings = $conn->query($querySettings);

// Get activity logs (latest 50)
$queryLogs = "SELECT al.*, a.nama_lengkap, a.username
              FROM activity_log al
              LEFT JOIN admin a ON al.admin_id = a.id
              ORDER BY al.created_at DESC
              LIMIT 50";
$resultLogs = $conn->query($queryLogs);

// Include header
include '../includes/header.php';
?>

<!-- Alert notifikasi -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert-auto-hide">
        <?php echo showAlert($_GET['success'], 'success'); ?>
    </div>
<?php endif; ?>

<!-- Header Section -->
<div class="mb-8 fade-in">
    <h2 class="text-3xl font-bold text-gray-800 flex items-center space-x-3">
        <i class="fas fa-cog text-blue-600"></i>
        <span>Pengaturan Sistem</span>
    </h2>
    <p class="text-gray-600 mt-2">Konfigurasi umum aplikasi</p>
</div>

<!-- Tab Navigation -->
<div class="mb-6">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button onclick="showTab('settings')" id="tab-settings" class="tab-button active border-b-2 border-blue-600 py-4 px-1 text-sm font-medium text-blue-600">
                <i class="fas fa-sliders-h"></i> Pengaturan Umum
            </button>
            <button onclick="showTab('logs')" id="tab-logs" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-history"></i> Activity Log
            </button>
            <button onclick="showTab('system')" id="tab-system" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-server"></i> Info Sistem
            </button>
        </nav>
    </div>
</div>

<!-- Tab Content: Pengaturan Umum -->
<div id="content-settings" class="tab-content">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form method="POST" action="settings.php">
            <input type="hidden" name="action" value="update_settings">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php while ($setting = $resultSettings->fetch_assoc()): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo htmlspecialchars($setting['description']); ?>
                        </label>
                        
                        <?php if ($setting['setting_type'] == 'boolean'): ?>
                            <select name="setting_<?php echo $setting['setting_key']; ?>" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="1" <?php echo ($setting['setting_value'] == '1') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo ($setting['setting_value'] == '0') ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        
                        <?php elseif ($setting['setting_type'] == 'number'): ?>
                            <input type="number" 
                                   name="setting_<?php echo $setting['setting_key']; ?>" 
                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        
                        <?php elseif ($setting['setting_type'] == 'time'): ?>
                            <input type="time" 
                                   name="setting_<?php echo $setting['setting_key']; ?>" 
                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        
                        <?php else: ?>
                            <input type="text" 
                                   name="setting_<?php echo $setting['setting_key']; ?>" 
                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php endif; ?>
                        
                        <p class="text-xs text-gray-500 mt-1">Key: <?php echo $setting['setting_key']; ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-300">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tab Content: Activity Log -->
<div id="content-logs" class="tab-content hidden">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h3 class="text-white text-xl font-semibold">
                <i class="fas fa-history"></i> Activity Log (50 Terbaru)
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aktivitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($log = $resultLogs->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm whitespace-nowrap"><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                            <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($log['nama_lengkap'] ?? 'System'); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs"><?php echo htmlspecialchars($log['activity_type']); ?></code>
                            </td>
                            <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($log['description']); ?></td>
                            <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tab Content: Info Sistem -->
<div id="content-system" class="tab-content hidden">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-server text-blue-600"></i> Server Info</h4>
            <div class="space-y-2 text-sm">
                <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                <p><strong>MySQL Version:</strong> <?php echo $conn->server_info; ?></p>
                <p><strong>Max Upload:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-database text-green-600"></i> Database Info</h4>
            <div class="space-y-2 text-sm">
                <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                <p><strong>Tables:</strong>
                    <?php
                    $tables = $conn->query("SHOW TABLES");
                    echo $tables->num_rows;
                    ?>
                </p>
                <p><strong>Charset:</strong> <?php echo $conn->character_set_name(); ?></p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-chart-bar text-purple-600"></i> Statistics</h4>
            <div class="space-y-2 text-sm">
                <p><strong>Total Santri:</strong> <?php echo getTotalSantri($conn); ?></p>
                <p><strong>Total Absensi:</strong>
                    <?php
                    $totalAbsensi = $conn->query("SELECT COUNT(*) as total FROM absensi")->fetch_assoc();
                    echo $totalAbsensi['total'];
                    ?>
                </p>
                <p><strong>Total Admin:</strong>
                    <?php
                    $totalAdmin = $conn->query("SELECT COUNT(*) as total FROM admin")->fetch_assoc();
                    echo $totalAdmin['total'];
                    ?>
                </p>
                <p><strong>Total Activity:</strong>
                    <?php
                    $totalActivity = $conn->query("SELECT COUNT(*) as total FROM activity_log")->fetch_assoc();
                    echo $totalActivity['total'];
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-600', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active', 'border-blue-600', 'text-blue-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}
</script>

<?php
include '../includes/footer.php';
$conn->close();
?>
