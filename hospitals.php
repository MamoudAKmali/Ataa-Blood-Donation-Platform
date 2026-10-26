<?php
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

$stmt = $pdo->query("
    SELECT f.id, f.name, f.address, f.phone, f.is_verified,
           COALESCE(SUM(i.units_available), 0) as total_units,
           COUNT(DISTINCT br.id) as active_requests,
           COUNT(DISTINCT i.blood_type) as blood_types_count
    FROM facilities f
    LEFT JOIN inventory i ON i.facility_id = f.id
    LEFT JOIN blood_requests br ON br.facility_id = f.id AND br.status = 'open'
    WHERE f.type = 'hospital'
    GROUP BY f.id
    ORDER BY f.name ASC
");
$hospitals = $stmt->fetchAll();
$total = count($hospitals);
?>

<!-- Hero Strip -->
<div class="hosp-hero">
    <div class="container">
        <div class="hosp-hero__inner">
            <div>
                <div class="hosp-hero__label">شبكة عطاء الطبية</div>
                <h1 class="hosp-hero__title">المستشفيات المعتمدة</h1>
                <p class="hosp-hero__sub">جميع المنشآت الطبية المشاركة في منصة عطاء للتبرع بالدم في المملكة العربية السعودية.</p>
            </div>
            <div class="hosp-hero__stat">
                <div class="hosp-hero__stat-number"><?php echo $total; ?></div>
                <div class="hosp-hero__stat-label">مستشفى مسجل</div>
            </div>
        </div>
    </div>
</div>

<!-- Cards Grid -->
<div class="container hosp-container">
    <?php if ($total > 0): ?>
        <div class="hosp-grid">
            <?php foreach ($hospitals as $h): ?>
                <div class="hosp-card">
                    <!-- Card Header -->
                    <div class="hosp-card__head">
                        <div class="hosp-card__icon">
                            <span class="material-symbols-outlined">local_hospital</span>
                        </div>
                        <div class="hosp-card__head-info">
                            <h3 class="hosp-card__name"><?php echo htmlspecialchars($h['name']); ?></h3>
                            <p class="hosp-card__address">
                                <span class="material-symbols-outlined" style="font-size:13px">location_on</span>
                                <?php echo htmlspecialchars($h['address'] ?: 'العنوان غير محدد'); ?>
                            </p>
                        </div>
                        <?php if ($h['is_verified']): ?>
                            <div class="hosp-card__verified" title="مستشفى موثق">
                                <span class="material-symbols-outlined">verified</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Stats Row -->
                    <div class="hosp-card__stats">
                        <div class="hosp-stat">
                            <div class="hosp-stat__num <?php echo $h['total_units'] > 0 ? 'hosp-stat__num--good' : 'hosp-stat__num--low'; ?>">
                                <?php echo number_format($h['total_units']); ?>
                            </div>
                            <div class="hosp-stat__label">وحدة متوفرة</div>
                        </div>
                        <div class="hosp-stat__divider"></div>
                        <div class="hosp-stat">
                            <div class="hosp-stat__num <?php echo $h['active_requests'] > 0 ? 'hosp-stat__num--urgent' : 'hosp-stat__num--good'; ?>">
                                <?php echo $h['active_requests']; ?>
                            </div>
                            <div class="hosp-stat__label">طلب نشط</div>
                        </div>
                        <div class="hosp-stat__divider"></div>
                        <div class="hosp-stat">
                            <div class="hosp-stat__num"><?php echo $h['blood_types_count']; ?></div>
                            <div class="hosp-stat__label">فصيلة متاحة</div>
                        </div>
                    </div>

                    <!-- Status Bar -->
                    <?php if ($h['active_requests'] > 0): ?>
                        <div class="hosp-card__alert">
                            <span class="material-symbols-outlined">emergency</span>
                            يوجد <?php echo $h['active_requests']; ?> طلب تبرع عاجل في هذا المستشفى
                        </div>
                    <?php endif; ?>

                    <!-- Action -->
                    <a href="/Blood_Donations/auth/register.php" class="hosp-card__btn <?php echo $h['active_requests'] > 0 ? 'hosp-card__btn--urgent' : ''; ?>">
                        <span class="material-symbols-outlined">water_drop</span>
                        تبرع في هذا المستشفى
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="hosp-empty">
            <span class="material-symbols-outlined">local_hospital</span>
            <h3>لا توجد مستشفيات مسجلة حالياً</h3>
            <p>سيتم إضافة المستشفيات المعتمدة قريباً.</p>
        </div>
    <?php endif; ?>
</div>

<style>
/* ── Hero Strip ── */
.hosp-hero {
    background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 60%, #b91c1c 100%);
    padding: 60px 0;
    margin-bottom: 56px;
}

.hosp-hero__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
}

.hosp-hero__label {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 20px;
    margin-bottom: 14px;
    letter-spacing: 0.5px;
}

.hosp-hero__title {
    font-size: 2.4rem;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 10px;
    line-height: 1.2;
}

.hosp-hero__sub {
    color: rgba(255,255,255,0.75);
    font-size: 15px;
    max-width: 520px;
}

.hosp-hero__stat {
    text-align: center;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 16px;
    padding: 28px 40px;
    flex-shrink: 0;
}

.hosp-hero__stat-number {
    font-size: 56px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1;
}

.hosp-hero__stat-label {
    font-size: 14px;
    color: rgba(255,255,255,0.7);
    margin-top: 6px;
    font-weight: 600;
}

/* ── Grid ── */
.hosp-container { padding-bottom: 80px; }

.hosp-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

/* ── Card ── */
.hosp-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: box-shadow 0.25s, transform 0.25s;
}

.hosp-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.09);
    transform: translateY(-3px);
}

/* Card Head */
.hosp-card__head {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.hosp-card__icon {
    width: 52px;
    height: 52px;
    background: #fef2f2;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #991b1b;
    font-size: 0;
}

.hosp-card__icon .material-symbols-outlined {
    font-size: 26px;
    font-variation-settings: 'FILL' 1;
}

.hosp-card__head-info { flex: 1; min-width: 0; }

.hosp-card__name {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 5px;
    line-height: 1.3;
}

.hosp-card__address {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 3px;
}

.hosp-card__verified {
    color: #2563eb;
    flex-shrink: 0;
}

.hosp-card__verified .material-symbols-outlined {
    font-size: 20px;
    font-variation-settings: 'FILL' 1;
}

/* Stats Row */
.hosp-card__stats {
    display: flex;
    align-items: center;
    background: #f8fafc;
    border-radius: 10px;
    padding: 16px;
    gap: 0;
}

.hosp-stat {
    flex: 1;
    text-align: center;
}

.hosp-stat__num {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
    margin-bottom: 4px;
}

.hosp-stat__num--good   { color: #15803d; }
.hosp-stat__num--low    { color: #94a3b8; }
.hosp-stat__num--urgent { color: #b91c1c; }

.hosp-stat__label {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 600;
}

.hosp-stat__divider {
    width: 1px;
    height: 36px;
    background: #e2e8f0;
    flex-shrink: 0;
}

/* Alert Bar */
.hosp-card__alert {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #b91c1c;
}

.hosp-card__alert .material-symbols-outlined {
    font-size: 16px;
    font-variation-settings: 'FILL' 1;
}

/* CTA Button */
.hosp-card__btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #0f172a;
    color: #ffffff;
    border-radius: 8px;
    padding: 13px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s;
    margin-top: auto;
}

.hosp-card__btn:hover { background: #1e293b; color: #ffffff; }

.hosp-card__btn--urgent {
    background: #991b1b;
}

.hosp-card__btn--urgent:hover { background: #7f1d1d; }

.hosp-card__btn .material-symbols-outlined {
    font-size: 18px;
    font-variation-settings: 'FILL' 1;
}

/* Empty State */
.hosp-empty {
    text-align: center;
    padding: 80px 20px;
    color: #94a3b8;
}

.hosp-empty .material-symbols-outlined {
    font-size: 64px;
    display: block;
    margin-bottom: 16px;
    font-variation-settings: 'FILL' 0;
}

.hosp-empty h3 {
    font-size: 20px;
    color: #334155;
    margin-bottom: 8px;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
    .hosp-grid { grid-template-columns: repeat(2, 1fr); }
    .hosp-hero__title { font-size: 2rem; }
}

@media (max-width: 768px) {
    .hosp-hero { padding: 40px 0; margin-bottom: 36px; }
    .hosp-hero__inner { flex-direction: column; align-items: flex-start; }
    .hosp-hero__title { font-size: 1.7rem; }
    .hosp-hero__stat { width: 100%; padding: 20px; }
    .hosp-hero__stat-number { font-size: 40px; }
    .hosp-grid { grid-template-columns: 1fr; gap: 16px; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
