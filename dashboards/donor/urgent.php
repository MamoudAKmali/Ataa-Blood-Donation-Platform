<?php
require_once '../../includes/donor_header.php';

$stmt = $pdo->prepare("SELECT blood_type FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_info = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT br.*, f.name as facility_name 
    FROM blood_requests br 
    JOIN facilities f ON br.facility_id = f.id 
    WHERE br.status = 'open' 
      AND (br.blood_type = ? OR br.blood_type = 'Unknown' OR ? = 'Unknown')
      AND NOT EXISTS (
          SELECT 1 FROM donations d
          WHERE d.request_id = br.id
            AND d.donor_id = ?
            AND d.status IN ('pending', 'completed', 'approved')
      )
    ORDER BY br.urgency_level DESC, br.created_at DESC
");
$stmt->execute([$user_info['blood_type'], $user_info['blood_type'], $_SESSION['user_id']]);
$urgent_requests = $stmt->fetchAll();
?>

<div class="db-page-header">
    <h1 class="db-page-title">جميع النداءات العاجلة المتوافقة معك</h1>
    <p class="db-page-subtitle">قائمة بكافة الاحتياجات الطارئة لفصيلة دمك في المستشفيات المعتمدة.</p>
</div>

<div class="bento bento--2col">
    <?php if (count($urgent_requests) > 0): ?>
            <?php foreach ($urgent_requests as $req): ?>
                    <?php $percent = ($req['units_needed'] > 0) ? ($req['units_secured'] / $req['units_needed'] * 100) : 0; ?>
                    <div class="request-card">
                        <div class="request-card__stripe"></div>
                        <div class="blood-chip">
                            <?php echo htmlspecialchars($req['blood_type']); ?>
                            <span class="blood-chip__dot"></span>
                        </div>
                        <div class="request-card__body">
                            <div class="request-card__top">
                                <span class="request-card__hospital"><?php echo htmlspecialchars($req['facility_name']); ?></span>
                                <span class="urgency-tag <?php echo ($req['urgency_level'] == 'critical') ? 'urgency-tag--critical' : 'urgency-tag--high'; ?>">
                                    <?php echo ($req['urgency_level'] == 'critical') ? 'حرج جداً' : 'عاجل'; ?>
                                </span>
                            </div>
                            <p class="request-card__location">
                                <span class="material-symbols-outlined">schedule</span>
                                تم توفير <?php echo $req['units_secured']; ?> من <?php echo $req['units_needed']; ?> وحدة
                            </p>
                            <div class="progress-info">
                                <span>نسبة التأمين</span>
                                <span><?php echo round($percent); ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-bar__fill" style="width:<?php echo $percent; ?>%"></div>
                            </div>
                            <div class="mt-md">
                                <?php if ($req['urgency_level'] == 'critical'): ?>
                                        <button class="btn-primary">حجز موعد تبرع</button>
                                <?php else: ?>
                                        <button class="btn-outline">حجز موعد تبرع</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            <?php endforeach; ?>
    <?php else: ?>
            <div class="empty-state col-span-full">
                الحمدلله، لا توجد نداءات طارئة لفصيلتك في الوقت الحالي.
            </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/donor_footer.php'; ?>
