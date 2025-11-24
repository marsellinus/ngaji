<?php
/**
 * Halaman Login
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Jika sudah login, redirect berdasarkan tipe user
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('index.php');
    } else {
        redirect('santri_log.php'); // Santri ke halaman log mereka
    }
}

// Handle login submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        if (login($conn, $username, $password)) {
            // Login berhasil, redirect berdasarkan tipe user
            if (isAdmin()) {
                $redirect = $_GET['redirect'] ?? 'index.php';
            } else {
                $redirect = 'santri_log.php'; // Santri ke halaman log mereka
            }
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    }
}

// Handle GET error message
if (isset($_GET['error'])) {
    $error = sanitizeInput($_GET['error']);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Ngaji</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <div class="max-w-md w-full">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-block bg-white p-4 rounded-full shadow-lg mb-4">
                <i class="fas fa-mosque text-blue-600 text-5xl"></i>
            </div>
            <h1 class="text-white text-3xl font-bold">Sistem Absensi Ngaji</h1>
            <p class="text-white opacity-90 mt-2">Silakan login untuk melanjutkan</p>
        </div>
        
        <!-- Login Form -->
        <div class="bg-white rounded-lg shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                <i class="fas fa-sign-in-alt text-blue-600"></i> Login
            </h2>
            
            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Form -->
            <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
                <!-- Username -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required
                           autofocus
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Masukkan username">
                </div>
                
                <!-- Password -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Masukkan password">
                        <button type="button" 
                                onclick="togglePassword()"
                                class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="mr-2">
                        <span class="text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 flex items-center justify-center space-x-2">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
            </form>
            
            <!-- Info Default Account -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-xs text-blue-800 font-semibold mb-2">
                    <i class="fas fa-info-circle"></i> Akun Default:
                </p>
                <div class="text-xs text-blue-700 space-y-1">
                    <p><strong>Admin:</strong> admin / admin123</p>
                    <p><strong>Operator:</strong> operator / admin123</p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-white">
            <p class="text-sm opacity-90">
                © <?php echo date('Y'); ?> Sistem Absensi Ngaji IoT
            </p>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto focus username on load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>
