<?php
require_once '../../includes/hospital_auth.php';
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    
    // تزويد كيس دم واحد للنداء وتحديث الحالة إذا اكتمل
    $stmt = $pdo->prepare("SELECT units_needed, units_secured FROM blood_requests WHERE id = ? AND facility_id = ?");
    $stmt->execute([$request_id, $_SESSION['facility_id']]);
    $req = $stmt->fetch();
    
    if ($req) {
        $new_secured = $req['units_secured'] + 1;
        $status = ($new_secured >= $req['units_needed']) ? 'fulfilled' : 'open';
        
        $update = $pdo->prepare("UPDATE blood_requests SET units_secured = ?, status = ? WHERE id = ?");
        $update->execute([$new_secured, $status, $request_id]);
    }
}
header("Location: index.php");
exit;
?>
