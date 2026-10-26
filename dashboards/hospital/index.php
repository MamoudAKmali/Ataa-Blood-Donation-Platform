<?php
require_once '../../includes/hospital_auth.php';
require_once '../../includes/db_connect.php';

$page_title = "مركز العمليات - عطاء";
require_once '../../includes/dashboard_header.php';

$facility_id = $_SESSION['facility_id'];

// 1. Fetch Inventory
$stmt = $pdo->prepare("SELECT blood_type, units_available FROM inventory WHERE facility_id = ?");
$stmt->execute([$facility_id]);
$inventory_db = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$blood_types = ['A+', 'A-', 'B+', 'B-', 'O-', 'O+', 'AB+', 'AB-'];
$inventory = [];
foreach ($blood_types as $bt) {
    $inventory[$bt] = $inventory_db[$bt] ?? 0;
}

// 2. Fetch Open Emergency Requests Directed to this hospital
$stmt = $pdo->prepare("
    SELECT * 
    FROM blood_requests 
    WHERE facility_id = ? AND status = 'open' 
    ORDER BY urgency_level DESC, created_at DESC
");
$stmt->execute([$facility_id]);
$requests = $stmt->fetchAll();
?>

<div class="db-page-header-row">
    <div class="db-page-header">
        <h1 class="db-page-title">مركز العمليات المباشر</h1>
        <p class="db-page-subtitle">مراقبة المخزون واستقبال الحالات الطارئة لحظياً</p>
    </div>
    <div class="status-tag status-tag--done">
        <span class="material-symbols-outlined" style="font-size: 16px;">sync</span> مزامنة نشطة
    </div>
</div>

<div class="bento bento--2col mb-lg">
    <!-- Current Inventory Summary -->
    <div class="stat-card" style="grid-column: span 2;">
        <div class="stat-card__label flex-between mb-md">
            <span><span class="material-symbols-outlined text-primary">bloodtype</span> ملخص المخزون الحالي</span>
            <a href="inventory.php" class="text-primary text-sm font-bold">تحديث المخزون &larr;</a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(60px, 1fr)); gap: 12px; text-align: center;">
            <?php foreach($blood_types as $bt): 
                $qty = $inventory[$bt];
                $status = 'مستقر';
                $color = '#2563eb'; // blue
                if ($qty < 5) { $status = 'حرج'; $color = '#dc2626'; } // red
                elseif ($qty < 10) { $status = 'منخفض'; $color = '#d97706'; } // orange
                elseif ($qty >= 20) { $status = 'ممتاز'; $color = '#16a34a'; } // green
            ?>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 4px;">
                <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: <?php echo $color; ?>;" dir="ltr"><?php echo $bt; ?></div>
                <div style="font-size: 11px; font-weight: 700; color: <?php echo $color; ?>; margin-top: 4px;"><?php echo $status; ?></div>
                <div style="font-size: 14px; font-weight: 800; margin-top: 4px;"><?php echo $qty; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="section-header mb-md">
    <h3 class="section-header__title">نداءات الطوارئ النشطة</h3>
</div>

<?php if (count($requests) > 0): ?>
    <div class="bento mb-lg">
        <?php foreach($requests as $req): ?>
        <div class="stat-card" style="border-right: 4px solid <?php echo ($req['urgency_level'] == 'critical') ? '#dc2626' : '#d97706'; ?>;">
            <div class="flex-between align-start mb-sm">
                <div>
                    <?php if ($req['urgency_level'] == 'critical'): ?>
                        <span class="status-tag status-tag--cancel mb-xs">حالة حرجة (CODE RED)</span>
                    <?php else: ?>
                        <span class="status-tag status-tag--pending mb-xs">حالة عاجلة</span>
                    <?php endif; ?>
                    <h3 style="font-size: 18px; margin: 4px 0;"><?php echo $req['patient_name'] ? htmlspecialchars($req['patient_name']) : 'طلب داخلي بالمستشفى'; ?></h3>
                    <p class="text-sm text-secondary">طُلب في: <?php echo date('H:i - Y/m/d', strtotime($req['created_at'])); ?></p>
                </div>
                <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; padding: 12px; border-radius: 8px; text-align: center; min-width: 80px;">
                    <div style="font-size: 11px; font-weight: 700;">الفصيلة المطلوبة</div>
                    <div style="font-size: 24px; font-weight: 800; font-family: monospace;" dir="ltr"><?php echo htmlspecialchars($req['blood_type']); ?></div>
                </div>
            </div>
            
            <div class="mt-md pt-md" style="border-top: 1px solid #e5e7eb;">
                <div class="flex-between text-sm font-bold mb-xs">
                    <span>الوحدات المؤمنة</span>
                    <span><?php echo $req['units_secured']; ?> من أصل <?php echo $req['units_needed']; ?></span>
                </div>
                <?php $percent = ($req['units_needed'] > 0) ? ($req['units_secured'] / $req['units_needed'] * 100) : 0; ?>
                <div style="height: 6px; background: #f3f4f6; border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: <?php echo $percent; ?>%; background: var(--clr-primary);"></div>
                </div>
            </div>
            
            <div class="mt-md">
                <a href="request_details.php?id=<?php echo $req['id']; ?>" class="btn-primary w-full" style="justify-content: center;">
                    <span class="material-symbols-outlined">visibility</span>
                    إدارة النداء وعرض المتبرعين
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="db-table-card text-center py-lg text-muted">
        <span class="material-symbols-outlined" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;">health_and_safety</span>
        <p>لا توجد أي نداءات طوارئ نشطة حالياً. الوضع مستقر والحمدلله.</p>
    </div>
<?php endif; ?>

<?php require_once '../../includes/dashboard_footer.php'; ?>
