<?php
require_once '../../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

$page_title = 'إدارة المستشفيات - عطاء';

// ── إضافة منشأة ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_facility') {
    $name      = trim($_POST['name']      ?? '');
    $type      = trim($_POST['type']      ?? 'hospital');
    $email     = trim($_POST['email']     ?? '');
    $password  = trim($_POST['password']  ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $address   = trim($_POST['address']   ?? '');
    $latitude  = trim($_POST['latitude']  ?? '') ?: null;
    $longitude = trim($_POST['longitude'] ?? '') ?: null;

    if ($name && $email && $password) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO facilities (name, type, email, password_hash, phone, address, latitude, longitude, is_verified) VALUES (?,?,?,?,?,?,?,?,1)")
                ->execute([$name, $type, $email, $hash, $phone, $address, $latitude, $longitude]);
            $success_msg = "تمت إضافة المنشأة بنجاح وتم توثيقها تلقائياً.";
        } catch (PDOException $e) {
            $error_msg = "خطأ: البريد الإلكتروني مسجل مسبقاً أو حدث خطأ في قاعدة البيانات.";
        }
    } else {
        $error_msg = "الاسم والبريد الإلكتروني وكلمة المرور حقول مطلوبة.";
    }
}

// ── تبديل حالة التوثيق ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_verify') {
    $id      = (int)($_POST['facility_id'] ?? 0);
    $current = (int)($_POST['current_status'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("UPDATE facilities SET is_verified = ? WHERE id = ?")->execute([!$current, $id]);
        $success_msg = $current ? "تم إلغاء توثيق المنشأة." : "تم توثيق المنشأة بنجاح.";
    }
}

// ── حذف منشأة ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_facility') {
    $id = (int)($_POST['facility_id'] ?? 0);
    if ($id > 0) {
        try {
            $pdo->prepare("DELETE FROM facilities WHERE id = ?")->execute([$id]);
            $success_msg = "تم حذف المنشأة بنجاح.";
        } catch (PDOException $e) {
            $error_msg = "تعذّر الحذف، قد تكون للمنشأة طلبات دم مرتبطة.";
        }
    }
}

$stmt = $pdo->query("SELECT id, name, type, email, phone, address, is_verified, created_at FROM facilities ORDER BY created_at DESC");
$facilities = $stmt->fetchAll();

require_once '../../includes/admin_head.php';
?>

<?php require_once '../../includes/admin_topbar.php'; ?>

<div class="db-layout">
    <?php require_once '../../includes/admin_sidebar.php'; ?>

    <main class="db-main">

        <div class="db-page-header-row">
            <div class="db-page-header">
                <h1 class="db-page-title">إدارة المستشفيات</h1>
                <p class="db-page-subtitle">المنشآت الطبية المعتمدة في النظام</p>
            </div>
            <button class="btn-primary--auto" id="openAddModal">
                <span class="material-symbols-outlined">add</span>
                إضافة مستشفى
            </button>
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
                        <th>اسم المنشأة</th>
                        <th>النوع</th>
                        <th>التواصل</th>
                        <th>التوثيق</th>
                        <th>تاريخ الإضافة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facilities as $f): ?>
                    <tr>
                        <td>
                            <div class="table-name-cell__main"><?php echo htmlspecialchars($f['name']); ?></div>
                            <div class="table-name-cell__sub">
                                <span class="material-symbols-outlined">location_on</span>
                                <?php echo htmlspecialchars($f['address'] ?: 'غير محدد'); ?>
                            </div>
                        </td>
                        <td><?php echo $f['type'] == 'hospital' ? 'مستشفى' : 'بنك دم'; ?></td>
                        <td>
                            <div class="table-contact-cell__phone"><?php echo htmlspecialchars($f['phone'] ?: '—'); ?></div>
                            <div class="table-contact-cell__email"><?php echo htmlspecialchars($f['email']); ?></div>
                        </td>
                        <td>
                            <!-- زر تبديل التوثيق -->
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="toggle_verify">
                                <input type="hidden" name="facility_id" value="<?php echo $f['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $f['is_verified']; ?>">
                                <button type="submit" class="status-tag <?php echo $f['is_verified'] ? 'status-tag--done' : 'status-tag--pending'; ?>" title="<?php echo $f['is_verified'] ? 'إلغاء التوثيق' : 'توثيق المنشأة'; ?>" style="cursor:pointer; border:none;">
                                    <?php if ($f['is_verified']): ?>
                                        <span class="material-symbols-outlined">verified</span> موثق
                                    <?php else: ?>
                                        <span class="material-symbols-outlined">pending</span> غير موثق
                                    <?php endif; ?>
                                </button>
                            </form>
                        </td>
                        <td><?php echo date('Y/m/d', strtotime($f['created_at'])); ?></td>
                        <td>
                            <div class="actions-cell">
                                <form method="POST" action=""
                                    data-confirm="سيتم حذف منشأة (<?php echo htmlspecialchars($f['name']); ?>) نهائياً من النظام."
                                    data-confirm-title="حذف المنشأة"
                                    data-confirm-type="danger"
                                    data-confirm-ok="نعم، احذف"
                                    data-confirm-no="إلغاء">
                                    <input type="hidden" name="action" value="delete_facility">
                                    <input type="hidden" name="facility_id" value="<?php echo $f['id']; ?>">
                                    <button type="submit" class="action-btn" title="حذف المنشأة">
                                        <span class="material-symbols-outlined text-error">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($facilities)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-lg text-muted">لا توجد منشآت طبية مسجلة بعد.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- مودال إضافة منشأة -->
<div id="addModal" class="db-modal-backdrop">
    <div class="db-modal-box">
        <button class="db-modal-close" id="closeAddModal">&times;</button>
        <h2 class="db-modal-title">إضافة منشأة طبية جديدة</h2>

        <form method="POST" action="">
            <input type="hidden" name="action" value="add_facility">

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">اسم المنشأة *</label>
                    <input type="text" name="name" required class="form-input" placeholder="مستشفى الملك فهد">
                </div>
                <div class="form-field">
                    <label class="form-label">النوع</label>
                    <select name="type" class="form-select">
                        <option value="hospital">مستشفى</option>
                        <option value="blood_bank">بنك دم</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">البريد الإلكتروني * <span class="form-hint">(يستخدم للدخول)</span></label>
                    <input type="email" name="email" required class="form-input" placeholder="info@hospital.sa">
                </div>
                <div class="form-field">
                    <label class="form-label">كلمة المرور * <span class="form-hint">(لتسجيل دخول المنشأة)</span></label>
                    <div class="password-input-wrap">
                        <input type="password" name="password" required class="form-input" id="facilityPassword" placeholder="••••••••" minlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('facilityPassword', this)" title="إظهار/إخفاء">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-input" placeholder="0501234567">
                </div>
                <div class="form-field">
                    <label class="form-label">العنوان التفصيلي</label>
                    <input type="text" name="address" class="form-input" placeholder="المنطقة، المدينة">
                </div>
            </div>

            <div class="form-field">
                <label class="form-label">
                    <span class="material-symbols-outlined">location_on</span>
                    الموقع الجغرافي
                    <span class="form-hint">(اختياري — يُستخدم في خريطة المراكز)</span>
                </label>
                <div class="form-row">
                    <input type="number" name="latitude"  id="lat-field"  class="form-input" placeholder="خط العرض  24.7136" step="any">
                    <input type="number" name="longitude" id="lng-field"  class="form-input" placeholder="خط الطول  46.6753" step="any">
                </div>
                <button type="button" class="btn-outline-sm" id="getLocationBtn">
                    <span class="material-symbols-outlined">my_location</span>
                    تحديد موقعي تلقائياً
                </button>
            </div>

            <button type="submit" class="btn-primary">
                <span class="material-symbols-outlined">save</span>
                حفظ المنشأة واعتمادها
            </button>
        </form>
    </div>
</div>

<script>
/* ── فتح/إغلاق المودال ── */
var openBtn  = document.getElementById('openAddModal');
var closeBtn = document.getElementById('closeAddModal');
var modal    = document.getElementById('addModal');

if (openBtn)  openBtn.addEventListener('click',  function() { modal.classList.add('is-open'); });
if (closeBtn) closeBtn.addEventListener('click', function() { modal.classList.remove('is-open'); });
if (modal)    modal.addEventListener('click',    function(e) { if (e.target === modal) modal.classList.remove('is-open'); });

/* ── تحديد الموقع تلقائياً ── */
var locBtn = document.getElementById('getLocationBtn');
if (locBtn) {
    locBtn.addEventListener('click', function() {
        if (!navigator.geolocation) {
            Ataa && Ataa.notify('warning', 'المتصفح لا يدعم تحديد الموقع.');
            return;
        }
        locBtn.innerHTML = '<span class="material-symbols-outlined">hourglass_top</span> جاري التحديد...';
        locBtn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                document.getElementById('lat-field').value = pos.coords.latitude.toFixed(6);
                document.getElementById('lng-field').value = pos.coords.longitude.toFixed(6);
                locBtn.innerHTML = '<span class="material-symbols-outlined">check_circle</span> تم التحديد';
                Ataa && Ataa.notify('success', 'تم تحديد الموقع بنجاح.');
                locBtn.disabled = false;
            },
            function() {
                locBtn.innerHTML = '<span class="material-symbols-outlined">my_location</span> تحديد موقعي تلقائياً';
                locBtn.disabled = false;
                Ataa && Ataa.notify('error', 'تعذّر تحديد الموقع. تأكد من منح الإذن.');
            }
        );
    });
}

/* ── إظهار/إخفاء كلمة المرور ── */
function togglePassword(fieldId, btn) {
    var f = document.getElementById(fieldId);
    if (f.type === 'password') {
        f.type = 'text';
        btn.querySelector('.material-symbols-outlined').textContent = 'visibility_off';
    } else {
        f.type = 'password';
        btn.querySelector('.material-symbols-outlined').textContent = 'visibility';
    }
}

/* ── فتح المودال تلقائياً عند وجود خطأ ── */
<?php if (isset($error_msg)): ?>
document.addEventListener('DOMContentLoaded', function() { modal.classList.add('is-open'); });
<?php endif; ?>
</script>


<?php require_once '../../includes/admin_foot.php'; ?>
