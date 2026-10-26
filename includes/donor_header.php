<?php
require_once 'auth_check.php';
require_once 'db_connect.php';

$stmt = $pdo->prepare("SELECT name, blood_type FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$donor_header_info = $stmt->fetch();
$first_name   = explode(' ', $donor_header_info['name'])[0];
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عطاء - لوحة المتبرع</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=24">
</head>
<body class="admin-page">

<!-- Topbar -->
<div class="db-topbar">
    <div class="db-topbar__inner">
        <div class="db-topbar__start">
            <button class="db-topbar__hamburger" id="dbSidebarToggle" aria-label="فتح القائمة الجانبية">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <a href="<?php echo BASE_URL; ?>/index.php" class="db-topbar__brand">
                <span class="material-symbols-outlined">water_drop</span>
                عطاء
            </a>
        </div>
        <div class="db-topbar__actions">
            <a href="<?php echo BASE_URL; ?>/dashboards/donor/urgent.php" class="db-topbar__donate">
                <span class="material-symbols-outlined">water_drop</span>
                تبرع الآن
            </a>
            <div class="db-topbar__avatar">
                <?php echo mb_substr($first_name, 0, 1, "UTF-8"); ?>
            </div>
        </div>
    </div>
</div>

<!-- Layout -->
<div class="db-layout">

    <!-- Sidebar -->
    <aside class="db-sidebar" id="adminSidebar">
        <div class="db-sidebar__profile">
            <div class="db-sidebar__profile-row">
                <div class="db-sidebar__profile-avatar db-sidebar__profile-avatar--blood">
                    <?php echo htmlspecialchars($donor_header_info['blood_type'] ?: '?'); ?>
                </div>
                <div class="db-sidebar__profile-info">
                    <div class="db-sidebar__profile-name"><?php echo htmlspecialchars($donor_header_info['name']); ?></div>
                    <div class="db-sidebar__profile-role">متبرع مسجل</div>
                </div>
            </div>
        </div>

        <p class="db-sidebar__section-title">القائمة الرئيسية</p>

        <nav class="db-sidebar__nav">
            <a href="<?php echo BASE_URL; ?>/dashboards/donor/index.php"
               class="db-sidebar__link <?php echo ($current_page == 'index.php') ? 'is-active' : ''; ?>">
                <span class="material-symbols-outlined">dashboard</span>
                لوحة التحكم
            </a>
            <a href="<?php echo BASE_URL; ?>/dashboards/donor/history.php"
               class="db-sidebar__link <?php echo ($current_page == 'history.php') ? 'is-active' : ''; ?>">
                <span class="material-symbols-outlined">history</span>
                سجل التبرعات
            </a>
            <a href="<?php echo BASE_URL; ?>/dashboards/donor/urgent.php"
               class="db-sidebar__link <?php echo ($current_page == 'urgent.php') ? 'is-active' : ''; ?>">
                <span class="material-symbols-outlined">emergency</span>
                النداءات العاجلة
            </a>
            <a href="<?php echo BASE_URL; ?>/dashboards/donor/profile.php"
               class="db-sidebar__link <?php echo ($current_page == 'profile.php') ? 'is-active' : ''; ?>">
                <span class="material-symbols-outlined">manage_accounts</span>
                ملفي الشخصي
            </a>
        </nav>

        <div class="db-sidebar__footer">
            <a href="<?php echo BASE_URL; ?>/index.php" class="db-sidebar__link db-sidebar__link--home">
                <span class="material-symbols-outlined">public</span>
                الموقع الرئيسي
            </a>
            <a href="<?php echo BASE_URL; ?>/auth/logout.php"
               class="db-sidebar__link db-sidebar__link--danger"
               data-logout>
                <span class="material-symbols-outlined">logout</span>
                تسجيل خروج
            </a>
        </div>
    </aside>

    <!-- Overlay -->
    <div class="db-sidebar-overlay" id="dbSidebarOverlay"></div>

    <!-- Main Content -->
    <main class="db-main">
