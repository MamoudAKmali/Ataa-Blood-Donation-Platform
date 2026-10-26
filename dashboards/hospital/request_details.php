<?php
require_once '../../includes/hospital_auth.php';
require_once '../../includes/db_connect.php';

$facility_id = $_SESSION['facility_id'];
$request_id = $_GET['id'] ?? 0;

// Fetch Request Details
$stmt = $pdo->prepare("
    SELECT * 
    FROM blood_requests 
    WHERE id = ? AND facility_id = ?
");
$stmt->execute([$request_id, $facility_id]);
$req = $stmt->fetch();

if (!$req) {
    die('النداء غير موجود أو لا تملك صلاحية الوصول إليه.');
}

// Handle Form Submissions
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'confirm_donation') {
        $donation_id = $_POST['donation_id'];
        
        try {
            $pdo->beginTransaction();
            
            // Mark donation as completed
            $update_don = $pdo->prepare("UPDATE donations SET status = 'completed', units_donated = 1 WHERE id = ? AND request_id = ? AND status = 'pending'");
            $update_don->execute([$donation_id, $request_id]);
            
            if ($update_don->rowCount() > 0) {
                // Increment secured units
                $pdo->prepare("UPDATE blood_requests SET units_secured = units_secured + 1 WHERE id = ?")->execute([$request_id]);
                
                // Check if fulfilled now
                $check_req = $pdo->prepare("SELECT units_needed, units_secured FROM blood_requests WHERE id = ?");
                $check_req->execute([$request_id]);
                $current_status = $check_req->fetch();
                
                if ($current_status['units_secured'] >= $current_status['units_needed']) {
                    $pdo->prepare("UPDATE blood_requests SET status = 'fulfilled' WHERE id = ?")->execute([$request_id]);
                    $req['status'] = 'fulfilled'; // update local variable for UI
                }
                
                $req['units_secured'] += 1; // update local var for UI
                $success_msg = "تم تأكيد استلام الوحدة من المتبرع بنجاح.";
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "حدث خطأ أثناء تأكيد التبرع.";
        }
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'update_request') {
        $units_needed = intval($_POST['units_needed']);
        $status = $_POST['status'];
        
        $pdo->prepare("UPDATE blood_requests SET units_needed = ?, status = ? WHERE id = ?")->execute([$units_needed, $status, $request_id]);
        
        $req['units_needed'] = $units_needed;
        $req['status'] = $status;
        $success_msg = "تم تحديث بيانات النداء بنجاح.";
    }
}

// Fetch Donors for this request
$stmt_donors = $pdo->prepare("
    SELECT d.id as donation_id, d.status, d.created_at, u.name, u.blood_type, u.phone 
    FROM donations d
    JOIN users u ON d.donor_id = u.id
    WHERE d.request_id = ?
    ORDER BY d.created_at DESC
");
$stmt_donors->execute([$request_id]);
$donors = $stmt_donors->fetchAll();

$page_title = "تفاصيل النداء - عطاء";
require_once '../../includes/dashboard_header.php';
?>

<div class="db-page-header-row">
    <div class="db-page-header">
        <a href="index.php" class="text-secondary text-sm font-bold mb-xs d-block">&larr; العودة لمركز العمليات</a>
        <h1 class="db-page-title">إدارة تفاصيل النداء #<?php echo $req['id']; ?></h1>
        <p class="db-page-subtitle"><?php echo $req['patient_name'] ? 'للمريض: ' . htmlspecialchars($req['patient_name']) : 'طلب داخلي / حملة تبرع'; ?></p>
    </div>
    
    <?php if ($req['status'] == 'open'): ?>
        <span class="status-tag status-tag--pending"><span class="material-symbols-outlined">radio_button_checked</span> نداء نشط</span>
    <?php elseif ($req['status'] == 'fulfilled'): ?>
        <span class="status-tag status-tag--done"><span class="material-symbols-outlined">check_circle</span> نداء مكتمل</span>
    <?php else: ?>
        <span class="status-tag status-tag--cancel">نداء مغلق</span>
    <?php endif; ?>
</div>

<?php if ($success_msg): ?>
<div class="alert alert--success mb-md"><?php echo htmlspecialchars($success_msg); ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
<div class="alert alert--error mb-md"><?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<div class="bento bento--2col mb-lg">
    <!-- بيانات النداء -->
    <div class="db-table-card" style="padding: 24px;">
        <h3 class="section-header__title mb-md">تعديل بيانات النداء</h3>
        <form method="POST" action="request_details.php?id=<?php echo $request_id; ?>">
            <input type="hidden" name="action" value="update_request">
            
            <div class="form-group">
                <label class="form-label">الفصيلة المطلوبة</label>
                <input type="text" class="form-input w-full" value="<?php echo $req['blood_type']; ?>" disabled>
            </div>
            
            <div class="form-group">
                <label class="form-label">الاحتياج الفعلي (تحديث العدد إذا لزم)</label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="number" name="units_needed" class="form-input" style="flex:1;" value="<?php echo $req['units_needed']; ?>" min="1" max="50">
                    <span class="text-sm font-bold text-success">مؤمَّن: <?php echo $req['units_secured']; ?></span>
                </div>
            </div>
            
            <div class="form-group mb-md">
                <label class="form-label">حالة النداء</label>
                <select name="status" class="form-input w-full">
                    <option value="open" <?php echo $req['status'] == 'open' ? 'selected' : ''; ?>>نشط (مفتوح لاستقبال المتبرعين)</option>
                    <option value="fulfilled" <?php echo $req['status'] == 'fulfilled' ? 'selected' : ''; ?>>مكتمل (تم تأمين الاحتياج)</option>
                    <option value="cancelled" <?php echo $req['status'] == 'cancelled' ? 'selected' : ''; ?>>ملغى (تم إيقاف النداء)</option>
                </select>
            </div>
            
            <button type="submit" class="btn-primary w-full" style="justify-content: center;">حفظ التغييرات</button>
        </form>
    </div>
    
    <!-- نسبة الإنجاز -->
    <div class="db-table-card" style="padding: 24px;">
        <h3 class="section-header__title mb-lg">مستوى التأمين</h3>
        
        <?php $percent = ($req['units_needed'] > 0) ? min(100, ($req['units_secured'] / $req['units_needed'] * 100)) : 0; ?>
        
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 48px; font-weight: 800; color: var(--clr-primary); line-height: 1;"><?php echo round($percent); ?>%</div>
            <div class="text-secondary mt-xs">نسبة الوحدات المؤمنة من الهدف</div>
        </div>
        
        <div style="height: 12px; background: #f3f4f6; border-radius: 6px; overflow: hidden; margin-bottom: 32px;">
            <div style="height: 100%; width: <?php echo $percent; ?>%; background: var(--clr-primary); transition: width 0.5s ease;"></div>
        </div>
        
        <div class="flex-between" style="border-top: 1px solid #e5e7eb; padding-top: 16px;">
            <div class="text-center">
                <div class="text-sm text-secondary mb-xs">الهدف الكلي</div>
                <div class="font-bold text-lg"><?php echo $req['units_needed']; ?></div>
            </div>
            <div class="text-center">
                <div class="text-sm text-secondary mb-xs">المستلم فعلياً</div>
                <div class="font-bold text-lg text-success"><?php echo $req['units_secured']; ?></div>
            </div>
            <div class="text-center">
                <div class="text-sm text-secondary mb-xs">المتبقي</div>
                <div class="font-bold text-lg text-error"><?php echo max(0, $req['units_needed'] - $req['units_secured']); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="section-header mb-md">
    <h3 class="section-header__title">المتبرعون المستجيبون للنداء</h3>
</div>

<div class="db-table-card">
    <table class="db-table">
        <thead>
            <tr>
                <th>المتبرع</th>
                <th>الفصيلة</th>
                <th>رقم التواصل</th>
                <th>وقت الاستجابة</th>
                <th>الحالة</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($donors as $d): ?>
            <tr>
                <td class="table-name-cell__main"><?php echo htmlspecialchars($d['name']); ?></td>
                <td><span class="blood-type-cell"><?php echo $d['blood_type']; ?></span></td>
                <td class="table-name-cell__sub" dir="ltr"><?php echo htmlspecialchars($d['phone']); ?></td>
                <td class="table-name-cell__sub"><?php echo date('H:i - Y/m/d', strtotime($d['created_at'])); ?></td>
                <td>
                    <?php if ($d['status'] == 'completed'): ?>
                        <span class="status-tag status-tag--done">تم التبرع</span>
                    <?php elseif ($d['status'] == 'pending'): ?>
                        <span class="status-tag status-tag--pending">في الطريق (معلّق)</span>
                    <?php else: ?>
                        <span class="status-tag status-tag--cancel">ملغى</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($d['status'] == 'pending' && $req['status'] == 'open'): ?>
                        <form method="POST" action="request_details.php?id=<?php echo $request_id; ?>">
                            <input type="hidden" name="action" value="confirm_donation">
                            <input type="hidden" name="donation_id" value="<?php echo $d['donation_id']; ?>">
                            <button class="btn btn--primary" style="padding: 4px 12px; font-size: 12px;">
                                <span class="material-symbols-outlined" style="font-size: 14px;">check</span> تأكيد الحضور والتبرع
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted text-sm">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($donors)): ?>
            <tr>
                <td colspan="6" class="text-center py-lg text-muted">لا يوجد متبرعين استجابوا لهذا النداء حتى الآن.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/dashboard_footer.php'; ?>
