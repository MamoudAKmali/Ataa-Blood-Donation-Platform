<?php
require_once '../../includes/hospital_auth.php';
require_once '../../includes/db_connect.php';

$page_title = 'إطلاق نداء استغاثة - عطاء';
$success = '';
$error = '';

$facility_id = $_SESSION['facility_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_type = $_POST['blood_type'] ?? '';
    $units_needed = intval($_POST['units_needed'] ?? 1);
    $urgency_level = $_POST['urgency_level'] ?? 'high';
    $patient_name = trim($_POST['patient_name'] ?? '');

    if (empty($blood_type) || $units_needed < 1) {
        $error = "يرجى تعبئة جميع الحقول بشكل صحيح.";
    } else {
        $insert = $pdo->prepare("INSERT INTO blood_requests (facility_id, patient_name, blood_type, units_needed, urgency_level) VALUES (?, ?, ?, ?, ?)");
        
        $patient_name_val = empty($patient_name) ? null : $patient_name;

        if ($insert->execute([$facility_id, $patient_name_val, $blood_type, $units_needed, $urgency_level])) {
            $success = "تم إطلاق نداء الاستغاثة بنجاح وسيتم إشعاره للمتبرعين المتوافقين والأقرب لمستشفاك!";
        } else {
            $error = "حدث خطأ أثناء إطلاق النداء.";
        }
    }
}

require_once '../../includes/dashboard_header.php';
?>

<div class="db-page-header-row">
    <div class="db-page-header">
        <h1 class="db-page-title"><span class="material-symbols-outlined text-error" style="vertical-align: middle; font-size: 28px;">campaign</span> إطلاق نداء استغاثة عاجل</h1>
        <p class="db-page-subtitle">أرسل نداءً فورياً للمتبرعين المتاحين لتأمين فصيلة الدم المطلوبة</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="bento mb-lg">
        <div class="db-table-card text-center" style="padding: 40px;">
            <div style="width: 80px; height: 80px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                <span class="material-symbols-outlined" style="font-size: 40px;">check_circle</span>
            </div>
            <h2 class="mb-sm">تم إطلاق النداء بنجاح!</h2>
            <p class="text-secondary mb-md"><?php echo htmlspecialchars($success); ?></p>
            <a href="index.php" class="btn-primary" style="display: inline-flex;">
                <span class="material-symbols-outlined">dashboard</span>
                العودة لمركز العمليات
            </a>
        </div>
    </div>
<?php else: ?>

<div class="bento bento--2col">
    <!-- Form Section -->
    <div class="db-table-card" style="padding: 32px;">
        <h3 class="section-header__title mb-md">تفاصيل النداء</h3>
        
        <?php if ($error): ?>
            <div class="alert alert--error mb-md"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="create_request.php">
            <div class="form-row">
                <div class="form-group w-full">
                    <label class="form-label">اسم المريض (اختياري)</label>
                    <input type="text" name="patient_name" class="form-input w-full" placeholder="اتركه فارغاً إذا كان النداء عبارة عن حملة تبرع عامة أو للمخزون">
                </div>
            </div>

            <div class="form-row" style="display: flex; gap: 16px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">فصيلة الدم المطلوبة <span class="text-error">*</span></label>
                    <select name="blood_type" class="form-input w-full" required>
                        <option value="">-- اختر الفصيلة --</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">عدد الأكياس المطلوبة <span class="text-error">*</span></label>
                    <input type="number" name="units_needed" class="form-input w-full" value="1" min="1" max="100" required>
                </div>
            </div>

            <div class="form-group mb-lg">
                <label class="form-label">مستوى الأولوية والخطورة <span class="text-error">*</span></label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px;">
                    
                    <label style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; cursor: pointer; display: flex; align-items: flex-start; gap: 12px;">
                        <input type="radio" name="urgency_level" value="normal" style="margin-top: 4px;">
                        <div style="text-align: right;">
                            <div class="font-bold">عادي</div>
                            <div class="text-xs text-secondary mt-xs">احتياج مستقبلي أو مخزون</div>
                        </div>
                    </label>

                    <label style="border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; cursor: pointer; display: flex; align-items: flex-start; gap: 12px; background: #fffbeb;">
                        <input type="radio" name="urgency_level" value="high" checked style="margin-top: 4px;">
                        <div style="text-align: right;">
                            <div class="font-bold text-warning">عاجل</div>
                            <div class="text-xs text-secondary mt-xs">احتياج خلال 24 ساعة</div>
                        </div>
                    </label>

                    <label style="border: 1px solid #fca5a5; border-radius: 8px; padding: 12px 16px; cursor: pointer; display: flex; align-items: flex-start; gap: 12px; background: #fef2f2;">
                        <input type="radio" name="urgency_level" value="critical" style="margin-top: 4px;">
                        <div style="text-align: right;">
                            <div class="font-bold text-error">حرج جداً</div>
                            <div class="text-xs text-secondary mt-xs">احتياج فوري وحياة أو موت</div>
                        </div>
                    </label>

                </div>
            </div>

            <button type="submit" class="btn-primary w-full" style="justify-content: center; font-size: 16px; padding: 14px;">
                <span class="material-symbols-outlined">campaign</span>
                بث النداء للمتبرعين المطابقين
            </button>
        </form>
    </div>

    <!-- Info Section -->
    <div>
        <div class="db-table-card" style="padding: 24px; background: #f8fafc;">
            <h4 class="font-bold mb-md" style="display: flex; align-items: center; gap: 8px; color: var(--clr-primary);">
                <span class="material-symbols-outlined">info</span>
                كيف يعمل نظام النداءات؟
            </h4>
            <ul class="text-sm text-secondary" style="line-height: 1.8; list-style: none; padding: 0;">
                <li style="margin-bottom: 12px; display: flex; gap: 8px;">
                    <span class="material-symbols-outlined text-success" style="font-size: 18px;">check_circle</span>
                    سيكون الطلب مرئياً فقط للمتبرعين الذين يحملون الفصيلة المطابقة لطلبك.
                </li>
                <li style="margin-bottom: 12px; display: flex; gap: 8px;">
                    <span class="material-symbols-outlined text-success" style="font-size: 18px;">location_on</span>
                    يتم إعطاء الأولوية في الظهور وإرسال الإشعارات للمتبرعين المتواجدين في محيط المستشفى لضمان سرعة الاستجابة.
                </li>
                <li style="margin-bottom: 12px; display: flex; gap: 8px;">
                    <span class="material-symbols-outlined text-success" style="font-size: 18px;">visibility_off</span>
                    إذا تركت اسم المريض فارغاً، سيظهر الطلب كـ "حملة تبرع" أو طلب لدعم مخزون المستشفى.
                </li>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once '../../includes/dashboard_footer.php'; ?>
