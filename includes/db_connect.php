<?php
// includes/db_connect.php

// ─── Local (XAMPP) ───────────────────────────────────────────
$host = 'localhost';
$dbname = 'ataa_db';
$username = 'root';
$password = '';
// ─────────────────────────────────────────────────────────────

// ─── InfinityFree Production ──────────────────────────────────
// $host = 'sql106.infinityfree.com';
// $dbname = 'if0_41762104_ataa_db';
// $username = 'if0_41762104';
// $password = 'XNqYmQ9hX7';
// ─────────────────────────────────────────────────────────────


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// تحديد المسار الأساسي (Base URL) ديناميكياً
if (!defined('BASE_URL')) {
    if (strpos($_SERVER['SCRIPT_NAME'], '/Blood_Donations/') !== false) {
        define('BASE_URL', '/Blood_Donations');
    } else {
        define('BASE_URL', '');
    }
}
?>