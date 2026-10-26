<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); } 
if (!defined('BASE_URL')) {
    if (strpos($_SERVER['SCRIPT_NAME'], '/Blood_Donations/') !== false) {
        define('BASE_URL', '/Blood_Donations');
    } else {
        define('BASE_URL', '');
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عطاء - نظام التبرع بالدم</title>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <!-- Main CSS Hub -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=27">
</head>

<body>
    <header class="header">
        <div class="container navbar">
            <a href="<?php echo BASE_URL; ?>/index.php" class="logo">
                <span class="material-symbols-outlined logo__icon">water_drop</span>
                <span>عطاء</span>
            </a>

            <nav class="nav-menu" id="nav-menu">
                <a href="<?php echo BASE_URL; ?>/index.php" class="nav-link">الرئيسية</a>
                <a href="<?php echo BASE_URL; ?>/about.php" class="nav-link">من نحن</a>
                <a href="<?php echo BASE_URL; ?>/hospitals.php" class="nav-link">المستشفيات</a>
                <a href="<?php echo BASE_URL; ?>/map.php" class="nav-link">خريطة المراكز</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'donor'): ?>
                    <a href="<?php echo BASE_URL; ?>/dashboards/donor/urgent.php" class="nav-link">تبرع الآن</a>
                <?php elseif (!isset($_SESSION['user_id']) && !isset($_SESSION['facility_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/auth/register.php" class="nav-link">تبرع الآن</a>
                <?php endif; ?>
            </nav>

            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/dashboards/<?php echo $_SESSION['role']; ?>/index.php" class="btn btn--outline">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="btn-text">لوحة التحكم</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn--primary">خروج</a>
                <?php elseif (isset($_SESSION['facility_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/dashboards/hospital/index.php" class="btn btn--outline">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="btn-text">لوحة التحكم</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn--primary">خروج</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn btn--outline">تسجيل الدخول</a>
                    <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn--primary">تبرع الآن</a>
                <?php endif; ?>

                <!-- زر الهمبرجر -->
                <button class="hamburger" id="hamburger-btn" aria-label="فتح القائمة">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobile-menu">
            <nav class="mobile-menu__nav">
                <a href="<?php echo BASE_URL; ?>/index.php" class="mobile-menu__link">
                    <span class="material-symbols-outlined">home</span> الرئيسية
                </a>
                <a href="<?php echo BASE_URL; ?>/about.php" class="mobile-menu__link">
                    <span class="material-symbols-outlined">info</span> من نحن
                </a>
                <a href="<?php echo BASE_URL; ?>/hospitals.php" class="mobile-menu__link">
                    <span class="material-symbols-outlined">local_hospital</span> المستشفيات المعتمدة
                </a>
                <a href="<?php echo BASE_URL; ?>/map.php" class="mobile-menu__link">
                    <span class="material-symbols-outlined">map</span> خريطة المراكز
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/dashboards/donor/index.php" class="mobile-menu__link">
                        <span class="material-symbols-outlined">dashboard</span> لوحة التحكم
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="mobile-menu__link mobile-menu__link--danger">
                        <span class="material-symbols-outlined">logout</span> تسجيل خروج
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/auth/login.php" class="mobile-menu__link">
                        <span class="material-symbols-outlined">login</span> تسجيل الدخول
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/register.php" class="mobile-menu__link mobile-menu__link--primary">
                        <span class="material-symbols-outlined">favorite</span> تبرع الآن
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="main-content">