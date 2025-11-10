<?php
/**
 * Entry Point - Redirect ke halaman login atau dashboard
 */

// Cek apakah sudah ada session
session_start();

// Redirect berdasarkan status login
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Sudah login, redirect berdasarkan tipe user
    if (isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] === 'admin') {
            header('Location: public/index.php');
        } else {
            header('Location: public/santri_log.php');
        }
    } else {
        header('Location: public/login.php');
    }
} else {
    // Belum login, redirect ke login
    header('Location: public/login.php');
}
exit();
