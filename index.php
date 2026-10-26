<?php 
require_once 'includes/db_connect.php';
require_once 'includes/header.php'; 

// Fetch stats
$donors_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role='donor'")->fetchColumn();
$saved_lives = $pdo->query("SELECT SUM(units_secured) FROM blood_requests")->fetchColumn();
if (!$saved_lives) $saved_lives = 0;
$hospitals_count = $pdo->query("SELECT COUNT(*) FROM facilities WHERE type='hospital'")->fetchColumn();

// Fetch up to 6 most urgent open requests
$urgent_requests = $pdo->query("
    SELECT br.*, f.name as facility_name
    FROM blood_requests br
    JOIN facilities f ON br.facility_id = f.id
    WHERE br.status = 'open'
    ORDER BY br.urgency_level DESC, br.created_at DESC
    LIMIT 6
")->fetchAll();
?>

<!-- Hero Section -->
<section class="section hero">
    <div class="container grid grid--2 align-center">
        <div class="hero__text-content">
            <span class="badge badge--soft mb-sm">منصة مجتمعية لإنقاذ الحياة</span>
            <h1 class="hero__title">قطرة من دمك.. <br><span class="text-primary">تنبض بها حياة</span></h1>
            <p class="hero__subtitle mb-lg">
                انضم إلى أكبر شبكة ذكية للتبرع بالدم. نحن نربط المتبرعين بالمستشفيات والحالات الطارئة لحظياً لضمان وصول الدم لمن يحتاجه في أسرع وقت.
            </p>
            <div class="hero__actions">
                <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn--primary">
                    <span class="material-symbols-outlined">favorite</span>
                    أريد أن أتبرع
                </a>
                <a href="<?php echo BASE_URL; ?>/hospitals.php" class="btn btn--outline">
                    <span class="material-symbols-outlined">campaign</span>
                    أحتاج دماً عاجلاً
                </a>
            </div>
            
            <div class="hero__stats mt-xl">
                <div class="stat-item">
                    <span class="stat-item__number text-primary"><?php echo number_format($donors_count); ?>+</span>
                    <span class="stat-item__label font-bold">متبرع مسجل</span>
                </div>
                <div class="stat-item">
                    <span class="stat-item__number text-primary"><?php echo number_format($saved_lives); ?>+</span>
                    <span class="stat-item__label font-bold">وحدة تم توفيرها</span>
                </div>
                <div class="stat-item">
                    <span class="stat-item__number text-primary"><?php echo number_format($hospitals_count); ?></span>
                    <span class="stat-item__label font-bold">مستشفى معتمد</span>
                </div>
            </div>
        </div>
        
        <div class="hero__visual">
            <!-- Glassmorphism Card showcasing urgent need -->
            <div class="glass-card">
                <div class="glass-card__header flex-between">
                    <div class="pulse-indicator">مباشر الآن</div>
                    <span class="material-symbols-outlined text-muted">notifications_active</span>
                </div>
                <div class="glass-card__body mb-lg text-center">
                    <div class="blood-badge blood-badge--urgent mb-md mx-auto">O-</div>
                    <h3 class="mb-xs">مستشفى الملك فهد التخصصي</h3>
                    <p class="text-secondary text-sm">نحتاج إلى 3 أكياس دم لعملية جراحية عاجلة.</p>
                </div>
                <div class="glass-card__footer">
                    <button class="btn btn--primary w-full">استجب للنداء الطارئ</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="about" class="section bg-surface">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">كيف تعمل منصة عطاء؟</h2>
            <p class="section__subtitle">صممنا النظام ليكون سهلاً وسريعاً وشفافاً ليضمن أفضل تجربة للمتبرع والمريض.</p>
        </div>
        <div class="grid grid--3">
            <div class="card text-center">
                <div class="card__icon mx-auto"><span class="material-symbols-outlined">person_add</span></div>
                <h3 class="card__title">سجل كمتبرع</h3>
                <p class="card__text">أنشئ حسابك بدقيقتين وأكمل التقييم الصحي السريع لتحديد أهليتك للتبرع بكل خصوصية.</p>
            </div>
            <div class="card text-center">
                <div class="card__icon mx-auto"><span class="material-symbols-outlined">cell_tower</span></div>
                <h3 class="card__title">تلقى النداءات الذكية</h3>
                <p class="card__text">نظامنا يطابق فصيلة دمك مع الحالات الطارئة القريبة منك، ليوجه لك إشعاراً عند الحاجة إليك.</p>
            </div>
            <div class="card text-center">
                <div class="card__icon mx-auto"><span class="material-symbols-outlined">military_tech</span></div>
                <h3 class="card__title">احصد الأوسمة وتتبع أثرك</h3>
                <p class="card__text">مع كل تبرع، ترتقي في مستويات المتبرعين. وسنعلمك فور استخدام دمك لإنقاذ مريض!</p>
            </div>
        </div>
    </div>
</section>

<!-- Urgent Requests Preview -->
<section id="urgent" class="section">
    <div class="container">
        <div class="section__header flex-between align-center mb-lg">
            <div>
                <h2 class="section__title text-right mb-0">نداءات عاجلة حالياً</h2>
                <p class="section__subtitle text-right">هذه المستشفيات تواجه نقصاً حرجاً في بعض الفصائل.</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/map.php" class="btn btn--outline">
                عرض الخريطة <span class="material-symbols-outlined">map</span>
            </a>
        </div>

        <div class="req-cards-grid">
            <?php if (count($urgent_requests) > 0): ?>
                <?php foreach ($urgent_requests as $req): ?>
                    <?php
                        $percent     = ($req['units_needed'] > 0) ? min(100, round($req['units_secured'] / $req['units_needed'] * 100)) : 0;
                        $remaining   = max(0, $req['units_needed'] - $req['units_secured']);
                        $is_critical = $req['urgency_level'] === 'critical';
                    ?>
                    <div class="home-req-card <?php echo $is_critical ? 'home-req-card--critical' : ''; ?>">
                        <div class="home-req-card__side"></div>
                        <div class="home-req-card__body">
                            <div class="home-req-card__top-row">
                                <div class="home-req-card__blood"><?php echo htmlspecialchars($req['blood_type']); ?></div>
                                <span class="home-req-card__urgency <?php echo $is_critical ? 'home-req-card__urgency--critical' : 'home-req-card__urgency--high'; ?>">
                                    <?php echo $is_critical ? 'حرج جداً' : 'عاجل'; ?>
                                </span>
                            </div>
                            <h4 class="home-req-card__name"><?php echo htmlspecialchars($req['facility_name']); ?></h4>
                            <p class="home-req-card__sub">
                                <?php echo $req['patient_name'] ? 'للمريض: ' . htmlspecialchars($req['patient_name']) : 'طلب داخلي / حملة تبرع'; ?>
                            </p>
                            <div class="home-req-card__progress">
                                <div class="home-req-card__progress-bar">
                                    <div class="home-req-card__progress-fill <?php echo $percent >= 100 ? 'home-req-card__progress-fill--done' : ''; ?>"
                                         data-pct="<?php echo $percent; ?>"></div>
                                </div>
                                <span class="home-req-card__progress-label">
                                    متبقي <strong><?php echo $remaining; ?></strong> من <?php echo $req['units_needed']; ?> وحدة
                                </span>
                            </div>
                        </div>
                        <div class="home-req-card__action">
                            <a href="<?php echo BASE_URL; ?>/request.php?id=<?php echo $req['id']; ?>" class="btn btn--outline home-req-card__detail-link">
                                <span class="material-symbols-outlined">info</span>
                                التفاصيل
                            </a>
                            <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn <?php echo $is_critical ? 'btn--primary' : 'btn--outline'; ?>">
                                استجب
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted py-lg text-center col-span-2">
                    <p>الحمدلله، لا توجد نداءات طارئة في الوقت الحالي.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.home-req-card__progress-fill[data-pct]').forEach(function (el) {
        el.style.width = el.dataset.pct + '%';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

