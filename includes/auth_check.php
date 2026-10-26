<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!defined('BASE_URL')) {
    if (strpos($_SERVER['SCRIPT_NAME'], '/Blood_Donations/') !== false) {
        define('BASE_URL', '/Blood_Donations');
    } else {
        define('BASE_URL', '');
    }
}

if (!isset($_SESSION['user_id']) && !isset($_SESSION['facility_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
?>
