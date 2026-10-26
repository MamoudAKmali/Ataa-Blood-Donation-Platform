<?php
require_once '../../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$page_title = 'النداءات العاجلة - عطاء';

$stmt = $pdo->query("
    SELECT r.id, r.blood_type, r.units_needed, r.units_secured,
           r.urgency_level, r.status, r.created_at, f.name AS facility_name
    FROM blood_requests r
    JOIN facilities f ON r.facility_id = f.id
    ORDER BY r.created_at DESC
");
$requests = $stmt->fetchAll();

require_once '../../includes/admin_head.php';
?>

<?php require_once '../../includes/admin_topbar.php'; ?>

<div class="db-layout">
    <?php require_once '../../includes/admin_sidebar.php'; ?>

    <main class="db-main">
        <div class="db-page-header-row">
            <div class="db-page-header">
                <h1 class="db-page-title">النداءات العاجلة</h1>
                <p class="db-page-subtitle">مراقبة طلبات الدم وحالات الطوارئ في المستشفيات</p>
            </div>
        </div>

        <div class="db-table-card">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>المستشفى</th>
                        <th>الفصيلة</th>
                        <th>الاحتياج</th>
                        <th>الأولوية</th>
                        <th>الحالة</th>
                        <th>وقت الطلب</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                    <tr>
                        <td class="table-name-cell__sub">#<?php echo $r['id']; ?></td>
                        <td class="table-name-cell__main"><?php echo htmlspecialchars($r['facility_name']); ?></td>
                        <td><span class="blood-type-cell"><?php echo $r['blood_type']; ?></span></td>
                        <td>
                            <div class="table-name-cell__main"><?php echo $r['units_needed']; ?> وحدات</div>
                            <div class="table-name-cell__sub text-success">تم تأمين <?php echo $r['units_secured']; ?></div>
                        </td>
                        <td>
                            <?php if ($r['urgency_level'] == 'critical'): ?>
                                <span class="status-tag status-tag--cancel">حرج جداً</span>
                            <?php elseif ($r['urgency_level'] == 'high'): ?>
                                <span class="status-tag status-tag--pending">عالي</span>
                            <?php else: ?>
                                <span class="status-tag status-tag--done">عادي</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['status'] == 'open'): ?>
                                <span class="status-tag status-tag--pending">نشط</span>
                            <?php elseif ($r['status'] == 'fulfilled'): ?>
                                <span class="status-tag status-tag--done">مكتمل</span>
                            <?php else: ?>
                                <span class="status-tag status-tag--cancel">مغلق</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-name-cell__sub"><?php echo date('Y/m/d H:i', strtotime($r['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-lg text-muted">لا توجد نداءات عاجلة مسجلة.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once '../../includes/admin_foot.php'; ?>
