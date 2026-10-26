<?php
require_once '../../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$page_title = 'إدارة المستخدمين - عطاء';

// ── حذف مستخدم ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)($_POST['user_id'] ?? 0);

    if ($_POST['action'] === 'delete_user' && $id > 0) {
        try {
            $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$id]);
            $success_msg = "تم حذف المستخدم بنجاح.";
        } catch (PDOException $e) {
            $error_msg = "تعذّر حذف المستخدم، قد تكون له سجلات مرتبطة.";
        }
    }
}

$stmt = $pdo->query("SELECT id, name, email, phone, role, blood_type, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once '../../includes/admin_head.php';
?>

<?php require_once '../../includes/admin_topbar.php'; ?>

<div class="db-layout">
    <?php require_once '../../includes/admin_sidebar.php'; ?>

    <main class="db-main">

        <div class="db-page-header-row">
            <div class="db-page-header">
                <h1 class="db-page-title">إدارة المستخدمين</h1>
                <p class="db-page-subtitle">استعراض وتعديل بيانات المتبرعين والمرضى</p>
            </div>
        </div>

        <?php if (isset($success_msg)): ?>
        <div id="php-flash" data-type="success" data-msg="<?php echo htmlspecialchars($success_msg); ?>"></div>
        <?php endif; ?>
        <?php if (isset($error_msg)): ?>
        <div id="php-flash" data-type="error" data-msg="<?php echo htmlspecialchars($error_msg); ?>"></div>
        <?php endif; ?>

        <div class="db-table-card">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الهاتف</th>
                        <th>الدور</th>
                        <th>الفصيلة</th>
                        <th>تاريخ التسجيل</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr id="row-<?php echo $u['id']; ?>">
                        <td>
                            <div class="table-name-cell__main"><?php echo htmlspecialchars($u['name']); ?></div>
                            <div class="table-name-cell__sub"><?php echo htmlspecialchars($u['email']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($u['phone'] ?: '—'); ?></td>
                        <td>
                            <?php if ($u['role'] == 'donor'): ?>
                                <span class="status-tag status-tag--done">متبرع</span>
                            <?php else: ?>
                                <span class="status-tag status-tag--pending">مريض</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['blood_type'] && $u['blood_type'] != 'Unknown'): ?>
                                <span class="blood-type-cell"><?php echo $u['blood_type']; ?></span>
                            <?php else: ?>
                                <span class="blood-type-cell--empty">غير محدد</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('Y/m/d', strtotime($u['created_at'])); ?></td>
                        <td>
                            <div class="actions-cell">
                                <form method="POST" action=""
                                    data-confirm="سيتم حذف المستخدم (<?php echo htmlspecialchars($u['name']); ?>) نهائياً ولا يمكن التراجع."
                                    data-confirm-title="حذف المستخدم"
                                    data-confirm-type="danger"
                                    data-confirm-ok="نعم، احذف"
                                    data-confirm-no="إلغاء">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" class="action-btn" title="حذف المستخدم">
                                        <span class="material-symbols-outlined text-error">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-lg text-muted">لا يوجد مستخدمون مسجلون بعد.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once '../../includes/admin_foot.php'; ?>
