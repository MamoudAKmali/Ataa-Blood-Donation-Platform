<?php
require_once '../../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: ../../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    $donor_id   = $_SESSION['user_id'];

    // جلب بيانات النداء والمستشفى - يجب أن يكون مفتوحاً
    $stmt = $pdo->prepare("
        SELECT br.id, br.facility_id, br.units_needed, br.units_secured,
               f.name AS facility_name, f.latitude, f.longitude
        FROM blood_requests br
        JOIN facilities f ON br.facility_id = f.id
        WHERE br.id = ? AND br.status = 'open'
    ");
    $stmt->execute([$request_id]);
    $req = $stmt->fetch();

    if ($req) {
        // هل سبق وأبدى هذا المتبرع استجابة لهذا النداء؟
        $check = $pdo->prepare("SELECT id FROM donations WHERE donor_id = ? AND request_id = ? AND status IN ('pending','completed')");
        $check->execute([$donor_id, $request_id]);

        if (!$check->fetch()) {
            $insert = $pdo->prepare("INSERT INTO donations (donor_id, facility_id, request_id, appointment_date, status) VALUES (?, ?, ?, NOW(), 'pending')");

            if ($insert->execute([$donor_id, $req['facility_id'], $request_id])) {

                // تحديث عدد الوحدات المؤمَّنة في النداء
                $new_secured = (int)$req['units_secured'] + 1;
                $pdo->prepare("UPDATE blood_requests SET units_secured = ? WHERE id = ?")->execute([$new_secured, $request_id]);

                // إذا اكتملت الوحدات المطلوبة، أغلق النداء تلقائياً
                if ($new_secured >= (int)$req['units_needed']) {
                    $pdo->prepare("UPDATE blood_requests SET status = 'fulfilled' WHERE id = ?")->execute([$request_id]);
                }

                // رسالة النجاح مع رابط الخريطة (بدون inline styles)
                $map_btn = '';
                if ($req['latitude'] && $req['longitude']) {
                    $map_url  = "https://www.google.com/maps/dir/?api=1&destination={$req['latitude']},{$req['longitude']}";
                    $map_btn  = "<a href='{$map_url}' target='_blank' class='map-link-btn'>";
                    $map_btn .= "<span class='material-symbols-outlined'>directions_car</span>";
                    $map_btn .= " عرض مسار الوصول إلى " . htmlspecialchars($req['facility_name']);
                    $map_btn .= "</a>";
                }

                $_SESSION['success_msg'] = 'تم تسجيل استجابتك بنجاح! شكراً لك، يرجى التوجه للمستشفى في أقرب وقت.' . ($map_btn ? '<br>' . $map_btn : '');
            } else {
                $_SESSION['error_msg'] = 'حدث خطأ أثناء تسجيل استجابتك. يرجى المحاولة لاحقاً.';
            }
        } else {
            $_SESSION['error_msg'] = 'سبق وأبديت استجابتك لهذا النداء. يرجى التوجه للمستشفى.';
        }
    } else {
        $_SESSION['error_msg'] = 'عذراً، هذا النداء لم يعد متاحاً أو اكتمل العدد المطلوب.';
    }
}

header('Location: index.php');
exit;
