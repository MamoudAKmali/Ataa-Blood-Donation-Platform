<?php
require_once '../../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$page_title = 'إدارة أسئلة الاستبانة الطبية - عطاء';

// ── قراءة رسائل الجلسة (PRG Pattern) ───────────────────────────
$success_msg = $_SESSION['sq_success'] ?? '';
$error_msg   = $_SESSION['sq_error'] ?? '';
unset($_SESSION['sq_success'], $_SESSION['sq_error']);

// ── معالجة الطلبات ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // إضافة سؤال جديد
    if ($action === 'add') {
        $q = trim($_POST['question_text'] ?? '');
        $r = trim($_POST['rejection_reason'] ?? '');
        if ($q && $r) {
            $pdo->prepare("INSERT INTO screening_questions (question_text, rejection_reason) VALUES (?, ?)")
                ->execute([$q, $r]);
            $_SESSION['sq_success'] = 'تمت إضافة السؤال بنجاح.';
        } else {
            $_SESSION['sq_error'] = 'يرجى تعبئة حقل السؤال وسبب الرفض.';
        }
    }

    // تعديل سؤال
    if ($action === 'edit') {
        $id = (int)($_POST['question_id'] ?? 0);
        $q  = trim($_POST['question_text'] ?? '');
        $r  = trim($_POST['rejection_reason'] ?? '');
        if ($id && $q && $r) {
            $pdo->prepare("UPDATE screening_questions SET question_text=?, rejection_reason=? WHERE id=?")
                ->execute([$q, $r, $id]);
            $_SESSION['sq_success'] = 'تم تعديل السؤال بنجاح.';
        } else {
            $_SESSION['sq_error'] = 'بيانات التعديل غير مكتملة.';
        }
    }

    // تبديل الحالة (تفعيل / إيقاف)
    if ($action === 'toggle') {
        $id = (int)($_POST['question_id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE screening_questions SET is_active = NOT is_active WHERE id=?")->execute([$id]);
            $_SESSION['sq_success'] = 'تم تغيير حالة السؤال.';
        }
    }

    // حذف سؤال
    if ($action === 'delete') {
        $id = (int)($_POST['question_id'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM screening_questions WHERE id=?")->execute([$id]);
            $_SESSION['sq_success'] = 'تم حذف السؤال نهائياً.';
        }
    }

    // PRG: إعادة التوجيه دائماً بعد أي POST لمنع إعادة الإرسال عند التحديث
    header('Location: screening_questions.php');
    exit;
}

// ── جلب الأسئلة ─────────────────────────────────────────────────
$questions = $pdo->query("SELECT * FROM screening_questions ORDER BY id ASC")->fetchAll();

require_once '../../includes/admin_head.php';
?>

<?php require_once '../../includes/admin_topbar.php'; ?>

<div class="db-layout">
    <?php require_once '../../includes/admin_sidebar.php'; ?>

    <main class="db-main">

        <div class="db-page-header-row">
            <div class="db-page-header">
                <h1 class="db-page-title">أسئلة الاستبانة الطبية</h1>
                <p class="db-page-subtitle">إدارة الأسئلة التي يجيب عليها المتبرع قبل تأكيد تبرعه</p>
            </div>
            <button class="btn-primary" onclick="openAddModal()">
                <span class="material-symbols-outlined">add</span>
                إضافة سؤال جديد
            </button>
        </div>

        <?php if ($success_msg): ?>
        <div id="php-flash" data-type="success" data-msg="<?php echo htmlspecialchars($success_msg); ?>"></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
        <div id="php-flash" data-type="error" data-msg="<?php echo htmlspecialchars($error_msg); ?>"></div>
        <?php endif; ?>

        <div class="db-table-card">
            <?php if (count($questions) === 0): ?>
                <div class="empty-state">
                    <span class="material-symbols-outlined">health_and_safety</span>
                    <p>لا توجد أسئلة مضافة بعد.</p>
                </div>
            <?php else: ?>
            <table class="db-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نص السؤال</th>
                        <th>سبب الرفض</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $i => $q): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($q['question_text']); ?></td>
                        <td class="text-secondary text-sm"><?php echo htmlspecialchars($q['rejection_reason']); ?></td>
                        <td>
                            <?php if ($q['is_active']): ?>
                                <span class="badge badge--success">مفعّل</span>
                            <?php else: ?>
                                <span class="badge badge--urgent">موقوف</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <!-- تعديل -->
                                <button class="action-btn edit-trigger"
                                    title="تعديل"
                                    data-id="<?php echo $q['id']; ?>"
                                    data-question="<?php echo htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-reason="<?php echo htmlspecialchars($q['rejection_reason'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="material-symbols-outlined text-primary">edit</span>
                                </button>

                                <!-- تبديل الحالة -->
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                    <button type="submit" class="action-btn"
                                        title="<?php echo $q['is_active'] ? 'إيقاف السؤال' : 'تفعيل السؤال'; ?>">
                                        <?php if ($q['is_active']): ?>
                                            <span class="material-symbols-outlined text-success">toggle_on</span>
                                        <?php else: ?>
                                            <span class="material-symbols-outlined text-muted">toggle_off</span>
                                        <?php endif; ?>
                                    </button>
                                </form>

                                <!-- حذف -->
                                <form method="POST" action="" onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال نهائياً؟')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                    <button type="submit" class="action-btn" title="حذف">
                                        <span class="material-symbols-outlined text-error">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- ── Modal: إضافة سؤال ───────────────────────────────────────── -->
<div id="addModal" class="sq-modal-overlay" onclick="closeOnOverlay(event, 'addModal')">
    <div class="sq-modal">
        <div class="sq-modal__head">
            <h2>إضافة سؤال جديد</h2>
            <button class="sq-modal__close" onclick="document.getElementById('addModal').style.display='none'">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="sq-modal__body">
                <div class="form-group">
                    <label class="form-label">نص السؤال *</label>
                    <textarea name="question_text" class="form-control sq-textarea" placeholder="مثال: هل تعاني من أعراض الحمى أو السعال حالياً؟" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">سبب الرفض (يُعرض للمتبرع عند إجابته بـ "نعم") *</label>
                    <textarea name="rejection_reason" class="form-control sq-textarea" placeholder="مثال: يجب أن تكون بصحة جيدة ولا تعاني من أعراض مرضية..." required></textarea>
                </div>
            </div>
            <div class="sq-modal__footer">
                <button type="button" class="btn-outline" onclick="document.getElementById('addModal').style.display='none'">إلغاء</button>
                <button type="submit" class="btn-primary">
                    <span class="material-symbols-outlined">save</span>
                    حفظ السؤال
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Modal: تعديل سؤال ───────────────────────────────────────── -->
<div id="editModal" class="sq-modal-overlay" onclick="closeOnOverlay(event, 'editModal')">
    <div class="sq-modal">
        <div class="sq-modal__head">
            <h2>تعديل السؤال</h2>
            <button class="sq-modal__close" onclick="document.getElementById('editModal').style.display='none'">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="question_id" id="editId">
            <div class="sq-modal__body">
                <div class="form-group">
                    <label class="form-label">نص السؤال *</label>
                    <textarea name="question_text" id="editQuestion" class="form-control sq-textarea" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">سبب الرفض *</label>
                    <textarea name="rejection_reason" id="editReason" class="form-control sq-textarea" required></textarea>
                </div>
            </div>
            <div class="sq-modal__footer">
                <button type="button" class="btn-outline" onclick="document.getElementById('editModal').style.display='none'">إلغاء</button>
                <button type="submit" class="btn-primary">
                    <span class="material-symbols-outlined">save</span>
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.sq-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.55);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 16px;
    backdrop-filter: blur(4px);
}
.sq-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}
.sq-modal__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f0f0f0;
    position: sticky;
    top: 0;
    background: #fff;
    border-radius: 16px 16px 0 0;
    z-index: 1;
}
.sq-modal__head h2 {
    font-size: 17px;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary);
}
.sq-modal__close {
    background: #f1f5f9;
    border: none;
    cursor: pointer;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    transition: background 0.2s;
}
.sq-modal__close:hover { background: #e2e8f0; }
.sq-modal__close .material-symbols-outlined { font-size: 18px; }
.sq-modal__body { padding: 24px; }
.sq-modal__footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 16px 24px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
    border-radius: 0 0 16px 16px;
}
.sq-textarea {
    min-height: 90px;
    resize: vertical;
    line-height: 1.6;
}
.actions-cell {
    display: flex;
    align-items: center;
    gap: 4px;
}
.action-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.action-btn:hover { background: #f1f5f9; }
.action-btn .material-symbols-outlined { font-size: 20px; }
.btn-icon--warning {
    color: #b45309;
    background: #fffbeb;
    border-color: #fde68a;
}
.btn-icon--warning:hover {
    background: #fef3c7;
}
.btn-icon--success {
    color: #15803d;
    background: #f0fdf4;
    border-color: #bbf7d0;
}
.btn-icon--success:hover {
    background: #dcfce7;
}
@media (max-width: 600px) {
    .sq-modal__footer { flex-direction: column; }
    .sq-modal__footer .btn-primary,
    .sq-modal__footer .btn-outline { justify-content: center; }
}
</style>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}

// Edit buttons — use data attributes to avoid quote conflicts
document.querySelectorAll('.edit-trigger').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('editId').value       = this.dataset.id;
        document.getElementById('editQuestion').value = this.dataset.question;
        document.getElementById('editReason').value   = this.dataset.reason;
        document.getElementById('editModal').style.display = 'flex';
    });
});

function closeOnOverlay(event, modalId) {
    if (event.target === document.getElementById(modalId)) {
        document.getElementById(modalId).style.display = 'none';
    }
}
</script>

<?php require_once '../../includes/admin_foot.php'; ?>
