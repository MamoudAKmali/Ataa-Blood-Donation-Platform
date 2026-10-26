<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="db-sidebar" id="adminSidebar">
    <div class="db-sidebar__profile">
        <div class="db-sidebar__profile-row">
            <div class="db-sidebar__profile-avatar">
                <span class="material-symbols-outlined">shield_person</span>
            </div>
            <div class="db-sidebar__profile-info">
                <div class="db-sidebar__profile-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'مدير النظام'); ?></div>
                <div class="db-sidebar__profile-role">مدير النظام</div>
            </div>
        </div>
    </div>

    <div class="db-sidebar__section-title">القائمة الرئيسية</div>
    <nav class="db-sidebar__nav">
        <a href="index.php" class="db-sidebar__link <?php echo $current_page == 'index.php' ? 'is-active' : ''; ?>">
            <span class="material-symbols-outlined">dashboard</span>
            نظرة عامة
        </a>
        <a href="users.php" class="db-sidebar__link <?php echo $current_page == 'users.php' ? 'is-active' : ''; ?>">
            <span class="material-symbols-outlined">group</span>
            إدارة المستخدمين
        </a>
        <a href="facilities.php" class="db-sidebar__link <?php echo $current_page == 'facilities.php' ? 'is-active' : ''; ?>">
            <span class="material-symbols-outlined">local_hospital</span>
            إدارة المستشفيات
        </a>
        <a href="requests.php" class="db-sidebar__link <?php echo $current_page == 'requests.php' ? 'is-active' : ''; ?>">
            <span class="material-symbols-outlined">campaign</span>
            النداءات العاجلة
        </a>
        <a href="screening_questions.php" class="db-sidebar__link <?php echo $current_page == 'screening_questions.php' ? 'is-active' : ''; ?>">
            <span class="material-symbols-outlined">health_and_safety</span>
            أسئلة الاستبانة الطبية
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
            تسجيل الخروج
        </a>
    </div>
</aside>

<!-- Overlay للسايدبار على الموبايل -->
<div class="db-sidebar-overlay" id="dbSidebarOverlay"></div>
