<?php
/**
 * admin_head.php
 * رأس الصفحة الخاص بلوحات التحكم - بدون navigation عامة
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!defined('BASE_URL')) {
    if (strpos($_SERVER['SCRIPT_NAME'], '/Blood_Donations/') !== false) {
        define('BASE_URL', '/Blood_Donations');
    } else {
        define('BASE_URL', '');
    }
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'لوحة التحكم - عطاء'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=25">
</head>
<body class="admin-page">
