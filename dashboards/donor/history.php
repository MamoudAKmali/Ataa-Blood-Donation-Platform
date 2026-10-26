<?php
require_once '../../includes/donor_header.php';

$stmt = $pdo->prepare("
    SELECT d.*, f.name as facility_name 
    FROM donations d 
    JOIN facilities f ON d.facility_id = f.id 
    WHERE d.donor_id = ? 
    ORDER BY d.appointment_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();
?>

<div class="db-page-header">
    <h1 class="db-page-title">سجل التبرعات السابقة</h1>
    <p class="db-page-subtitle">تتبع تفاصيل تبرعاتك بالدم ومقدار النقاط المكتسبة.</p>
</div>

<div class="db-table-card">
    <?php if (count($history) > 0): ?>
        <table class="db-table">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>المستشفى</th>
                    <th>الوحدات</th>
                    <th>النقاط المكتسبة</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($history as $r): ?>
                <tr>
                    <td><?php echo date('Y-m-d', strtotime($r['appointment_date'])); ?></td>
                    <td><?php echo htmlspecialchars($r['facility_name']); ?></td>
                    <td><?php echo $r['units_donated']; ?></td>
                    <td class="points-cell">+<?php echo $r['points_earned']; ?></td>
                    <td>
                        <?php if ($r['status'] == 'completed'): ?>
                            <span class="status-tag status-tag--done">مكتمل</span>
                        <?php elseif ($r['status'] == 'pending'): ?>
                            <span class="status-tag status-tag--pending">قيد الانتظار</span>
                        <?php else: ?>
                            <span class="status-tag status-tag--cancel">ملغى</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">لم تقم بأي تبرع حتى الآن. قطرة من دمك تنقذ حياة!</div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/donor_footer.php'; ?>
