<?php
require_once '../../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$page_title = 'لوحة التحكم - عطاء';

// Fetch stats
$donors    = $pdo->query("SELECT COUNT(*) as c FROM users WHERE role='donor'")->fetch()['c'];
$hospitals = $pdo->query("SELECT COUNT(*) as c FROM facilities WHERE type='hospital'")->fetch()['c'];
$urgent    = $pdo->query("SELECT COUNT(*) as c FROM blood_requests WHERE status='open'")->fetch()['c'];

require_once '../../includes/admin_head.php';
?>

<?php require_once '../../includes/admin_topbar.php'; ?>

<div class="db-layout">
    <?php require_once '../../includes/admin_sidebar.php'; ?>

    <main class="db-main">
        <div class="db-page-header-row">
            <div class="db-page-header">
                <h1 class="db-page-title">لوحة تحكم الإدارة العليا</h1>
                <p class="db-page-subtitle">إحصائيات النظام ومراقبة الأداء العام للمنصة</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="bento mb-lg">
            <div class="stat-card stat-card--dark">
                <div>
                    <div class="stat-card__label">
                        <span class="material-symbols-outlined">group</span>
                        إجمالي المتبرعين
                    </div>
                    <div class="stat-card__number mt-sm"><?php echo $donors; ?></div>
                    <div class="stat-card__desc">متبرع نشط في المنصة</div>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-card__label">
                        <span class="material-symbols-outlined text-primary">local_hospital</span>
                        المستشفيات المعتمدة
                    </div>
                    <div class="stat-card__number mt-sm text-primary"><?php echo $hospitals; ?></div>
                    <div class="stat-card__desc">منشأة طبية مرتبطة</div>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-card__label">
                        <span class="material-symbols-outlined text-error">campaign</span>
                        النداءات النشطة
                    </div>
                    <div class="stat-card__number mt-sm text-error"><?php echo $urgent; ?></div>
                    <div class="stat-card__desc">حالة طارئة تتطلب الدم</div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="section-header mb-md">
            <h3 class="section-header__title">أحدث العمليات في النظام</h3>
        </div>

        <div class="db-table-card">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>نوع العملية</th>
                        <th>الوصف</th>
                        <th>الوقت</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $activities = [];

                    $stmt = $pdo->query("SELECT name, role, blood_type, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 4");
                    foreach ($stmt->fetchAll() as $row) {
                        $activities[] = [
                            'icon'         => 'how_to_reg',
                            'icon_color'   => 'text-success',
                            'title'        => 'تسجيل ' . ($row['role'] == 'donor' ? 'متبرع' : 'مريض'),
                            'desc'         => "انضمام {$row['name']}" . ($row['role'] == 'donor' && $row['blood_type'] != 'Unknown' ? " (فصيلة {$row['blood_type']})" : ""),
                            'time'         => $row['created_at'],
                            'status_text'  => 'مكتمل',
                            'status_class' => 'status-tag--done'
                        ];
                    }

                    $stmt = $pdo->query("SELECT name, created_at FROM facilities ORDER BY created_at DESC LIMIT 3");
                    foreach ($stmt->fetchAll() as $row) {
                        $activities[] = [
                            'icon'         => 'local_hospital',
                            'icon_color'   => 'text-primary',
                            'title'        => 'تسجيل منشأة',
                            'desc'         => "انضمام {$row['name']} للمنصة",
                            'time'         => $row['created_at'],
                            'status_text'  => 'مكتمل',
                            'status_class' => 'status-tag--done'
                        ];
                    }

                    $stmt = $pdo->query("SELECT r.blood_type, r.units_needed, r.status, r.created_at, f.name as fn FROM blood_requests r JOIN facilities f ON r.facility_id = f.id ORDER BY r.created_at DESC LIMIT 5");
                    foreach ($stmt->fetchAll() as $row) {
                        $activities[] = [
                            'icon'         => 'emergency',
                            'icon_color'   => 'text-error',
                            'title'        => 'نداء عاجل',
                            'desc'         => "{$row['fn']} يطلب {$row['units_needed']} وحدات {$row['blood_type']}",
                            'time'         => $row['created_at'],
                            'status_text'  => $row['status'] == 'open' ? 'نشط' : 'مغلق',
                            'status_class' => $row['status'] == 'open' ? 'status-tag--pending' : 'status-tag--done'
                        ];
                    }

                    usort($activities, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));
                    $activities = array_slice($activities, 0, 8);

                    function time_ago($datetime) {
                        $diff = (new DateTime)->diff(new DateTime($datetime));
                        if ($diff->d > 0) return "قبل {$diff->d} يوم";
                        if ($diff->h > 0) return "قبل {$diff->h} ساعة";
                        if ($diff->i > 0) return "قبل {$diff->i} دقيقة";
                        return "الآن";
                    }

                    if (count($activities) > 0):
                        foreach ($activities as $act):
                    ?>
                    <tr>
                        <td>
                            <span class="material-symbols-outlined <?php echo $act['icon_color']; ?>">
                                <?php echo $act['icon']; ?>
                            </span>
                            <?php echo $act['title']; ?>
                        </td>
                        <td><?php echo htmlspecialchars($act['desc']); ?></td>
                        <td class="table-name-cell__sub"><?php echo time_ago($act['time']); ?></td>
                        <td><span class="status-tag <?php echo $act['status_class']; ?>"><?php echo $act['status_text']; ?></span></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-md text-muted">لا توجد عمليات في النظام حتى الآن</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once '../../includes/admin_foot.php'; ?>
