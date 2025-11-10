    </div> <!-- End Container -->
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <h3 class="text-lg font-semibold flex items-center space-x-2">
                        <i class="fas fa-mosque"></i>
                        <span>Sistem Absensi Ngaji</span>
                    </h3>
                    <p class="text-gray-400 text-sm mt-1">Berbasis IoT ESP32 & RFID</p>
                </div>
                
                <div class="text-center md:text-right">
                    <p class="text-gray-400 text-sm">
                        © <?php echo date('Y'); ?> Absensi Ngaji. All rights reserved.
                    </p>
                    <p class="text-gray-500 text-xs mt-1">
                        Powered by PHP & Tailwind CSS
                    </p>
                </div>
            </div>
            
            <!-- Info ESP32 -->
            <div class="mt-4 pt-4 border-t border-gray-700">
                <div class="flex items-center justify-center space-x-2 text-sm text-gray-400">
                    <i class="fas fa-microchip text-blue-400"></i>
                    <span>Status ESP32:</span>
                    <span id="esp32-status" class="px-2 py-1 bg-green-600 text-white rounded text-xs">
                        <i class="fas fa-circle animate-pulse"></i> Online
                    </span>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="js/script.js"></script>
    
    <!-- Auto-hide alerts -->
    <script>
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-auto-hide');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
