<?php
require_once '../../includes/donor_header.php';

// Fetch User Info
$stmt = $pdo->prepare("SELECT name, blood_type, points, latitude, longitude, gender FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_info = $stmt->fetch();

// Fetch Donation Stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(units_donated) as units FROM donations WHERE donor_id = ? AND status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Fetch Last Donation
$stmt = $pdo->prepare("
    SELECT d.created_at, f.name as facility_name 
    FROM donations d 
    JOIN blood_requests br ON d.request_id = br.id 
    JOIN facilities f ON br.facility_id = f.id 
    WHERE d.donor_id = ? AND d.status = 'completed' 
    ORDER BY d.created_at DESC LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$last_donation = $stmt->fetch();

// Medical Eligibility Logic
$is_eligible = true;
$next_eligible_date = null;

if ($last_donation) {
    $last_date = new DateTime($last_donation['created_at']);
    $wait_days = ($user_info['gender'] === 'female') ? 120 : 90;

    $next_date = clone $last_date;
    $next_date->modify("+$wait_days days");
    $now = new DateTime();

    if ($now < $next_date) {
        $is_eligible = false;
        $next_eligible_date = $next_date->format('Y/m/d');
    }
}

$user_lat = $user_info['latitude'] ?: 0;
$user_lng = $user_info['longitude'] ?: 0;

$urgent_requests = [];

// Fetch requests ONLY if eligible, excluding requests this donor already responded to
if ($is_eligible) {
    $stmt = $pdo->prepare("
        SELECT br.*, f.name as facility_name,
        ( 6371 * acos( cos( radians(?) ) * cos( radians( f.latitude ) )
        * cos( radians( f.longitude ) - radians(?) ) + sin( radians(?) )
        * sin( radians( f.latitude ) ) ) ) AS distance
        FROM blood_requests br
        JOIN facilities f ON br.facility_id = f.id
        WHERE br.status = 'open'
          AND br.blood_type = ?
          AND NOT EXISTS (
              SELECT 1 FROM donations d
              WHERE d.request_id = br.id
                AND d.donor_id = ?
                AND d.status IN ('pending', 'completed', 'approved')
          )
        ORDER BY br.urgency_level DESC, distance ASC, br.created_at DESC
        LIMIT 4
    ");
    $stmt->execute([$user_lat, $user_lng, $user_lat, $user_info['blood_type'], $_SESSION['user_id']]);
    $urgent_requests = $stmt->fetchAll();
}

// Fetch screening questions for modal
$stmt = $pdo->query("SELECT * FROM screening_questions WHERE is_active = 1 ORDER BY id ASC");
$screening_questions = $stmt->fetchAll();
?>

<div class="db-page-header">
    <h1 class="db-page-title">مرحباً بك، <?php echo htmlspecialchars(explode(' ', $user_info['name'])[0]); ?></h1>
    <p class="db-page-subtitle">إليك ملخص تأثيرك والاحتياجات العاجلة الحالية في منطقتك.</p>
</div>
<!-- Alerts -->
<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert--success mb-md">
        <?php echo htmlspecialchars($_SESSION['success_msg']);
        unset($_SESSION['success_msg']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert--error mb-md">
        <?php echo htmlspecialchars($_SESSION['error_msg']);
        unset($_SESSION['error_msg']); ?></div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="bento mb-lg">
    <!-- أرواح تم إنقاذها -->
    <div class="stat-card">
        <div class="stat-card__label">
            <span class="material-symbols-outlined">favorite</span>
            أرواح تم إنقاذها
        </div>
        <div>
            <div class="stat-card__number"><?php echo number_format($user_info['points']); ?></div>
            <p class="stat-card__desc">مرضى ساعدتهم منذ التسجيل</p>
        </div>
    </div>

    <!-- إجمالي التبرعات -->
    <div class="stat-card">
        <div class="stat-card__label stat-card__label--muted">
            <span class="material-symbols-outlined">water_drop</span>
            إجمالي التبرعات
        </div>
        <div>
            <div class="stat-card__number"><?php echo number_format($stats['total']); ?></div>
            <p class="stat-card__desc">وحدة دم كاملة</p>
        </div>
    </div>

    <!-- آخر تبرع -->
    <div class="stat-card stat-card--dark">
        <div class="stat-card__top-row">
            <div class="stat-card__label">
                <span class="material-symbols-outlined">history</span>
                آخر تبرع لك
            </div>
            <?php if ($last_donation): ?>
                <span class="badge-confirmed"
                    style="background: rgba(255,255,255,0.2); color:#fff; border:none;">مكتمل</span>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($last_donation): ?>
                <div class="stat-card__appt-time"><?php echo date('Y/m/d', strtotime($last_donation['created_at'])); ?>
                </div>
                <p class="stat-card__desc" style="color: #cbd5e1;">
                    <?php echo htmlspecialchars($last_donation['facility_name']); ?></p>
            <?php else: ?>
                <div class="stat-card__appt-time" style="font-size: 18px;">لا يوجد تبرعات سابقة</div>
                <p class="stat-card__desc" style="color: #cbd5e1;">بانتظار قطرتك الأولى لإنقاذ حياة</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- النداءات العاجلة -->
<div class="mb-lg">
    <div class="section-header">
        <div>
            <h2 class="section-header__title">نداءات عاجلة بالقرب منك</h2>
            <p class="section-header__sub">مستشفيات ضمن نطاق 15 كم تعاني من نقص حاد في فصيلتك.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/dashboards/donor/urgent.php" class="section-header__link">
            عرض الخريطة <span class="material-symbols-outlined">arrow_back</span>
        </a>
    </div>

    <div class="bento bento--2col">
        <?php if (!$is_eligible): ?>
            <div class="empty-state col-span-full" style="background: #fffbeb; border: 1px solid #fde68a;">
                <span class="material-symbols-outlined text-warning mb-sm" style="font-size: 32px;">health_and_safety</span>
                <h3 class="font-bold mb-xs">فترة نقاهة طبية</h3>
                <p class="text-secondary text-sm">
                    حفاظاً على صحتك، لا يمكنك التبرع بالدم حالياً.
                    ستكون مؤهلاً للتبرع مجدداً ابتداءً من تاريخ <strong
                        dir="ltr"><?php echo $next_eligible_date; ?></strong>.
                </p>
            </div>
        <?php elseif (count($urgent_requests) > 0): ?>
            <?php foreach ($urgent_requests as $req): ?>
                <div class="request-card">
                    <div class="request-card__stripe"></div>
                    <div class="blood-chip">
                        <?php echo htmlspecialchars($req['blood_type']); ?>
                        <span class="blood-chip__dot"></span>
                    </div>
                    <div class="request-card__body" style="padding-right: 16px;">
                        <div class="request-card__top mb-xs">
                            <h3 class="request-card__hospital"
                                style="font-size: 18px; margin:0; font-weight: bold; color: var(--clr-primary-dark);">
                                <?php echo htmlspecialchars($req['facility_name']); ?>
                            </h3>
                            <span
                                class="urgency-tag <?php echo ($req['urgency_level'] == 'critical') ? 'urgency-tag--critical' : 'urgency-tag--high'; ?>">
                                <?php echo ($req['urgency_level'] == 'critical') ? 'حرج جداً' : 'عاجل'; ?>
                            </span>
                        </div>

                        <p style="font-size: 13px; color: #64748b; margin-bottom: 12px; font-weight: 500;">
                            <?php echo $req['patient_name'] ? 'للمريض: ' . htmlspecialchars($req['patient_name']) : 'طلب داخلي / حملة تبرع'; ?>
                        </p>

                        <div
                            style="display: flex; gap: 16px; margin-bottom: 16px; font-size: 13px; background: #f8fafc; padding: 8px 12px; border-radius: 6px;">
                            <div style="display: flex; align-items: center; gap: 6px; color: #475569; font-weight: 600;">
                                <span class="material-symbols-outlined text-error" style="font-size: 18px;">bloodtype</span>
                                متبقي: <?php echo max(0, $req['units_needed'] - $req['units_secured']); ?> أكياس
                            </div>
                            <?php if ($req['distance'] !== null): ?>
                                <div
                                    style="display: flex; align-items: center; gap: 6px; color: #475569; border-right: 1px solid #cbd5e1; padding-right: 16px; font-weight: 600;">
                                    <span class="material-symbols-outlined text-primary" style="font-size: 18px;">location_on</span>
                                    يبعد <?php echo number_format($req['distance'], 1); ?> كم
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="button"
                            onclick="openScreeningModal(<?php echo $req['id']; ?>, '<?php echo htmlspecialchars(addslashes($req['facility_name'])); ?>')"
                            class="btn-primary w-full"
                            style="justify-content: center; font-size: 15px; font-weight: bold; <?php echo ($req['urgency_level'] == 'critical') ? 'background: #dc2626; border-color: #dc2626;' : ''; ?> padding: 12px; cursor:pointer;">
                            <?php echo ($req['urgency_level'] == 'critical') ? 'تبرع الآن لإنقاذ حياة' : 'تبرع الآن'; ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state col-span-full">
                لا توجد نداءات عاجلة لفصيلتك بالقرب منك في الوقت الحالي.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Screening Modal -->
<div id="screeningModal"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.6); z-index:9999; align-items:center; justify-content:center; padding:16px; backdrop-filter: blur(4px);">
    <div
        style="background:#fff; width:100%; max-width:600px; max-height:90vh; overflow-y:auto; border-radius:16px; position:relative; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
        <div
            style="position:sticky; top:0; background:#fff; padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; z-index:10; border-radius: 16px 16px 0 0;">
            <h2 class="font-bold"
                style="font-size:18px; color:var(--clr-primary-dark); display:flex; align-items:center; gap:8px; margin:0;">
                <span class="material-symbols-outlined text-primary">health_and_safety</span> الاستبانة الطبية للتبرع
            </h2>
            <button type="button" onclick="closeScreeningModal()"
                style="background:#f1f5f9; border:none; cursor:pointer; color:#64748b; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition: background 0.2s;">
                <span class="material-symbols-outlined" style="font-size:18px;">close</span>
            </button>
        </div>

        <div style="padding:24px;">
            <div class="mb-lg"
                style="background:#f8fafc; padding:16px; border-radius:8px; border-right:4px solid var(--clr-primary); font-size:14px;">
                <span class="text-secondary">تبرع لصالح:</span> <strong id="modalFacilityName"
                    style="color:var(--clr-primary-dark); font-size:15px;"></strong>
            </div>

            <form id="screeningForm" method="POST" action="donate_action.php">
                <input type="hidden" name="request_id" id="modalRequestId" value="">

                <?php foreach ($screening_questions as $index => $q): ?>
                    <div class="mb-md"
                        style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; transition: border-color 0.2s;">
                        <p class="font-bold mb-sm" style="font-size: 14px; line-height:1.6; color:#334155;">
                            <?php echo ($index + 1) . '. ' . htmlspecialchars($q['question_text']); ?>
                        </p>
                        <div style="display: flex; gap: 24px; margin-top:12px;">
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="yes" required
                                    onchange="handleAnswer(<?php echo $q['id']; ?>, 'yes', '<?php echo htmlspecialchars(addslashes($q['rejection_reason'])); ?>')">
                                <span style="font-weight:600; color:#475569;">نعم</span>
                            </label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="no" required
                                    onchange="handleAnswer(<?php echo $q['id']; ?>, 'no', '')">
                                <span style="font-weight:600; color:#475569;">لا</span>
                            </label>
                        </div>
                        <div id="reason_<?php echo $q['id']; ?>" class="text-error text-sm mt-sm"
                            style="display: none; background: #fef2f2; padding: 10px 12px; border-radius: 6px; border-right:3px solid #fca5a5; line-height:1.5;">
                        </div>
                    </div>
                <?php endforeach; ?>

                <div id="rejection_alert" class="alert alert--error mb-md mt-lg"
                    style="display: none; font-weight:600;">
                    عذراً، بناءً على إجاباتك، أنت غير لائق طبياً للتبرع بالدم في الوقت الحالي حرصاً على سلامتك وسلامة
                    المريض.
                </div>

                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 24px;">
                    <button type="submit" id="submitBtn" class="btn-primary w-full"
                        style="justify-content: center; padding: 14px; font-size:15px; font-weight:bold;">
                        <span class="material-symbols-outlined" style="font-size:18px;">verified</span> تأكيد التبرع
                        الآن
                    </button>
                    <button type="button" onclick="closeScreeningModal()" class="btn-outline w-full"
                        style="justify-content: center; padding: 14px; font-size:15px; font-weight:bold; background:#fff;">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openScreeningModal(reqId, facilityName) {
        document.getElementById('modalRequestId').value = reqId;
        document.getElementById('modalFacilityName').innerText = facilityName;
        document.getElementById('screeningModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeScreeningModal() {
        document.getElementById('screeningModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('screeningForm').reset();

        // Hide all reasons
        const reasons = document.querySelectorAll('[id^="reason_"]');
        reasons.forEach(r => {
            r.style.display = 'none';
            r.parentElement.style.borderColor = '#e5e7eb';
        });

        checkAllAnswers();
    }

    function handleAnswer(qId, answer, reason) {
        const reasonDiv = document.getElementById('reason_' + qId);
        const parentCard = reasonDiv.parentElement;

        if (answer === 'yes') {
            reasonDiv.innerHTML = '<span class="material-symbols-outlined" style="font-size: 16px; vertical-align: text-bottom;">warning</span> ' + reason;
            reasonDiv.style.display = 'block';
            parentCard.style.borderColor = '#fca5a5';
            parentCard.style.backgroundColor = '#fef2f2';
        } else {
            reasonDiv.style.display = 'none';
            parentCard.style.borderColor = '#e5e7eb';
            parentCard.style.backgroundColor = '#fff';
        }

        checkAllAnswers();
    }

    function checkAllAnswers() {
        const radios = document.querySelectorAll('#screeningForm input[type="radio"]:checked');
        let anyYes = false;

        radios.forEach(radio => {
            if (radio.value === 'yes') {
                anyYes = true;
            }
        });

        const submitBtn = document.getElementById('submitBtn');
        const rejectionAlert = document.getElementById('rejection_alert');

        if (anyYes) {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
            rejectionAlert.style.display = 'block';
        } else {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            rejectionAlert.style.display = 'none';
        }
    }
</script>

<?php require_once '../../includes/donor_footer.php'; ?>