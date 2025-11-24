<?php
/**
 * Logout Page
 */

// Include files
include '../config/database.php';
include '../includes/functions.php';
include '../includes/auth.php';

// Logout user
logout($conn);
?>
