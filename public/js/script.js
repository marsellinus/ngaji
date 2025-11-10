/**
 * Script JavaScript untuk Sistem Absensi Ngaji
 * Handles interaksi frontend dan real-time updates
 */

// Auto-refresh dashboard setiap 30 detik
let autoRefreshInterval;

function startAutoRefresh() {
    // Hanya aktifkan di halaman index
    if (window.location.pathname.includes('index.php')) {
        autoRefreshInterval = setInterval(() => {
            console.log('Auto-refreshing dashboard...');
            location.reload();
        }, 30000); // 30 detik
    }
}

// Stop auto-refresh jika user sedang interaksi
function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
}

// Konfirmasi delete dengan style
function confirmDelete(nama) {
    return confirm(`Apakah Anda yakin ingin menghapus santri "${nama}"?\n\nSemua data absensi santri ini juga akan terhapus!`);
}

// Format tanggal real-time
function updateCurrentTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    
    const dateElement = document.getElementById('current-time');
    if (dateElement) {
        dateElement.textContent = now.toLocaleDateString('id-ID', options);
    }
}

// Animasi counter untuk statistik
function animateCounter(element, target, duration = 1000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Initialize counters pada load
function initializeCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-counter'));
        animateCounter(counter, target);
    });
}

// Highlight row yang baru ditambahkan
function highlightNewRow() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('highlight')) {
        const row = document.querySelector(`[data-id="${urlParams.get('highlight')}"]`);
        if (row) {
            row.classList.add('bg-yellow-100');
            setTimeout(() => {
                row.classList.remove('bg-yellow-100');
                row.classList.add('transition', 'duration-1000');
            }, 2000);
        }
    }
}

// Search/filter table
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    input.addEventListener('keyup', function() {
        const filter = this.value.toUpperCase();
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            
            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            rows[i].style.display = found ? '' : 'none';
        }
    });
}

// Validate RFID format
function validateRFID(input) {
    const rfidPattern = /^[a-zA-Z0-9]{4,}$/;
    const value = input.value;
    const feedback = document.getElementById('rfid-feedback');
    
    if (!rfidPattern.test(value) && value.length > 0) {
        input.classList.add('border-red-500');
        input.classList.remove('border-gray-300');
        if (feedback) {
            feedback.textContent = 'Format RFID tidak valid! Minimal 4 karakter alfanumerik.';
            feedback.classList.remove('hidden');
        }
        return false;
    } else {
        input.classList.remove('border-red-500');
        input.classList.add('border-gray-300');
        if (feedback) {
            feedback.classList.add('hidden');
        }
        return true;
    }
}

// Check ESP32 status (simulasi)
function checkESP32Status() {
    const statusElement = document.getElementById('esp32-status');
    if (!statusElement) return;
    
    // Simulasi pengecekan status
    // Dalam implementasi nyata, bisa menggunakan AJAX ke endpoint khusus
    const isOnline = Math.random() > 0.1; // 90% chance online
    
    if (isOnline) {
        statusElement.innerHTML = '<i class="fas fa-circle animate-pulse"></i> Online';
        statusElement.className = 'px-2 py-1 bg-green-600 text-white rounded text-xs';
    } else {
        statusElement.innerHTML = '<i class="fas fa-circle"></i> Offline';
        statusElement.className = 'px-2 py-1 bg-red-600 text-white rounded text-xs';
    }
}

// Notification toast
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center space-x-3 animate-fade-in`;
    toast.innerHTML = `
        <i class="fas ${icon} text-xl"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 500);
    }, 3000);
}

// Print function
function printTable() {
    window.print();
}

// Export to CSV (simple implementation)
function exportToCSV(tableId, filename = 'absensi.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistem Absensi Ngaji loaded');
    
    // Initialize features
    initializeCounters();
    highlightNewRow();
    checkESP32Status();
    
    // Setup RFID validation
    const rfidInput = document.querySelector('input[name="rfid_id"]');
    if (rfidInput && !rfidInput.hasAttribute('readonly')) {
        rfidInput.addEventListener('input', function() {
            validateRFID(this);
        });
    }
    
    // Update time every second
    setInterval(updateCurrentTime, 1000);
    
    // Check ESP32 status every 10 seconds
    setInterval(checkESP32Status, 10000);
    
    // Start auto-refresh if on dashboard
    // startAutoRefresh(); // Uncomment jika ingin auto-refresh aktif
    
    // Stop auto-refresh on user interaction
    document.addEventListener('click', () => {
        // stopAutoRefresh(); // Uncomment jika menggunakan auto-refresh
    });
    
    console.log('All features initialized');
});

// Handle page visibility change
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        // startAutoRefresh(); // Uncomment jika ingin auto-refresh aktif
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+R or F5: Refresh
    if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
        console.log('Manual refresh triggered');
    }
    
    // Ctrl+P: Print
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        printTable();
    }
});
