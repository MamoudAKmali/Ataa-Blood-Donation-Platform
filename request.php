<?php
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

$request_id = (int)($_GET['id'] ?? 0);
if (!$request_id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT br.*, f.name AS facility_name, f.phone AS facility_phone,
           f.latitude, f.longitude
    FROM blood_requests br
    JOIN facilities f ON br.facility_id = f.id
    WHERE br.id = ?
");
$stmt->execute([$request_id]);
$req = $stmt->fetch();

if (!$req) {
    header('Location: index.php');
    exit;
}

$remaining    = max(0, $req['units_needed'] - $req['units_secured']);
$percent      = $req['units_needed'] > 0 ? min(100, round($req['units_secured'] / $req['units_needed'] * 100)) : 0;
$is_critical  = $req['urgency_level'] === 'critical';
$is_open      = $req['status'] === 'open';
$map_url      = ($req['latitude'] && $req['longitude'])
                ? "https://www.google.com/maps/dir/?api=1&destination={$req['latitude']},{$req['longitude']}"
                : '';

$urgency_label = match($req['urgency_level']) { 'critical' => 'حرج جداً', 'high' => 'عاجل', default => 'عادي' };
$status_label  = match($req['status'])         { 'open' => 'متاح', 'fulfilled' => 'اكتمل', default => 'مغلق' };
?>

<main class="pub-detail-page">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="pub-breadcrumb">
            <a href="index.php">الرئيسية</a>
            <span class="material-symbols-outlined">chevron_left</span>
            <a href="hospitals.php">النداءات</a>
            <span class="material-symbols-outlined">chevron_left</span>
            <span>تفاصيل النداء</span>
        </nav>

        <div class="pub-detail-layout">

            <!-- Main Content -->
            <div class="pub-detail-main">

                <!-- Hero -->
                <div class="pub-hero-card <?php echo $is_critical ? 'pub-hero-card--critical' : ''; ?>">
                    <div class="pub-hero-card__banner"></div>
                    <div class="pub-hero-card__content">
                        <div class="pub-hero-blood">
                            <?php echo htmlspecialchars($req['blood_type']); ?>
                            <?php if ($is_open): ?>
                                <span class="pub-hero-blood__dot"></span>
                            <?php endif; ?>
                        </div>
                        <div class="pub-hero-info">
                            <h1 class="pub-hero-name"><?php echo htmlspecialchars($req['facility_name']); ?></h1>
                            <div class="pub-hero-badges">
                                <span class="pub-badge pub-badge--<?php echo $req['urgency_level']; ?>">
                                    <span class="material-symbols-outlined">
                                        <?php echo $is_critical ? 'emergency' : 'warning'; ?>
                                    </span>
                                    <?php echo $urgency_label; ?>
                                </span>
                                <span class="pub-badge pub-badge--status-<?php echo $req['status']; ?>">
                                    <span class="material-symbols-outlined">
                                        <?php echo $is_open ? 'check_circle' : 'lock'; ?>
                                    </span>
                                    <?php echo $status_label; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="pub-info-grid">

                    <!-- Request Details -->
                    <div class="pub-info-card">
                        <div class="pub-info-card__title">
                            <span class="material-symbols-outlined">info</span>
                            تفاصيل الطلب
                        </div>
                        <div class="pub-info-row">
                            <span>فصيلة الدم</span>
                            <strong><?php echo htmlspecialchars($req['blood_type']); ?></strong>
                        </div>
                        <?php if ($req['patient_name']): ?>
                        <div class="pub-info-row">
                            <span>اسم المريض</span>
                            <strong><?php echo htmlspecialchars($req['patient_name']); ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="pub-info-row">
                            <span>وحدات مطلوبة</span>
                            <strong><?php echo $req['units_needed']; ?> وحدة</strong>
                        </div>
                        <div class="pub-info-row">
                            <span>تم تأمين</span>
                            <strong><?php echo $req['units_secured']; ?> وحدة</strong>
                        </div>
                        <div class="pub-info-row">
                            <span>لا يزال ينقص</span>
                            <strong class="pub-text-danger"><?php echo $remaining; ?> وحدة</strong>
                        </div>
                        <div class="pub-info-row">
                            <span>تاريخ النداء</span>
                            <strong><?php echo date('Y/m/d', strtotime($req['created_at'])); ?></strong>
                        </div>

                        <!-- Progress -->
                        <div class="pub-progress">
                            <div class="pub-progress__labels">
                                <span>نسبة التأمين</span>
                                <span><?php echo $percent; ?>%</span>
                            </div>
                            <div class="pub-progress__bar">
                                <div class="pub-progress__fill <?php echo $percent >= 100 ? 'pub-progress__fill--done' : ''; ?>"
                                     data-pct="<?php echo $percent; ?>"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Facility Details -->
                    <div class="pub-info-card">
                        <div class="pub-info-card__title">
                            <span class="material-symbols-outlined">local_hospital</span>
                            بيانات المنشأة
                        </div>
                        <div class="pub-info-row">
                            <span>اسم المنشأة</span>
                            <strong><?php echo htmlspecialchars($req['facility_name']); ?></strong>
                        </div>
                        <?php if ($req['facility_phone']): ?>
                        <div class="pub-info-row">
                            <span>هاتف الطوارئ</span>
                            <strong dir="ltr"><?php echo htmlspecialchars($req['facility_phone']); ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="pub-info-row">
                            <span>الموقع الجغرافي</span>
                            <strong><?php echo ($req['latitude'] && $req['longitude']) ? 'محدد ✓' : 'غير محدد'; ?></strong>
                        </div>

                        <?php if ($map_url): ?>
                        <a href="<?php echo $map_url; ?>" target="_blank" class="pub-map-btn">
                            <span class="material-symbols-outlined">directions_car</span>
                            عرض مسار الوصول على الخريطة
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar CTA -->
            <aside class="pub-detail-sidebar">
                <div class="pub-cta-card">
                    <?php if (!$is_open): ?>
                        <div class="pub-cta-closed">
                            <span class="material-symbols-outlined">lock</span>
                            هذا النداء لم يعد متاحاً
                        </div>
                    <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'donor'): ?>
                        <a href="/Blood_Donations/dashboards/donor/request_detail.php?id=<?php echo $req['id']; ?>"
                           class="btn btn--primary pub-cta-btn">
                            <span class="material-symbols-outlined">water_drop</span>
                            <?php echo $is_critical ? 'تبرع الآن لإنقاذ حياة' : 'تبرع بالدم الآن'; ?>
                        </a>
                    <?php else: ?>
                        <p class="pub-cta-note">سجّل كمتبرع أو سجّل دخولك للاستجابة لهذا النداء</p>
                        <a href="/Blood_Donations/auth/register.php" class="btn btn--primary pub-cta-btn">
                            <span class="material-symbols-outlined">person_add</span>
                            سجّل كمتبرع
                        </a>
                        <a href="/Blood_Donations/auth/login.php" class="btn btn--outline pub-cta-btn">
                            <span class="material-symbols-outlined">login</span>
                            تسجيل الدخول
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Quick Stats -->
                <div class="pub-stats-card">
                    <div class="pub-stat-item">
                        <span class="pub-stat-num pub-text-danger"><?php echo $remaining; ?></span>
                        <span class="pub-stat-label">وحدة متبقية</span>
                    </div>
                    <div class="pub-stat-item">
                        <span class="pub-stat-num"><?php echo $req['units_secured']; ?></span>
                        <span class="pub-stat-label">وحدة مؤمَّنة</span>
                    </div>
                    <div class="pub-stat-item">
                        <span class="pub-stat-num"><?php echo $req['units_needed']; ?></span>
                        <span class="pub-stat-label">إجمالي المطلوب</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.pub-progress__fill[data-pct]').forEach(function (el) {
        el.style.width = el.dataset.pct + '%';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
