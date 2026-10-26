<?php
$page_title = "ملفي الشخصي - عطاء";
require_once '../../includes/donor_header.php';

$error   = '';
$success = '';

$stmt = $pdo->prepare("SELECT name, email, phone, blood_type, gender, points, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations WHERE donor_id = ? AND status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$donation_count = $stmt->fetch()['total'];

$gender_labels = ['male' => 'ذكر', 'female' => 'أنثى', 'unknown' => 'غير محدد'];
$gender_label  = $gender_labels[$user['gender']] ?? 'غير محدد';
$initials      = mb_substr($user['name'], 0, 1, 'UTF-8');
$member_since  = date('Y', strtotime($user['created_at']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $blood_type = $_POST['blood_type'] ?? 'Unknown';
    $password   = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($phone)) {
        $error = 'الرجاء تعبئة جميع الحقول الإجبارية.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error = 'البريد الإلكتروني مستخدم في حساب آخر.';
        } else {
            if (!empty($password)) {
                $hash   = password_hash($password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, blood_type=?, password_hash=? WHERE id=?");
                $ok     = $update->execute([$name, $email, $phone, $blood_type, $hash, $_SESSION['user_id']]);
            } else {
                $update = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, blood_type=? WHERE id=?");
                $ok     = $update->execute([$name, $email, $phone, $blood_type, $_SESSION['user_id']]);
            }
            if ($ok) {
                $success            = 'تم حفظ التغييرات بنجاح!';
                $user['name']       = $name;
                $user['email']      = $email;
                $user['phone']      = $phone;
                $user['blood_type'] = $blood_type;
                $initials           = mb_substr($name, 0, 1, 'UTF-8');
            } else {
                $error = 'حدث خطأ أثناء التحديث.';
            }
        }
    }
}
?>

<style>
/* ── Hero ────────────────────────────────────────────── */
.profile-hero {
    background: linear-gradient(135deg, #8b0000 0%, #b52619 55%, #d94a35 100%);
    border-radius: 20px;
    padding: 40px 36px;
    display: flex;
    align-items: center;
    gap: 28px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(139,0,0,0.28);
}
.profile-hero::before {
    content: '';
    position: absolute;
    top: -70px; left: -70px;
    width: 240px; height: 240px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    pointer-events: none;
}
.profile-hero::after {
    content: '';
    position: absolute;
    bottom: -90px; right: -40px;
    width: 280px; height: 280px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    pointer-events: none;
}
.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: 3px solid rgba(255,255,255,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    font-weight: 800;
    color: #fff;
    font-family: var(--font-heading);
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.profile-hero-info {
    position: relative;
    z-index: 1;
}
.profile-hero-name {
    font-size: 26px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 10px;
    font-family: var(--font-heading);
}
.profile-hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 18px;
}
.profile-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.15);
    color: #fff;
    padding: 5px 14px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,0.2);
}
.profile-badge .material-symbols-outlined { font-size: 15px; }
.profile-stats {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}
.profile-stat {
    text-align: center;
    background: rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 10px 22px;
    border: 1px solid rgba(255,255,255,0.15);
    min-width: 90px;
}
.profile-stat__num {
    font-size: 24px;
    font-weight: 800;
    color: #fff;
    line-height: 1;
    font-family: var(--font-heading);
}
.profile-stat__label {
    font-size: 11px;
    color: rgba(255,255,255,0.8);
    margin-top: 4px;
}

/* ── Section Card ───────────────────────────────────── */
.profile-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    margin-bottom: 24px;
    overflow: hidden;
}
.profile-card__head {
    padding: 18px 28px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.profile-card__head h2 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}
.profile-card__head .material-symbols-outlined {
    color: var(--primary);
    font-size: 22px;
}
.profile-card__body {
    padding: 28px;
}
.profile-card__footer {
    padding: 18px 28px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.profile-footer-hint {
    font-size: 13px;
    color: var(--text-muted);
}

/* ── Form Grid ──────────────────────────────────────── */
.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
}
.profile-field > label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.profile-field input,
.profile-field select {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-family: var(--font-body);
    font-size: 15px;
    color: var(--text-primary);
    background: #fafafa;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    display: block;
}
.profile-field input:focus,
.profile-field select:focus {
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(181,38,25,0.08);
}
.profile-field-hint {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 6px;
}
.profile-field-hint .material-symbols-outlined { font-size: 14px; }

/* ── Gender Lock ────────────────────────────────────── */
.gender-locked {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 15px;
    color: var(--text-secondary);
    width: 100%;
}
.gender-locked .material-symbols-outlined {
    font-size: 18px;
    color: #94a3b8;
}

/* ── Save Button ────────────────────────────────────── */
.profile-save-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-family: var(--font-body);
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(181,38,25,0.3);
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
}
.profile-save-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(181,38,25,0.4);
}
.profile-save-btn:active { transform: translateY(0); }
.profile-save-btn .material-symbols-outlined { font-size: 18px; }

/* ── Alert ──────────────────────────────────────────── */
.profile-alert {
    padding: 14px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
}
.profile-alert--success {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.profile-alert--error {
    background: #fff1f2;
    color: #be123c;
    border: 1px solid #fecdd3;
}
.profile-alert .material-symbols-outlined { font-size: 20px; }

/* ── Mobile ─────────────────────────────────────────── */
@media (max-width: 600px) {
    .profile-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 24px 18px;
    }
    .profile-card__body { padding: 20px 16px; }
    .profile-card__footer { flex-direction: column; align-items: stretch; }
    .profile-save-btn { justify-content: center; }
}
</style>

<?php if ($success): ?>
<div class="profile-alert profile-alert--success">
    <span class="material-symbols-outlined">check_circle</span>
    <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="profile-alert profile-alert--error">
    <span class="material-symbols-outlined">error</span>
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<!-- Hero -->
<div class="profile-hero">
    <div class="profile-avatar"><?php echo htmlspecialchars($initials); ?></div>
    <div class="profile-hero-info">
        <h1 class="profile-hero-name"><?php echo htmlspecialchars($user['name']); ?></h1>
        <div class="profile-hero-meta">
            <span class="profile-badge">
                <span class="material-symbols-outlined">water_drop</span>
                <?php echo htmlspecialchars($user['blood_type']); ?>
            </span>
            <span class="profile-badge">
                <span class="material-symbols-outlined">wc</span>
                <?php echo htmlspecialchars($gender_label); ?>
            </span>
            <span class="profile-badge">
                <span class="material-symbols-outlined">calendar_today</span>
                عضو منذ <?php echo $member_since; ?>
            </span>
        </div>
        <div class="profile-stats">
            <div class="profile-stat">
                <div class="profile-stat__num"><?php echo $donation_count; ?></div>
                <div class="profile-stat__label">تبرع مكتمل</div>
            </div>
            <div class="profile-stat">
                <div class="profile-stat__num"><?php echo (int)($user['points'] ?? 0); ?></div>
                <div class="profile-stat__label">نقطة</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="">

    <!-- Personal Info -->
    <div class="profile-card">
        <div class="profile-card__head">
            <span class="material-symbols-outlined">person</span>
            <h2>المعلومات الشخصية</h2>
        </div>
        <div class="profile-card__body">
            <div class="profile-grid">
                <div class="profile-field">
                    <label>الاسم الكامل *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required placeholder="الاسم الكامل">
                </div>
                <div class="profile-field">
                    <label>رقم الهاتف *</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="profile-field">
                    <label>فصيلة الدم</label>
                    <select name="blood_type">
                        <?php
                        $types = ['Unknown'=>'غير معروف','A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-'];
                        foreach ($types as $val => $lbl): ?>
                            <option value="<?php echo $val; ?>" <?php echo ($user['blood_type'] === $val) ? 'selected' : ''; ?>>
                                <?php echo $lbl; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="profile-field">
                    <label>الجنس</label>
                    <div class="gender-locked">
                        <span class="material-symbols-outlined">lock</span>
                        <?php echo htmlspecialchars($gender_label); ?>
                    </div>
                    <p class="profile-field-hint">
                        <span class="material-symbols-outlined">info</span>
                        مرتبط بالفترات الطبية، لا يمكن تعديله.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Security -->
    <div class="profile-card">
        <div class="profile-card__head">
            <span class="material-symbols-outlined">lock_person</span>
            <h2>بيانات الحساب والأمان</h2>
        </div>
        <div class="profile-card__body">
            <div class="profile-grid">
                <div class="profile-field">
                    <label>البريد الإلكتروني *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="profile-field">
                    <label>كلمة المرور الجديدة</label>
                    <input type="password" name="password" placeholder="اتركه فارغاً للإبقاء على الحالية">
                    <p class="profile-field-hint">
                        <span class="material-symbols-outlined">shield</span>
                        8 أحرف على الأقل تشمل أرقاماً ورموزاً.
                    </p>
                </div>
            </div>
        </div>
        <div class="profile-card__footer">
            <span class="profile-footer-hint">أي تغيير سيُحفظ فور الضغط على الزر</span>
            <button type="submit" class="profile-save-btn">
                <span class="material-symbols-outlined">save</span>
                حفظ التغييرات
            </button>
        </div>
    </div>

</form>

<?php require_once '../../includes/donor_footer.php'; ?>
