<?php
require_once 'auth_check.php';
require_once 'db_connect.php';

$current_page = basename($_SERVER['PHP_SELF']);
$user_name    = $_SESSION['name'] ?? 'مستخدم';
$first_letter = mb_substr($user_name, 0, 1, 'UTF-8');
$is_facility  = isset($_SESSION['facility_id']);
$role         = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عطاء - لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=25">
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
            <?php if ($is_facility): ?>
                <a href="<?php echo BASE_URL; ?>/dashboards/hospital/create_request.php" class="db-topbar__donate">
                    <span class="material-symbols-outlined">campaign</span>
                    نداء عاجل
                </a>
            <?php elseif ($role === 'patient'): ?>
                <a href="<?php echo BASE_URL; ?>/dashboards/patient/request.php" class="db-topbar__donate">
                    <span class="material-symbols-outlined">campaign</span>
                    طلب دم
                </a>
            <?php endif; ?>
            <div class="db-topbar__avatar"><?php echo $first_letter; ?></div>
        </div>
    </div>
</div>

<!-- Layout -->
<div class="db-layout">

    <!-- Sidebar -->
    <aside class="db-sidebar" id="adminSidebar">
        <div class="db-sidebar__profile">
            <div class="db-sidebar__profile-row">
                <div class="db-sidebar__profile-avatar">
                    <span class="material-symbols-outlined">
                        <?php echo $is_facility ? 'local_hospital' : ($role === 'patient' ? 'personal_injury' : 'person'); ?>
                    </span>
                </div>
                <div class="db-sidebar__profile-info">
                    <div class="db-sidebar__profile-name"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="db-sidebar__profile-role">
                        <?php echo $is_facility ? 'منشأة طبية معتمدة' : ($role === 'patient' ? 'مريض مسجل' : 'عضو'); ?>
                    </div>
                </div>
            </div>
        </div>

        <p class="db-sidebar__section-title">القائمة الرئيسية</p>

        <nav class="db-sidebar__nav">
            <?php if ($is_facility): ?>
                <a href="<?php echo BASE_URL; ?>/dashboards/hospital/index.php"
                   class="db-sidebar__link <?php echo ($current_page == 'index.php') ? 'is-active' : ''; ?>">
                    <span class="material-symbols-outlined">dashboard</span>
                    مركز العمليات
                </a>
                <a href="<?php echo BASE_URL; ?>/dashboards/hospital/inventory.php"
                   class="db-sidebar__link <?php echo ($current_page == 'inventory.php') ? 'is-active' : ''; ?>">
                    <span class="material-symbols-outlined">inventory</span>
                    إدارة المخزون
                </a>
                <a href="<?php echo BASE_URL; ?>/dashboards/hospital/create_request.php"
                   class="db-sidebar__link <?php echo ($current_page == 'create_request.php') ? 'is-active' : ''; ?>">
                    <span class="material-symbols-outlined">campaign</span>
                    إطلاق نداء عاجل
                </a>
                <a href="<?php echo BASE_URL; ?>/dashboards/hospital/settings.php"
                   class="db-sidebar__link <?php echo ($current_page == 'settings.php') ? 'is-active' : ''; ?>">
                    <span class="material-symbols-outlined">settings</span>
                    إعدادات المنشأة
                </a>
            <?php elseif ($role === 'donor'): ?>
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
            <?php endif; ?>
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
                تسجيل الخروج
            </a>
        </div>
    </aside>

    <!-- Overlay -->
    <div class="db-sidebar-overlay" id="dbSidebarOverlay"></div>

    <!-- Main Content -->
    <main class="db-main">
