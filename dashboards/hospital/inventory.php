<?php
require_once '../../includes/hospital_auth.php';
require_once '../../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$page_title  = 'إدارة المخزون - عطاء';
$facility_id = $_SESSION['facility_id'];

// حفظ المخزون
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inventory'])) {
    try {
        $pdo->beginTransaction();
        foreach ($_POST['inventory'] as $type => $amount) {
            $amt = max(0, (int)$amount);
            $pdo->prepare("UPDATE inventory SET units_available = ? WHERE facility_id = ? AND blood_type = ?")
                ->execute([$amt, $facility_id, $type]);
        }
        $pdo->commit();
        $success_msg = "تم تحديث المخزون بنجاح.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "حدث خطأ أثناء الحفظ.";
    }
}

$stmt = $pdo->prepare("SELECT blood_type, units_available FROM inventory WHERE facility_id = ?");
$stmt->execute([$facility_id]);
$inventory   = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

require_once '../../includes/dashboard_header.php';
?>

<?php if (isset($success_msg)): ?>
<div id="php-flash" data-type="success" data-msg="<?php echo htmlspecialchars($success_msg); ?>"></div>
<?php endif; ?>
<?php if (isset($error_msg)): ?>
<div id="php-flash" data-type="error" data-msg="<?php echo htmlspecialchars($error_msg); ?>"></div>
<?php endif; ?>

<div class="db-page-header-row">
    <div class="db-page-header">
        <h1 class="db-page-title">إدارة مخزون الدم</h1>
        <p class="db-page-subtitle">تحديث الكميات المتاحة من كل فصيلة في منشأتك</p>
    </div>
</div>

<div class="bento bento--2col">
    <!-- نموذج التحديث -->
    <div class="db-table-card" style="grid-column: span 2;">
        <form method="POST" action="">
            <div class="inventory-update-grid">
                <?php foreach ($blood_types as $bt):
                    $qty    = $inventory[$bt] ?? 0;
                    $status = $qty < 5 ? 'critical' : ($qty < 10 ? 'low' : ($qty < 20 ? 'medium' : 'good'));
                    $colors = ['critical' => '#dc2626', 'low' => '#d97706', 'medium' => '#2563eb', 'good' => '#16a34a'];
                    $labels = ['critical' => 'حرج', 'low' => 'منخفض', 'medium' => 'مستقر', 'good' => 'ممتاز'];
                ?>
                <div class="inv-update-card">
                    <div class="inv-update-card__type" style="color: <?php echo $colors[$status]; ?>;" dir="ltr">
                        <?php echo $bt; ?>
                    </div>
                    <div class="inv-update-card__status" style="color: <?php echo $colors[$status]; ?>;">
                        <?php echo $labels[$status]; ?>
                    </div>
                    <input type="number"
                           name="inventory[<?php echo $bt; ?>]"
                           class="form-input"
                           style="text-align: center; font-weight: 700; font-size: 18px;"
                           min="0"
                           value="<?php echo $qty; ?>">
                    <div class="inv-update-card__unit">وحدة</div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 24px; text-align: center;">
                <button type="submit" class="btn-primary" style="min-width: 200px;">
                    <span class="material-symbols-outlined">save</span>
                    حفظ التغييرات
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.inventory-update-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    padding: 8px;
}

.inv-update-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px 16px;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
    transition: box-shadow 0.2s;
}

.inv-update-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

.inv-update-card__type {
    font-size: 26px;
    font-weight: 800;
    font-family: monospace;
}

.inv-update-card__status {
    font-size: 12px;
    font-weight: 700;
}

.inv-update-card .form-input {
    width: 80px;
    padding: 8px;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    font-family: 'Cairo', sans-serif;
    background: white;
}

.inv-update-card .form-input:focus {
    outline: none;
    border-color: #991b1b;
}

.inv-update-card__unit {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 600;
}

@media (max-width: 640px) {
    .inventory-update-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<?php require_once '../../includes/dashboard_footer.php'; ?>
